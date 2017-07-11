<?php /*2813c4576d60a7ae9658777884880f043305bb3e*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_CategoryAssociation_AdvertType extends XenForo_Model
{
    public function getAssociationByCategory($categoryId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT advert_type_id
            FROM kmk_classifieds_advert_type_category
            WHERE category_id = ?', $categoryId
        );
    }

    public function getAssociationByAdvertType($advertTypeId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT category_id
            FROM kmk_classifieds_advert_type_category
            WHERE advert_type_id = ?', $advertTypeId
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

        /** @var KomuKuYJB_Model_AdvertType $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_AdvertType');

        foreach ((array) $model->getAdvertTypesInCategories($categoryIds) as $advertType)
        {
            $cache[$advertType['category_id']][$advertType['advert_type_id']] = $advertType['advert_type_id'];
        }

        XenForo_Db::beginTransaction($db);

        foreach ($categoryIds as $categoryId)
        {
            $update = isset($cache[$categoryId]) ? XenForo_Helper_Php::safeSerialize($cache[$categoryId]) : '';
            $db->update('kmk_classifieds_category', array('advert_type_cache' => $update), 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
    }

    public function updateAssociationByCategory($categoryId, array $advertTypeIds)
    {
        $advertTypeIds = array_unique($advertTypeIds);

        $emptyAdvertTypeKey = array_search(0, $advertTypeIds);
        if ($emptyAdvertTypeKey !== false)
        {
            unset ($advertTypeIds[$emptyAdvertTypeKey]);
        }

        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_advert_type_category', 'category_id = ' . $db->quote($categoryId));

        foreach ($advertTypeIds as $id)
        {
            $db->insert('kmk_classifieds_advert_type_category', array('category_id' => $categoryId, 'advert_type_id' => $id));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryId);
    }

    public function updatedAssociationByAdvertType($advertTypeId, array $categoryIds)
    {
        $categoryIds = array_unique($categoryIds);

        $emptyCategoryKey = array_search(0, $categoryIds);
        if ($emptyCategoryKey !== false)
        {
            unset ($categoryIds[$emptyCategoryKey]);
        }

        $db = $this->_getDb();

        $existingCategoryIds = $db->fetchCol('SELECT category_id FROM kmk_classifieds_advert_type_category WHERE advert_type_id = ?', $advertTypeId);
        if (!$categoryIds && !$existingCategoryIds)
        {
            return;
        }

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_advert_type_category', 'advert_type_id = ' . $db->quote($advertTypeId));

        foreach ($categoryIds as $id)
        {
            $db->insert('kmk_classifieds_advert_type_category', array('category_id' => $id, 'advert_type_id' => $advertTypeId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryIds);
    }

    public function removeAssociationByCategory($categoryId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_advert_type_category', 'category_id = ' . $db->quote($categoryId));
        $this->rebuildAssociationCache();
    }

    public function removeAssociationByAdvertType($advertTypeId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_advert_type_category', 'advert_type_id = ' . $db->quote($advertTypeId));
        $this->rebuildAssociationCache();
    }
}