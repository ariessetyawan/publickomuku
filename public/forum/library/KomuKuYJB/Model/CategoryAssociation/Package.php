<?php /*a2708e485718796db77f4ca297f57ea06af9dacc*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_CategoryAssociation_Package extends XenForo_Model
{
    public function getAssociationByCategory($categoryId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT package_id
            FROM kmk_classifieds_package_category
            WHERE category_id = ?', $categoryId
        );
    }

    public function getAssociationByPackage($packageId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT category_id
            FROM kmk_classifieds_package_category
            WHERE package_id = ?', $packageId
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

        /** @var KomuKuYJB_Model_Package $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_Package');

        foreach ((array) $model->getPackagesInCategories($categoryIds) as $package)
        {
            $cache[$package['category_id']][$package['package_id']] = $package['package_id'];
        }

        XenForo_Db::beginTransaction($db);

        foreach ($categoryIds as $categoryId)
        {
            $update = isset($cache[$categoryId]) ? XenForo_Helper_Php::safeSerialize($cache[$categoryId]) : '';
            $db->update('kmk_classifieds_category', array('package_cache' => $update), 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
    }

    public function updateAssociationByCategory($categoryId, array $packageIds)
    {
        $packageIds = array_unique($packageIds);

        $emptyPackageKey = array_search(0, $packageIds);
        if ($emptyPackageKey !== false)
        {
            unset ($packageIds[$emptyPackageKey]);
        }

        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_package_category', 'category_id = ' . $db->quote($categoryId));

        foreach ($packageIds as $id)
        {
            $db->insert('kmk_classifieds_package_category', array('category_id' => $categoryId, 'package_id' => $id));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryId);
    }

    public function updateAssociationByPackage($packageId, array $categoryIds)
    {
        $categoryIds = array_unique($categoryIds);

        $emptyCategoryKey = array_search(0, $categoryIds);
        if ($emptyCategoryKey !== false)
        {
            unset ($categoryIds[$emptyCategoryKey]);
        }

        $db = $this->_getDb();

        $existingCategoryIds = $db->fetchCol('SELECT category_id FROM kmk_classifieds_package_category WHERE package_id = ?', $packageId);
        if (!$categoryIds && !$existingCategoryIds)
        {
            return;
        }

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_package_category', 'package_id = ' . $db->quote($packageId));

        foreach ($categoryIds as $id)
        {
            $db->insert('kmk_classifieds_package_category', array('category_id' => $id, 'package_id' => $packageId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryIds);
    }

    public function removeAssociationByCategory($categoryId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_package_category', 'category_id = ' . $db->quote($categoryId));
        $this->rebuildAssociationCache();
    }

    public function removeAssociationByPackage($packageId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_package_category', 'package_id = ' . $db->quote($packageId));
        $this->rebuildAssociationCache();
    }
}