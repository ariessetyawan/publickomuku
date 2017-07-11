<?php /*afcb08f66990fea7d3b4ae72b47faae834361af4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_TraderRatingCriteria extends XenForo_Model
{
    const FETCH_CATEGORY = 0x01;

    public function getRatingCriteriaById($criteriaId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_rating_criteria
            WHERE criteria_id = ?', $criteriaId
        );
    }

    public function getRatingCriterias(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareRatingCriteriaConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareRatingCriteriaFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT criteria.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_rating_criteria AS criteria
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
            ORDER BY criteria.display_order ASC', 'criteria_id'
        );
    }

    public function getAllRatingCriterias(array $fetchOptions = array())
    {
        return $this->getRatingCriterias(array(), $fetchOptions);
    }

    public function getRatingCriteriasForEdit($categoryId = 0, $feedbackId = 0)
    {
        $fetchOptions = array(
            'categoryId' => $categoryId,
            'valueFeedbackId' => $feedbackId
        );

        if (!$categoryId)
        {
            return $this->getRatingCriterias(array(), $fetchOptions);
        }

        $globals = $this->getRatingCriteriasForEdit(0, $feedbackId);
        $others = $this->getRatingCriterias(array(), $fetchOptions);

        return XenForo_Application::mapMerge($globals, $others);
    }

    public function getRatingCriteriasByClassifiedForEdit($classifiedId = 0, $feedbackId = 0)
    {
        $fetchOptions = array(
            'classifiedId' => $classifiedId,
            'valueFeedbackId' => $feedbackId
        );

        if (!$classifiedId)
        {
            return $this->getRatingCriterias(array(), $fetchOptions);
        }

        $globals = $this->getRatingCriteriasForEdit(0, $feedbackId);
        $others = $this->getRatingCriterias(array(), $fetchOptions);

        return XenForo_Application::mapMerge($globals, $others);
    }

    public function prepareRatingCriteriaConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (
            (isset($fetchOptions['categoryId']) && !$fetchOptions['categoryId'])
            || (isset($fetchOptions['classifiedId']) && !$fetchOptions['classifiedId'])
        )
        {
            $sqlConditions[] = 'is_global = 1';
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareRatingCriteriaFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['categoryId']))
        {
            $joinTables .= ' INNER JOIN kmk_classifieds_rating_criteria_category AS category_assoc
                                ON (
                                    category_assoc.criteria_id = criteria.criteria_id
                                    AND category_assoc.category_id = ' . $db->quote($fetchOptions['categoryId']) . '
                                )';
        }

        if (!empty($fetchOptions['classifiedId']))
        {
            $joinTables .= ' INNER JOIN kmk_classifieds_classified AS classified
                                ON (
                                    classified.classified_id = ' . $db->quote($fetchOptions['classifiedId']) . '
                                )
                            INNER JOIN kmk_classifieds_rating_criteria_category AS category_assoc
                                ON (
                                    category_assoc.criteria_id = criteria.criteria_id
                                    AND category_assoc.category_id = classified.category_id
                                )';
        }

        if (!empty($fetchOptions['valueFeedbackId']))
        {
            $selectFields .= ', criteria_feedback.rating, criteria_feedback.message';
            $joinTables .= ' LEFT JOIN kmk_classifieds_rating_criteria_feedback AS criteria_feedback
                                ON (
                                    criteria.criteria_id = criteria_feedback.criteria_id
                                    AND criteria_feedback.feedback_id = ' . $db->quote($fetchOptions['valueFeedbackId']) . '
                                )';
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function getRatingCriteriaFeedbacks($feedbackId)
    {
        $criterias = $this->_getDb()->fetchAll(
            'SELECT *
            FROM kmk_classifieds_rating_criteria_feedback
            WHERE feedback_id = ?', $feedbackId
        );

        $feedbacks = array();

        foreach ($criterias as $criteria)
        {
            $feedbacks[$criteria['criteria_id']] = array(
                'rating' => $criteria['rating'],
                'message' => $criteria['message']
            );
        }

        return $feedbacks;
    }

    public function getCriteriaInCategories(array $categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT criteria.*, category_assoc.category_id
            FROM kmk_classifieds_rating_criteria AS criteria
            LEFT JOIN kmk_classifieds_rating_criteria_category AS category_assoc
              ON (category_assoc.criteria_id = criteria.criteria_id)
            WHERE category_assoc.category_id IN (' . $db->quote($categoryIds) . ')
            ORDER BY criteria.display_order'
        );
    }

    public function prepareRatingCriteria(array $criteria, $criteriaFeedback = null, $valueSaved = true)
    {
        if (!isset($criteria['title']))
        {
            $criteria['title'] = new XenForo_Phrase(
                $this->getRatingCriteriaTitlePhraseName($criteria['criteria_id'])
            );
        }

        if (!isset($criteria['description']))
        {
            $criteria['description'] = new XenForo_Phrase(
                $this->getRatingCriteriaDescriptionPhraseName($criteria['criteria_id'])
            );
        }

        if ($criteriaFeedback === null && isset($criteria['criteria_feedback']))
        {
            $criteriaFeedback = $criteria['criteria_feedback'];
        }

        $criteria['criteria_feedback'] = $criteriaFeedback;
        $criteria['hasValue'] = $valueSaved && ((is_string($criteriaFeedback) && $criteriaFeedback !== '') || (!is_string($criteriaFeedback) && $criteriaFeedback));

        return $criteria;
    }

    public function prepareRatingCriterias(array $criterias, array $criteriaFeedbacks = array(), $valueSaved = true)
    {
        foreach ($criterias as &$criteria)
        {
            $feedback = isset($criteriaFeedbacks[$criteria['criteria_id']]) ? $criteriaFeedbacks[$criteria['criteria_id']] : null;
            $criteria = $this->prepareRatingCriteria($criteria, $feedback, $valueSaved);
        }

        return $criterias;
    }

    public function getRatingCriteriaTitlePhraseName($criteriaId)
    {
        return 'classifieds_rating_criteria_' . $criteriaId;
    }

    public function getRatingCriteriaDescriptionPhraseName($criteriaId)
    {
        return 'classifieds_rating_criteria_' . $criteriaId . '_desc';
    }

    public function getRatingCriteriaMasterTitlePhraseValue($criteriaId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getRatingCriteriaTitlePhraseName($criteriaId)
        );
    }

    public function getRatingCriteriaMasterDescriptionPhraseValue($criteriaId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getRatingCriteriaDescriptionPhraseName($criteriaId)
        );
    }

    /**
     * @return XenForo_Model_Phrase
     */
    protected function _getPhraseModel()
    {
        return $this->getModelFromCache('XenForo_Model_Phrase');
    }
}