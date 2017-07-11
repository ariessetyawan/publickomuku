<?php /*1c02de53e39c10f1166bc90ff34dcd32ed4b5494*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_Prefix extends KomuKuYJB_Model
{
    const FETCH_CATEGORY        = 0x01;
    const FETCH_PREFIX_GROUP    = 0x02;

    public function getPrefixById($prefixId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_prefix
            WHERE prefix_id = ?', $prefixId
        );
    }

    public function getAllPrefixes(array $fetchOptions = array())
    {
        return $this->getPrefixes(array(), $fetchOptions);
    }

    public function getPrefixes(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->preparePrefixConditions($conditions, $fetchOptions);
        $orderClause = $this->preparePrefixOrderOptions($fetchOptions);
        $joinOptions = $this->preparePrefixFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            "SELECT prefix.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_prefix AS prefix
            {$joinOptions['joinTables']}
            WHERE {$whereClause}
            {$orderClause}", $limitOptions['limit'], $limitOptions['offset']
        ), 'prefix_id');
    }

    public function preparePrefixFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category_assoc.category_id';
                $joinTables .= ' INNER JOIN kmk_classifieds_prefix_category AS category_assoc
                                    ON (category_assoc.prefix_id = prefix.prefix_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_PREFIX_GROUP)
            {
                $selectFields .= ', prefix_group.display_order AS prefix_group_order';
                $joinTables .= ' LEFT JOIN kmk_classifieds_prefix_group AS prefix_group
                                     ON (prefix_group.prefix_group_id = prefix.prefix_group_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function verifyPrefixIsUsable($prefixId, $categoryId, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$prefixId)
        {
            return true; // not picking one, always ok
        }

        $prefix = $this->getPrefixIfInCategory($prefixId, $categoryId);
        if (!$prefix)
        {
            return false; // bad prefix or bad category
        }

        return $this->_verifyPrefixIsUsableInternal($prefix, $viewingUser);
    }

    public function getPrefixIfInCategory($prefixId, $categoryId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT prefix.*
            FROM kmk_classifieds_prefix AS prefix
            INNER JOIN kmk_classifieds_prefix_category AS assoc
            ON (assoc.prefix_id = prefix.prefix_id AND assoc.category_id = ?)
            WHERE prefix.prefix_id = ?', array($categoryId, $prefixId)
        );
    }

    public function preparePrefixConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['category_ids']))
        {
            $conditions['category_id'] = $conditions['category_ids'];
        }

        if (isset($conditions['category_id']))
        {
            if (is_array($conditions['category_id']))
            {
                $sqlConditions[] = 'category_assoc.category_id IN (' . $db->quote($conditions['category_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'category_assoc.category_id = ' . $db->quote($conditions['category_id']);
            }

            $this->addFetchOptionJoin($fetchOptions, self::FETCH_CATEGORY);
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function preparePrefixOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'materialized_order' => 'prefix.materialized_order',
            'canonical_order' => 'prefix_group.display_order, prefix.display_order',
        );

        if (!empty($fetchOptions['order']) && $fetchOptions['order'] == 'canonical_order')
        {
            $this->addFetchOptionJoin($fetchOptions, self::FETCH_PREFIX_GROUP);
        }

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function getPrefixGroupById($groupId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_prefix_group
            WHERE prefix_group_id = ?', $groupId
        );
    }

    public function getAllPrefixGroups()
    {
        return $this->fetchAllKeyed(
            'SELECT *
            FROM kmk_classifieds_prefix_group
            ORDER BY display_order', 'prefix_group_id'
        );
    }

    public function getPrefixesInCategories($categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT prefix.*, category_assoc.category_id
            FROM kmk_classifieds_prefix AS prefix
            INNER JOIN kmk_classifieds_prefix_category AS category_assoc
              ON (prefix.prefix_id = category_assoc.prefix_id)
            WHERE category_assoc.category_id IN (' . $db->quote($categoryIds) . ')
            ORDER BY prefix.materialized_order'
        );
    }

    public function getUsablePrefixesInCategories($categoryIds, array $viewingUser = null, $verifyUsability = true)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $prefixes = $this->getPrefixesInCategories($categoryIds);

        $prefixGroups = array();
        foreach ($prefixes AS $prefix)
        {
            if (!$verifyUsability || $this->_verifyPrefixIsUsableInternal($prefix, $viewingUser))
            {
                $prefixId = $prefix['prefix_id'];
                $prefixGroupId = $prefix['prefix_group_id'];

                if (!isset($prefixGroups[$prefixGroupId]))
                {
                    $prefixGroups[$prefixGroupId] = array();

                    if ($prefixGroupId)
                    {
                        $prefixGroups[$prefixGroupId]['title'] = new XenForo_Phrase(
                            $this->getPrefixGroupTitlePhraseName($prefixGroupId));
                    }

                }

                $prefixGroups[$prefixGroupId]['prefixes'][$prefixId] = $prefix;
            }
        }

        return $prefixGroups;
    }

    protected function _verifyPrefixIsUsableInternal(array $prefix, array $viewingUser)
    {
        $userGroups = explode(',', $prefix['allowed_user_group_ids']);
        if (in_array(-1, $userGroups) || in_array($viewingUser['user_group_id'], $userGroups))
        {
            return true; // available to all groups or the primary group
        }

        if ($viewingUser['secondary_group_ids'])
        {
            foreach (explode(',', $viewingUser['secondary_group_ids']) AS $userGroupId)
            {
                if (in_array($userGroupId, $userGroups))
                {
                    return true; // available to one secondary group
                }
            }
        }

        return false; // not available to any groups
    }

    public function getPrefixesByGroups(array $conditions = array(), array $fetchOptions = array(), &$prefixCount = 0)
    {
        $prefixes = $this->getPrefixes($conditions, $fetchOptions);
        $return = array();

        foreach ($prefixes as $prefix)
        {
            $return[$prefix['prefix_group_id']][$prefix['prefix_id']] = $this->preparePrefix($prefix);
        }

        $prefixCount = count($prefixes);
        return $return;
    }

    public function getVisiblePrefixIds(array $viewingUser = null, array $categoryIds = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = $this->getModelFromCache('KomuKuYJB_Model_Category');
        $prefixes = array();

        if ($categoryIds === null)
        {
            $categoryLimit = '';
        }
        else
        {
            if (!$categoryIds)
            {
                return array();
            }

            $categoryLimit = ' AND category.category_id IN (' . $this->_getDb()->quote($categoryIds) . ')';
        }

        $stmt = $this->_getDb()->query(
            'SELECT prefix.prefix_id, category.*, cache.cache_value AS category_permission_cache
            FROM kmk_classifieds_prefix AS prefix
            INNER JOIN kmk_classifieds_prefix_category AS assoc ON (assoc.prefix_id = prefix.prefix_id)
            INNER JOIN kmk_classifieds_category AS category ON (assoc.category_id = category.category_id' . $categoryLimit . ')
            INNER JOIN kmk_permission_cache_content AS cache ON
                (cache.content_type = \'classified_category\' AND cache.content_id = category.category_id AND cache.permission_combination_id = ?)
            ORDER BY prefix.materialized_order', $viewingUser['permission_combination_id']
        );

        while ($result = $stmt->fetch())
        {
            if (isset($prefixes[$result['prefix_id']]))
            {
                continue;
            }

            $permissions = XenForo_Permission::unserializePermissions($result['category_permission_cache']);

            if ($categoryModel->canViewCategory($result, $null, $viewingUser, $permissions))
            {
                $prefixes[$result['prefix_id']] = $result['prefix_id'];
            }
        }

        return $prefixes;
    }

    public function mergePrefixesIntoGroups(array $prefixes, array $prefixGroups)
    {
        $merge = array();

        foreach ($prefixGroups AS $prefixGroupId => $prefixGroup)
        {
            if (isset($prefixes[$prefixGroupId]))
            {
                $merge[$prefixGroupId] = $prefixes[$prefixGroupId];
                unset($prefixes[$prefixGroupId]);
            }
            else
            {
                $merge[$prefixGroupId] = array();
            }
        }

        if (!empty($prefixes))
        {
            foreach ($prefixes AS $prefixGroupId => $_prefixes)
            {
                $merge[$prefixGroupId] = $_prefixes;
            }
        }

        return $merge;
    }

    public function getPrefixOptions(array $conditions = array(), array $fetchOptions = array())
    {
        $prefixGroups = $this->getPrefixesByGroups($conditions, $fetchOptions);

        $options = array();

        foreach ($prefixGroups AS $prefixGroupId => $prefixes)
        {
            if ($prefixes)
            {
                if ($prefixGroupId)
                {
                    $groupTitle = new XenForo_Phrase($this->getPrefixGroupTitlePhraseName($prefixGroupId));
                    $groupTitle = (string) $groupTitle;
                }
                else
                {
                    $groupTitle = new XenForo_Phrase('ungrouped');
                    $groupTitle = '(' . $groupTitle . ')';
                }

                foreach ($prefixes AS $prefixId => $prefix)
                {
                    $options[$groupTitle][$prefixId] = array(
                        'value' => $prefixId,
                        'label' => (string)$prefix['title'],
                        '_data' => array('css' => $prefix['css_class'])
                    );
                }
            }
        }

        return $options;
    }

    public function preparePrefix(array &$prefix)
    {
        $prefix['title'] = new XenForo_Phrase(
            $this->getPrefixTitlePhraseName($prefix['prefix_id'])
        );

        return $prefix;
    }

    public function preparePrefixGroup(array &$group)
    {
        $group['title'] = new XenForo_Phrase(
            $this->getPrefixGroupTitlePhraseName($group['prefix_group_id'])
        );

        return $group;
    }

    public function preparePrefixGroups(array &$groups)
    {
        array_walk($groups, array($this, 'preparePrefixGroup'));
        return $groups;
    }

    public function getPrefixGroupOptions($selectedGroupId = 0)
    {
        $groups = $this->getAllPrefixGroups();

        if (!$groups)
        {
            return array();
        }

        $this->preparePrefixGroups($groups);
        $options = array();

        foreach ($groups as $i => $g)
        {
            $options[$i] = $g['title'];
        }

        return $options;
    }

    public function rebuildPrefixMaterializedOrder()
    {
        $prefixes = $this->getAllPrefixes(array('order' => 'canonical_order'));

        $db = $this->_getDb();
        $ungroupedPrefixes = array();
        $updates = array();
        $i = 0;

        foreach ($prefixes AS $prefixId => $prefix)
        {
            if ($prefix['prefix_group_id'])
            {
                if (++$i != $prefix['materialized_order'])
                {
                    $updates[$prefixId] = 'WHEN ' . $db->quote($prefixId) . ' THEN ' . $db->quote($i);
                }
            }
            else
            {
                $ungroupedPrefixes[$prefixId] = $prefix;
            }
        }

        foreach ($ungroupedPrefixes AS $prefixId => $prefix)
        {
            if (++$i != $prefix['materialized_order'])
            {
                $updates[$prefixId] = 'WHEN ' . $db->quote($prefixId) . ' THEN ' . $db->quote($i);
            }
        }

        if (!empty($updates))
        {
            $db->query('
				UPDATE kmk_classifieds_prefix SET materialized_order = CASE prefix_id
				' . implode(' ', $updates) . '
				END
				WHERE prefix_id IN(' . $db->quote(array_keys($updates)) . ')
			');
        }
    }

    public function getPrefixCache()
    {
        return $this->_getDb()->fetchPairs(
            'SELECT prefix_id, css_class
            FROM kmk_classifieds_prefix
            ORDER BY materialized_order'
        );
    }

    public function rebuildPrefixCache()
    {
        $prefixes = $this->getPrefixCache();
        GFNCore_Registry::set('classifiedPrefixes', $prefixes);

        return $prefixes;
    }

    public function getPrefixTitlePhraseName($prefixId)
    {
        return 'classifieds_prefix_' . $prefixId;
    }

    public function getPrefixGroupTitlePhraseName($groupId)
    {
        return 'classifieds_prefix_group_' . $groupId;
    }

    public function getPrefixMasterTitlePhraseValue($prefixId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getPrefixTitlePhraseName($prefixId)
        );
    }

    public function getPrefixGroupMasterTitlePhraseValue($groupId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getPrefixGroupTitlePhraseName($groupId)
        );
    }
} 