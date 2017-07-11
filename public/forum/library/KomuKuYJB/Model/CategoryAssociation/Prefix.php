<?php /*89b4a1b0418b811a0c1084c4a7e192b470e7399c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_CategoryAssociation_Prefix extends XenForo_Model
{
    public function getAssociationByCategory($categoryId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT prefix_id
            FROM kmk_classifieds_prefix_category
            WHERE category_id = ?', $categoryId
        );
    }

    public function getAssociationByPrefix($prefixId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT category_id
            FROM kmk_classifieds_prefix_category
            WHERE prefix_id = ?', $prefixId
        );
    }

    public function rebuildAssociationCache($categoryIds = null)
    {
        if ($categoryIds === null)
        {
            $categoryIds = $this->_getDb()->fetchCol('SELECT category_id FROM kmk_classifieds_category');
        }

        if (!is_array($categoryIds))
        {
            $categoryIds = array($categoryIds);
        }

        if (!$categoryIds)
        {
            return;
        }

        $db = $this->_getDb();
        $cache = array();

        /** @var KomuKuYJB_Model_Prefix $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_Prefix');

        foreach ((array) $model->getPrefixesInCategories($categoryIds) as $prefix)
        {
            $cache[$prefix['category_id']][$prefix['prefix_group_id']][$prefix['prefix_id']] = $prefix['prefix_id'];
        }

        XenForo_Db::beginTransaction($db);

        foreach ($categoryIds as $categoryId)
        {
            $update = isset($cache[$categoryId]) ? XenForo_Helper_Php::safeSerialize($cache[$categoryId]) : '';
            $db->update('kmk_classifieds_category', array('prefix_cache' => $update), 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
    }

    public function updateAssociationByCategory($categoryId, array $prefixIds)
    {
        $prefixIds = array_unique($prefixIds);

        $emptyPrefixKey = array_search(0, $prefixIds);
        if ($emptyPrefixKey !== false)
        {
            unset ($prefixIds[$emptyPrefixKey]);
        }

        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_prefix_category', 'category_id = ' . $db->quote($categoryId));

        foreach ($prefixIds as $id)
        {
            $db->insert('kmk_classifieds_prefix_category', array('category_id' => $categoryId, 'prefix_id' => $id));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryId);
    }

    public function updateAssociationByPrefix($prefixId, array $categoryIds)
    {
        $categoryIds = array_unique($categoryIds);

        $emptyCategoryKey = array_search(0, $categoryIds);
        if ($emptyCategoryKey !== false)
        {
            unset ($categoryIds[$emptyCategoryKey]);
        }

        $db = $this->_getDb();

        $existingCategoryIds = $db->fetchCol('SELECT category_id FROM kmk_classifieds_prefix_category WHERE prefix_id = ?', $prefixId);
        if (!$categoryIds && !$existingCategoryIds)
        {
            return;
        }

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_prefix_category', 'prefix_id = ' . $db->quote($prefixId));

        foreach ($categoryIds as $id)
        {
            $db->insert('kmk_classifieds_prefix_category', array('category_id' => $id, 'prefix_id' => $prefixId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryIds);
    }

    public function removeAssociationByCategory($categoryId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_prefix_category', 'category_id = ' . $db->quote($categoryId));
        $this->rebuildAssociationCache();
    }

    public function removeAssociationByPrefix($prefixId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_prefix_category', 'prefix_id = ' . $db->quote($prefixId));
        $this->rebuildAssociationCache();
    }
}