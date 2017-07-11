<?php /*d08e3da39f0994aa02a4f1c892dd0d5d3666b23b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_CategoryAssociation_Field extends XenForo_Model
{
    public function getAssociationByCategory($categoryId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT field_id
            FROM kmk_classifieds_field_category
            WHERE category_id = ?', $categoryId
        );
    }

    public function getAssociationByField($fieldId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT category_id
            FROM kmk_classifieds_field_category
            WHERE field_id = ?', $fieldId
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

        /** @var GFNClassifieds_Model_Field $model */
        $model = $this->getModelFromCache('GFNClassifieds_Model_Field');

        foreach ((array) $model->getFieldsInCategories($categoryIds) as $field)
        {
            $cache[$field['category_id']][$field['display_group']][$field['field_id']] = $field['field_id'];
        }

        XenForo_Db::beginTransaction($db);

        foreach ($categoryIds as $categoryId)
        {
            $update = isset($cache[$categoryId]) ? XenForo_Helper_Php::safeSerialize($cache[$categoryId]) : '';
            $db->update('kmk_classifieds_category', array('field_cache' => $update), 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
    }

    public function updateAssociationByCategory($categoryId, array $fieldIds)
    {
        $fieldIds = array_unique($fieldIds);

        $emptyFieldKey = array_search('', $fieldIds);
        if ($emptyFieldKey !== false)
        {
            unset ($fieldIds[$emptyFieldKey]);
        }

        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_field_category', 'category_id = ' . $db->quote($categoryId));

        foreach ($fieldIds as $id)
        {
            $db->insert('kmk_classifieds_field_category', array('category_id' => $categoryId, 'field_id' => $id));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryId);
    }

    public function updateAssociationByField($fieldId, array $categoryIds)
    {
        $categoryIds = array_unique($categoryIds);

        $emptyCategoryKey = array_search(0, $categoryIds);
        if ($emptyCategoryKey !== false)
        {
            unset ($categoryIds[$emptyCategoryKey]);
        }

        $db = $this->_getDb();

        $existingCategoryIds = $db->fetchCol('SELECT category_id FROM kmk_classifieds_field_category WHERE field_id = ?', $fieldId);
        if (!$categoryIds && !$existingCategoryIds)
        {
            return;
        }

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_field_category', 'field_id = ' . $db->quote($fieldId));

        foreach ($categoryIds as $id)
        {
            $db->insert('kmk_classifieds_field_category', array('category_id' => $id, 'field_id' => $fieldId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryIds);
    }

    public function removeAssociationByCategory($categoryId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_field_category', 'category_id = ' . $db->quote($categoryId));
        $this->rebuildAssociationCache();
    }

    public function removeAssociationByField($fieldId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_field_category', 'field_id = ' . $db->quote($fieldId));
        $this->rebuildAssociationCache();
    }
}