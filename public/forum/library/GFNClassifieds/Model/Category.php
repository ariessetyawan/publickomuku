<?php /*bab1f483a6e726c242302ca91ca9c84fc6d3c2ca*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Category extends XenForo_Model
{
    public function getCategoryById($categoryId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            "SELECT category.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_category AS category
            {$joinOptions['joinTables']}
            WHERE category.category_id = ?", $categoryId
        );
    }

    public function getCategoriesByIds(array $categoryIds, array $fetchOptions = array())
    {
        return $this->getCategories(array('category_id' => $categoryIds), $fetchOptions);
    }

    public function getAllCategories(array $fetchOptions = array())
    {
        $joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            "SELECT category.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_category AS category
            {$joinOptions['joinTables']}
            ORDER BY category.lft", 'category_id'
        );
    }

    public function getCategories(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareCategoryConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT category.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_category AS category
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
            ORDER BY category.lft', 'category_id'
        );
    }

    public function getPossibleParentCategories(array $category, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            "SELECT category.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_category AS category
            {$joinOptions['joinTables']}
            WHERE category.lft < ? OR category.rgt > ?
            ORDER BY category.lft", 'category_id', array($category['lft'], $category['rgt'])
        );
    }

    public function prepareCategoryConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['category_ids']))
        {
            $conditions['category_id'] = $conditions['category_ids'];
        }

        if (!empty($conditions['category_id']))
        {
            if (is_array($conditions['category_id']))
            {
                $sqlConditions[] = 'category.category_id IN (' . $db->quote($conditions['category_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'category.category_id = ' . $db->quote($conditions['category_id']);
            }
        }

        if (isset($conditions['thread_node_id']))
        {
            if (is_array($conditions['thread_node_id']))
            {
                $sqlConditions[] = 'category.thread_node_id IN (' . $db->quote($conditions['thread_node_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'category.thread_node_id = ' . $db->quote($conditions['thread_node_id']);
            }
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareCategoryFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['permissionCombinationId']))
        {
            $selectFields .= ',
				permission.cache_value AS category_permission_cache';
            $joinTables .= '
				LEFT JOIN kmk_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'classified_category\'
						AND permission.content_id = category.category_id)';
        }

        if (isset($fetchOptions['watchUserId']))
        {
            if (empty($fetchOptions['watchUserId']))
            {
                $selectFields .= ', 0 AS category_is_watched';
            }
            else
            {
                $selectFields .= ', IF(category_watch.user_id IS NULL, 0, 1) AS category_is_watched';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_category_watch AS category_watch
                        ON (category_watch.category_id = category.category_id
                            AND category_watch.user_id = ' . $this->_getDb()->quote($fetchOptions['watchUserId']) . ')';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function prepareCategories(array $categories)
    {
        array_walk($categories, array($this, 'prepareCategory'));
        return $categories;
    }

    public function getViewableCategories(array $fetchOptions = array(), array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (empty($fetchOptions['permissionCombinationId']))
        {
            $fetchOptions['permissionCombinationId'] = $viewingUser['permission_combination_id'];
        }

        $categories = $this->getAllCategories($fetchOptions);
        if (!$categories)
        {
            return array();
        }

        if (!empty($fetchOptions['permissionCombinationId']))
        {
            $this->bulkSetCategoryPermCache(
                $fetchOptions['permissionCombinationId'], $categories, 'category_permission_cache'
            );
        }

        foreach ($categories AS $key => $category)
        {
            if (!$this->canViewCategory($category, $null, $viewingUser))
            {
                unset($categories[$key]);
            }
        }

        return $categories;
    }

    public function canViewCategory(array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);
        return $this->hasPermission('view', $category, $viewingUser);
    }

    public function canWatchCategory(array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        return ($viewingUser['user_id']);
    }

    public function canAddClassified(array $category = null, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        if ($category)
        {
            $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        }
        else
        {
            $this->standardizeViewingUserReference($viewingUser);
        }

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($category)
        {
            if (!isset($category['allowClassifieds']))
            {
                $category['allowClassifieds'] = !(empty($category['advert_type_cache']) || empty($category['package_cache']));
            }

            if (!$category['allowClassifieds'])
            {
                $errorPhraseKey = 'classifieds_category_not_allow_new_classifieds';
                return false;
            }

            $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
            $createLimit = $this->hasPermission('max', $category);
            if ($createLimit != -1 && $viewingUser['classified_count'] >= $createLimit)
            {
                $errorPhraseKey = array(
                    'classified_you_cannot_have_more_than_x_active_classifieds_at_same_time',
                    'count' => $this->hasPermission('max', $category)
                );

                return false;
            }

            if (!$this->hasPermission('add', $category, $viewingUser))
            {
                return false;
            }

            return true;
        }
        else
        {
            $createLimit = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'max');
            if ($createLimit != -1 && $viewingUser['classified_count'] >= $createLimit)
            {
                $errorPhraseKey = array(
                    'classified_you_cannot_have_more_than_x_active_classifieds_at_same_time',
                    'count' => XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'max')
                );

                return false;
            }

            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'add');
        }
    }

    public function standardizeViewingUserReferenceForCategory($categoryId, array &$viewingUser = null, array &$categoryPermissions = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!is_array($categoryPermissions))
        {
            if (is_array($categoryId))
            {
                $categoryId = $categoryId['category_id'];
            }

            $categoryPermissions = $this->getCategoryPermCache($viewingUser['permission_combination_id'], $categoryId);
        }
    }

    public function prepareCategory(array &$category)
    {
        if (empty($category['advert_type_cache']))
        {
            $category['advert_type_cache'] = array();
        }
        elseif (!is_array($category['advert_type_cache']))
        {
            $category['advert_type_cache'] = XenForo_Helper_Php::safeUnserialize($category['advert_type_cache']);
        }

        if (empty($category['package_cache']))
        {
            $category['package_cache'] = array();
        }
        elseif (!is_array($category['package_cache']))
        {
            $category['package_cache'] = XenForo_Helper_Php::safeUnserialize($category['package_cache']);
        }

        if (empty($category['prefix_cache']))
        {
            $category['prefix_cache'] = array();
        }
        elseif (!is_array($category['prefix_cache']))
        {
            $category['prefix_cache'] = XenForo_Helper_Php::safeUnserialize($category['prefix_cache']);
        }

        if (empty($category['field_cache']))
        {
            $category['field_cache'] = array();
        }
        elseif (!is_array($category['field_cache']))
        {
            $category['field_cache'] = XenForo_Helper_Php::safeUnserialize($category['field_cache']);
        }

        if (!isset($category['allowClassifieds']))
        {
            $category['allowClassifieds'] = !(empty($category['advert_type_cache']) || empty($category['package_cache']));
        }

        $category['canAdd'] = $this->canAddClassified($category);
        $category['canWatch'] = $this->canWatchCategory($category);
        $category['fieldCache'] = $category['field_cache'];

        return $category;
    }

    public function getCategoryOptionArray(array $categories, $selectedCategoryId = 0)
    {
        $options = array();

        foreach ($categories as $category)
        {
            $options[] = array(
                'value' => $category['category_id'],
                'label' => $category['title'],
                'selected' => ($selectedCategoryId == $category['category_id']),
                'depth' => $category['depth']
            );
        }

        return $options;
    }

    public function groupCategoriesByParent(array $categories)
    {
        $return = array();

        foreach ($categories AS $category)
        {
            $return[$category['parent_category_id']][$category['category_id']] = $category;
        }

        return $return;
    }

    public function ungroupCategories(array $grouped, array $filterIds = null, $parentCategoryId = 0)
    {
        $output = array();

        if (!empty($grouped[$parentCategoryId]))
        {
            foreach ($grouped[$parentCategoryId] AS $category)
            {
                if ($filterIds === null || in_array($category['category_id'], $filterIds))
                {
                    $output[$category['category_id']] = $category;
                }

                $output += $this->ungroupCategories($grouped, $filterIds, $category['category_id']);
            }
        }

        return $output;
    }

    public function getCategoryAncestors(array $category, array $categories = null)
    {
        if ($categories === null)
        {
            return $this->fetchAllKeyed(
                'SELECT *
                FROM kmk_classifieds_category
                WHERE lft < ? AND rgt > ?
			    ORDER BY lft', 'category_id', array($category['lft'], $category['rgt'])
            );
        }

        if (empty($categories))
        {
            return array();
        }

        $output = array();

        foreach ($categories as $i => $ancestor)
        {
            if ($ancestor['lft'] < $category['lft'] && $ancestor['rgt'] > $category['lft'])
            {
                $output[$i] = $ancestor;
            }
        }

        return $output;
    }

    public function getCategoryDescendants(array $category, array $categories = null)
    {
        if ($categories === null)
        {
            return $this->fetchAllKeyed(
                'SELECT *
                FROM kmk_classifieds_category
                WHERE lft > ? AND rgt < ?
			    ORDER BY lft', 'category_id', array($category['lft'], $category['rgt'])
            );
        }

        if (empty($categories))
        {
            return array();
        }

        $output = array();

        foreach ($categories as $i => $descendant)
        {
            if ($descendant['lft'] > $category['lft'] && $descendant['rgt'] < $category['lft'])
            {
                $output[$i] = $descendant;
            }
        }

        return $output;
    }

    public function getDescendantCategoryIdsFromGrouped(array $grouped, $parentCategoryId = 0)
    {
        $parentIds = array($parentCategoryId);
        $output = array();

        do
        {
            $parentId = array_shift($parentIds);
            if (isset($grouped[$parentId]))
            {
                $keys = array_keys($grouped[$parentId]);
                $output = array_merge($output, $keys);
                $parentIds = array_merge($parentIds, $keys);
            }
        }
        while ($parentIds);

        return $output;
    }

    public function getDescendantsOfCategoryIds(array $categoryIds)
    {
        $categories = $this->getAllCategories();

        $ranges = array();
        foreach ($categoryIds AS $categoryId)
        {
            if (isset($categories[$categoryId]))
            {
                $category = $categories[$categoryId];
                $ranges[] = array($category['lft'], $category['rgt']);
            }
        }

        $descendants = array();
        foreach ($categories AS $category)
        {
            foreach ($ranges AS $range)
            {
                if ($category['lft'] > $range[0] && $category['lft'] < $range[1])
                {
                    $descendants[$category['category_id']] = $category;
                    break;
                }
            }
        }

        return $descendants;
    }

    public function rebuildCategoryStructure()
    {
        $grouped = $this->groupCategoriesByParent($this->fetchAllKeyed(
            'SELECT *
            FROM kmk_classifieds_category
            ORDER BY display_order', 'category_id'
        ));

        $db = $this->_getDb();
        XenForo_Db::beginTransaction($db);

        $changes = $this->_getStructureChanges($grouped);
        foreach ($changes AS $categoryId => $change)
        {
            $db->update('kmk_classifieds_category', $change, 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
        return $changes;
    }

    protected function _getStructureChanges(array $grouped, $parentId = 0, $depth = 0, $startPosition = 1, &$nextPosition = 0, array $breadcrumb = array())
    {
        $nextPosition = $startPosition;

        if (!isset($grouped[$parentId]))
        {
            return array();
        }

        $changes = array();
        $serializedBreadcrumb = XenForo_Helper_Php::safeSerialize($breadcrumb);

        foreach ($grouped[$parentId] AS $categoryId => $category)
        {
            $left = $nextPosition;
            $nextPosition++;

            $thisBreadcrumb = $breadcrumb + array(
                    $categoryId => array(
                        'category_id' => $categoryId,
                        'title' => $category['title'],
                        'parent_category_id' => $category['parent_category_id'],
                        'depth' => $category['depth'],
                        'lft' => $category['lft'],
                        'rgt' => $category['rgt'],
                    )
                );

            $changes += $this->_getStructureChanges($grouped, $categoryId, $depth + 1, $nextPosition, $nextPosition, $thisBreadcrumb);

            $catChanges = array();
            if ($category['depth'] != $depth)
            {
                $catChanges['depth'] = $depth;
            }
            if ($category['lft'] != $left)
            {
                $catChanges['lft'] = $left;
            }
            if ($category['rgt'] != $nextPosition)
            {
                $catChanges['rgt'] = $nextPosition;
            }
            if ($category['category_breadcrumb'] != $serializedBreadcrumb)
            {
                $catChanges['category_breadcrumb'] = $serializedBreadcrumb;
            }

            if ($catChanges)
            {
                $changes[$categoryId] = $catChanges;
            }

            $nextPosition++;
        }

        return $changes;
    }

    protected static $_categoryPermCache = array();

    public function setCategoryPermCache($combinationId, $categoryId, $cache)
    {
        if ($combinationId === null)
        {
            $combinationId = XenForo_Visitor::getInstance()->get('permission_combination_id');
        }

        if (is_string($cache))
        {
            $cache = XenForo_Permission::unserializePermissions($cache);
        }

        self::$_categoryPermCache[$combinationId][$categoryId] = $cache;
    }

    public function bulkSetCategoryPermCache($combinationId, array $dataSets, $key = null)
    {
        if ($combinationId === null)
        {
            $combinationId = XenForo_Visitor::getInstance()->get('permission_combination_id');
        }

        foreach ($dataSets AS $categoryId => $data)
        {
            if ($key !== null && !isset($data[$key]))
            {
                continue;
            }

            $this->setCategoryPermCache($combinationId, $categoryId,
                $key === null ? $data : $data[$key]
            );
        }
    }

    public function resetCategoryPermCache($combinationId = null, $categoryId = null)
    {
        if ($combinationId === null && $categoryId === null)
        {
            self::$_categoryPermCache = array();
        }
        else if ($categoryId === null)
        {
            unset(self::$_categoryPermCache[$combinationId]);
        }
        else
        {
            unset(self::$_categoryPermCache[$combinationId][$categoryId]);
        }
    }

    public function getCategoryPermCache($combinationId, $categoryId)
    {
        if ($combinationId === null)
        {
            $combinationId = XenForo_Visitor::getInstance()->get('permission_combination_id');
        }

        if (!isset(self::$_categoryPermCache[$combinationId][$categoryId]))
        {
            /** @var XenForo_Model_PermissionCache $permissionCacheModel */
            $permissionCacheModel = $this->getModelFromCache('XenForo_Model_PermissionCache');

            self::$_categoryPermCache[$combinationId][$categoryId] = $permissionCacheModel->getContentPermissionsForItem(
                $combinationId, 'classified_category', $categoryId
            );
        }

        return self::$_categoryPermCache[$combinationId][$categoryId];
    }

    public function getCategoryBreadcrumb(array $category, $includeSelf = true)
    {
        $breadcrumbs = array();

        if (!isset($category['categoryBreadcrumb']))
        {
            $category['categoryBreadcrumb'] = XenForo_Helper_Php::safeUnserialize($category['category_breadcrumb']);
        }

        foreach ($category['categoryBreadcrumb'] AS $catId => $breadcrumb)
        {
            $breadcrumbs[$catId] = array(
                'href' => XenForo_Link::buildPublicLink('full:classifieds/categories', $breadcrumb),
                'value' => $breadcrumb['title']
            );
        }

        if ($includeSelf)
        {
            $breadcrumbs[$category['category_id']] = array(
                'href' => XenForo_Link::buildPublicLink('full:classifieds/categories', $category),
                'value' => $category['title']
            );
        }

        return $breadcrumbs;
    }

    public function applyRecursiveCountsToGrouped(array $grouped, $parentCategoryId = 0)
    {
        if (!isset($grouped[$parentCategoryId]))
        {
            return array();
        }

        $this->_applyRecursiveCountsToGrouped($grouped, $parentCategoryId);
        return $grouped;
    }

    protected function _applyRecursiveCountsToGrouped(array &$grouped, $parentCategoryId)
    {
        $output = array(
            'classified_count' => 0,
            'featured_count' => 0,
            'last_update' => 0
        );

        foreach ($grouped[$parentCategoryId] AS $categoryId => &$category)
        {
            if (isset($grouped[$categoryId]))
            {
                $childCounts = $this->_applyRecursiveCountsToGrouped($grouped, $categoryId);

                $category['classified_count'] += $childCounts['classified_count'];
                $category['featured_count'] += $childCounts['featured_count'];
                if ($childCounts['last_update'] > $category['last_update'])
                {
                    $category['last_update'] = $childCounts['last_update'];
                    $category['last_classified_title'] = $childCounts['last_classified_title'];
                    $category['last_classified_id'] = $childCounts['last_classified_id'];
                }
            }

            $output['classified_count'] += $category['classified_count'];
            $output['featured_count'] += $category['featured_count'];
            if ($category['last_update'] > $output['last_update'])
            {
                $output['last_update'] = $category['last_update'];
                $output['last_classified_title'] = $category['last_classified_title'];
                $output['last_classified_id'] = $category['last_classified_id'];
            }
        }

        return $output;
    }

    public function hasPermission($permission, $category, $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        return XenForo_Permission::hasContentPermission($categoryPermissions, $permission);
    }
}