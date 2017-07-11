<?php /*9632f0356c1b83d33c7eeb89f41b85c3aefe3903*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_TraderRatingCriteria extends XenForo_DataWriter
{
    const DATA_TITLE        = 'title';
    const DATA_DESCRIPTION  = 'description';
    const DATA_CATEGORIES   = 'applicableCategories';

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_rating_criteria' => array(
                'criteria_id' => array(
                    'type' => self::TYPE_STRING,
                    'maxLength' => 25,
                    'required' => true,
                    'verification' => array('$this', '_validateCriteriaId')
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                ),
                'required' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'is_global' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'show_message' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 1
                ),
                'require_message' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $criteriaId = $this->_getExistingPrimaryKey($data, 'criteria_id');
        if (!$criteriaId)
        {
            return false;
        }

        $criteria = $this->_getRatingCriteriaModel()->getRatingCriteriaById($criteriaId);
        if (!$criteria)
        {
            return false;
        }

        return array('kmk_classifieds_rating_criteria' => $criteria);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'criteria_id = ' . $this->_db->quote($this->getExisting('criteria_id'));
    }

    protected function _preSave()
    {
        if (!$this->get('show_message'))
        {
            $this->set('require_message', false);
        }

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null && strlen($descriptionPhrase) == 0)
        {
            $this->setExtraData(self::DATA_DESCRIPTION, false);
        }

        $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
        if (is_array($categoryIds) && sizeof($categoryIds) == 0)
        {
            $this->set('is_global', 1);
        }
    }

    protected function _postSave()
    {
        $criteriaId = $this->get('criteria_id');

        if ($this->isUpdate() && $this->isChanged('criteria_id'))
        {
            $this->_renameMasterPhrase(
                $this->_getTitlePhraseName($this->getExisting('criteria_id')),
                $this->_getTitlePhraseName($criteriaId)
            );

            $this->_renameMasterPhrase(
                $this->_getDescriptionPhraseName($this->getExisting('criteria_id')),
                $this->_getDescriptionPhraseName($criteriaId)
            );
        }

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getTitlePhraseName($criteriaId), $titlePhrase,
                '', array('global_cache' => 1)
            );
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getDescriptionPhraseName($criteriaId), $descriptionPhrase
            );
        }

        $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
        if (is_array($categoryIds))
        {
            $this->_getAssociationModel()->ratingCriteria()->updateAssociationByRatingCriteria($criteriaId, $categoryIds);
        }
    }

    protected function _postDelete()
    {
        $criteriaId = $this->get('criteria_id');

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($criteriaId));
        $this->_deleteMasterPhrase($this->_getDescriptionPhraseName($criteriaId));

        $this->_getAssociationModel()->ratingCriteria()->removeAssociationByRatingCriteria($criteriaId);
    }

    /**
     * @return GFNClassifieds_Model_TraderRatingCriteria
     */
    protected function _getRatingCriteriaModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_TraderRatingCriteria');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation');
    }

    protected function _validateCriteriaId(&$data)
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $data))
        {
            $this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'criteria_id');
            return false;
        }

        if ($data !== $this->getExisting('criteria_id') && $this->_getRatingCriteriaModel()->getRatingCriteriaById($data))
        {
            $this->error(new XenForo_Phrase('rating_criteria_ids_must_be_unique'), 'criteria_id');
            return false;
        }

        return true;
    }

    protected function _getTitlePhraseName($criteriaId)
    {
        return $this->_getRatingCriteriaModel()->getRatingCriteriaTitlePhraseName($criteriaId);
    }

    protected function _getDescriptionPhraseName($criteriaId)
    {
        return $this->_getRatingCriteriaModel()->getRatingCriteriaDescriptionPhraseName($criteriaId);
    }
}