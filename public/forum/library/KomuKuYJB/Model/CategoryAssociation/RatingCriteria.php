<?php /*683855e310c3f9f7d9883ae750b3ae240086db09*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_CategoryAssociation_RatingCriteria extends XenForo_Model
{
    public function getAssociationByCategory($categoryId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT criteria_id
            FROM kmk_classifieds_rating_criteria_category
            WHERE category_id = ?', $categoryId
        );
    }

    public function getAssociationByRatingCriteria($criteriaId)
    {
        return $this->_getDb()->fetchCol(
            'SELECT category_id
            FROM kmk_classifieds_rating_criteria_category
            WHERE criteria_id = ?', $criteriaId
        );
    }

    public function rebuildAssociationCache($categoryIds = null)
    {
        if ($categoryIds === null)
        {
            $categoryIds = $this->_getDb()->fetchCol('SELECT DISTINCT category_id FROM kmk_classifieds_rating_criteria_category');
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

        /** @var KomuKuYJB_Model_TraderRatingCriteria $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_TraderRatingCriteria');

        foreach ($model->getCriteriaInCategories($categoryIds) as $criteria)
        {
            $cache[$criteria['category_id']][$criteria['criteria_id']] = $criteria['criteria_id'];
        }

        XenForo_Db::beginTransaction($db);

        foreach ($categoryIds as $categoryId)
        {
            $update = isset($cache[$categoryId]) ? XenForo_Helper_Php::safeSerialize($cache[$categoryId]) : '';
            $db->update('kmk_classifieds_category', array('rating_criteria_cache' => $update), 'category_id = ' . $db->quote($categoryId));
        }

        XenForo_Db::commit($db);
    }

    public function updateAssociationByCategory($categoryId, array $criteriaIds)
    {
        $criteriaIds = array_unique($criteriaIds);

        $emptyCriteriaKey = array_search(0, $criteriaIds);
        if ($emptyCriteriaKey !== false)
        {
            unset ($criteriaIds[$emptyCriteriaKey]);
        }

        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_rating_criteria_category', 'category_id = ' . $db->quote($categoryId));

        foreach ($criteriaIds as $criteriaId)
        {
            $db->insert('kmk_classifieds_rating_criteria_category', array('category_id' => $categoryId, 'criteria_id' => $criteriaId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryId);
    }

    public function updateAssociationByRatingCriteria($criteriaId, array $categoryIds)
    {
        $categoryIds = array_unique($categoryIds);

        $emptyCategoryKey = array_search(0, $categoryIds);
        if ($emptyCategoryKey !== false)
        {
            unset ($categoryIds[$emptyCategoryKey]);
        }

        $db = $this->_getDb();

        $existingCategoryIds = $db->fetchCol('SELECT category_id FROM kmk_classifieds_rating_criteria_category WHERE criteria_id = ?', $criteriaId);
        if (!$categoryIds && !$existingCategoryIds)
        {
            return;
        }

        XenForo_Db::beginTransaction($db);

        $db->delete('kmk_classifieds_rating_criteria_category', 'criteria_id = ' . $db->quote($criteriaId));

        foreach ($categoryIds as $categoryId)
        {
            $db->insert('kmk_classifieds_rating_criteria_category', array('category_id' => $categoryId, 'criteria_id' => $criteriaId));
        }

        XenForo_Db::commit($db);

        $this->rebuildAssociationCache($categoryIds);
    }

    public function removeAssociationByCategory($categoryId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_rating_criteria_category', 'category_id = ' . $db->quote($categoryId));
        $this->rebuildAssociationCache();
    }

    public function removeAssociationByRatingCriteria($criteriaId)
    {
        $db = $this->_getDb();
        $db->delete('kmk_classifieds_rating_criteria_category', 'criteria_id = ' . $db->quote($criteriaId));
        $this->rebuildAssociationCache();
    }
}