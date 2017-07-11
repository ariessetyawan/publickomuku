<?php /*9ddbf0d5409f57fbc934c06f8d06191b7ec16751*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 9
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_AdvertType extends GFNClassifieds_Model
{
    const FETCH_CATEGORY = 0x01;

    public function getAdvertTypeById($advertTypeId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareAdvertTypeFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            "SELECT advert_type.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_advert_type AS advert_type
            {$joinOptions['joinTables']}
            WHERE advert_type.advert_type_id = ?", $advertTypeId
        );
    }

    public function getAllAdvertTypes(array $fetchOptions = array())
    {
        return $this->getAdvertTypes(array(), $fetchOptions);
    }

    public function getAdvertTypes(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareAdvertTypeConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareAdvertTypeFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            "SELECT advert_type.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_advert_type AS advert_type
            {$joinOptions['joinTables']}
            WHERE {$whereClause}
            ORDER BY display_order ASC", 'advert_type_id'
        );
    }

    public function getVisibleAdvertTypeIds(array $viewingUser = null, array $categoryIds = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        /** @var GFNClassifieds_Model_Category $categoryModel */
        $categoryModel = $this->getModelFromCache('GFNClassifieds_Model_Category');
        $advertTypes = array();

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
            'SELECT advert_type.advert_type_id, category.*, cache.cache_value AS category_permission_cache
            FROM kmk_classifieds_advert_type AS advert_type
            INNER JOIN kmk_classifieds_advert_type_category AS assoc ON (assoc.advert_type_id = advert_type.advert_type_id)
            INNER JOIN kmk_classifieds_category AS category ON (assoc.category_id = category.category_id' . $categoryLimit . ')
            INNER JOIN kmk_permission_cache_content AS cache ON
                (cache.content_type = \'classified_category\' AND cache.content_id = category.category_id AND cache.permission_combination_id = ?)
            ORDER BY advert_type.display_order', $viewingUser['permission_combination_id']
        );

        while ($result = $stmt->fetch())
        {
            if (isset($advertTypes[$result['advert_type_id']]))
            {
                continue;
            }

            $permissions = XenForo_Permission::unserializePermissions($result['category_permission_cache']);

            if ($categoryModel->canViewCategory($result, $null, $viewingUser, $permissions))
            {
                $advertTypes[$result['advert_type_id']] = $result['advert_type_id'];
            }
        }

        return $advertTypes;
    }

    public function prepareAdvertTypeFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category_assoc.category_id';
                $joinTables .= ' INNER JOIN kmk_classifieds_advert_type_category AS category_assoc
                                    ON (category_assoc.advert_type_id = advert_type.advert_type_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function prepareAdvertTypeConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (isset($conditions['advert_type_ids']))
        {
            $sqlConditions[] = 'advert_type.advert_type_id IN (' . $db->quote($conditions['advert_type_ids']) . ')';
        }

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

    public function prepareAdvertType(array &$advertType)
    {
        $advertType['title'] = new XenForo_Phrase(
            $this->getAdvertTypeTitlePhraseName($advertType['advert_type_id'])
        );

        $advertType['zero_value_text'] = new XenForo_Phrase(
            $this->getZeroValueTextPhraseName($advertType['advert_type_id'])
        );

        $advertType['complete_text'] = new XenForo_Phrase(
            $this->getCompleteTextPhraseName($advertType['advert_type_id'])
        );

        return $advertType;
    }

    public function getAdvertTypeOptions(array $selectedAdvertTypes = array(0), array $advertTypes = null)
    {
        if ($advertTypes === null)
        {
            $advertTypes = $this->getAllAdvertTypes();
        }

        $options = array();

        foreach ($advertTypes as $advertType)
        {
            $options[] = array(
                'value' => $advertType['advert_type_id'],
                'label' => new XenForo_Phrase($this->getAdvertTypeTitlePhraseName($advertType['advert_type_id'])),
                'selected' => in_array($advertType['advert_type_id'], $selectedAdvertTypes)
            );
        }

        return $options;
    }

    public function verifyAdvertTypeIsUsable($advertTypeId, $categoryId, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $advertType = $this->getAdvertTypeIfInCategory($advertTypeId, $categoryId);
        return $advertType ? true : false;
    }

    public function getAdvertTypeIfInCategory($advertTypeId, $categoryId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT advert_type.*
            FROM kmk_classifieds_advert_type AS advert_type
            INNER JOIN kmk_classifieds_advert_type_category AS assoc
            ON (assoc.advert_type_id = advert_type.advert_type_id AND assoc.category_id = ?)
            WHERE advert_type.advert_type_id = ?', array($categoryId, $advertTypeId)
        );
    }

    public function getUsableAdvertTypesInCategories($categoryIds)
    {
        $advertTypes = $this->getAdvertTypesInCategories($categoryIds);
        return $this->prepareAdvertTypes($advertTypes);
    }

    public function getAdvertTypesInCategories($categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT advert_type.*, category_assoc.category_id
            FROM kmk_classifieds_advert_type AS advert_type
            INNER JOIN kmk_classifieds_advert_type_category AS category_assoc
              ON (advert_type.advert_type_id = category_assoc.advert_type_id)
            WHERE category_assoc.category_id IN (' . $db->quote($categoryIds) . ')
            ORDER BY advert_type.display_order'
        );
    }

    public function prepareAdvertTypes(array &$advertTypes)
    {
        array_walk($advertTypes, array($this, 'prepareAdvertType')); return $advertTypes;
    }

    public function getAdvertTypeTitlePhraseName($advertTypeId)
    {
        return 'classifieds_advert_type_' . $advertTypeId . '_title';
    }

    public function getZeroValueTextPhraseName($advertTypeId)
    {
        return 'classifieds_advert_type_' . $advertTypeId . '_zero_value_text';
    }

    public function getCompleteTextPhraseName($advertTypeId)
    {
        return 'classifieds_advert_type_' . $advertTypeId . '_complete_text';
    }

    public function getAdvertTypeMasterTitlePhraseValue($advertTypeId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getAdvertTypeTitlePhraseName($advertTypeId)
        );
    }

    public function getZeroValueTextMasterTitlePhraseValue($advertTypeId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getZeroValueTextPhraseName($advertTypeId)
        );
    }

    public function getCompleteTextMasterTitlePhraseValue($advertTypeId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getCompleteTextPhraseName($advertTypeId)
        );
    }

    public function getAdvertTypesFromCache()
    {
        return GFNCore_Registry::get('classifiedAdvertTypes') ?: $this->rebuildAdvertTypeCache();
    }

    public function rebuildAdvertTypeCache()
    {
        $cache = array();

        foreach ($this->getAllAdvertTypes() as $advertType)
        {
            $cache[$advertType['advert_type_id']] = array(
                'advert_type_id' => $advertType['advert_type_id'],
                'show_badge' => $advertType['show_badge'],
                'badge_color' => $advertType['badge_color'],
                'complete_badge_color' => $advertType['complete_badge_color']
            );
        }

        GFNCore_Registry::set('classifiedAdvertTypes', $cache);
        return $cache;
    }
} 