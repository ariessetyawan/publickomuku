<?php /*512809a88dec7fcdf8b93829a0b64045fb7263d6*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 9
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_AdvertType extends XenForo_DataWriter
{
    const DATA_TITLE = 'phraseTitle';
    const DATA_ZERO_VALUE_TEXT = 'phraseZeroText';
    const DATA_COMPLETE_TEXT = 'phraseCompleteText';
    const DATA_CATEGORIES = 'categories';

    const OPTION_REBUILD_CACHE = 'rebuildCache';

    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_REBUILD_CACHE => true
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_advert_type' => array(
                'advert_type_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'badge_color' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 30
                ),
                'complete_badge_color' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 30
                ),
                'show_badge' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                ),
                'classified_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $advertTypeId = $this->_getExistingPrimaryKey($data);
        $type = $this->_getAdvertTypeModel()->getAdvertTypeById($advertTypeId);

        if (!$type)
        {
            return false;
        }

        return array('kmk_classifieds_advert_type' => $type);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'advert_type_id = ' . $this->_db->quote($this->getExisting('advert_type_id'));
    }

    protected function _preSave()
    {
        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }

        $zeroValueTextPhrase = $this->getExtraData(self::DATA_ZERO_VALUE_TEXT);
        if ($zeroValueTextPhrase !== null && strlen($zeroValueTextPhrase) == 0)
        {
            $this->setExtraData(self::DATA_ZERO_VALUE_TEXT, false);
        }

        $completeTextPhrase = $this->getExtraData(self::DATA_COMPLETE_TEXT);
        if ($completeTextPhrase !== null && strlen($completeTextPhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_complete_text'), 'complete_text');
        }
    }

    protected function _postSave()
    {
        $advertTypeId = $this->get('advert_type_id');

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getTitlePhraseName($advertTypeId), $titlePhrase,
                '', array('global_cache' => 1)
            );
        }

        $zeroValueTextPhrase = $this->getExtraData(self::DATA_ZERO_VALUE_TEXT);
        if ($zeroValueTextPhrase === false)
        {
            $this->_deleteMasterPhrase($this->_getZeroValueTextPhraseName($advertTypeId));
        }
        elseif ($zeroValueTextPhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getZeroValueTextPhraseName($advertTypeId), $zeroValueTextPhrase,
                '', array('global_cache' => 1)
            );
        }

        $completeTextPhrase = $this->getExtraData(self::DATA_COMPLETE_TEXT);
        if ($completeTextPhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getCompleteTextPhraseName($advertTypeId), $completeTextPhrase,
                '', array('global_cache' => 1)
            );
        }

        $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
        if (is_array($categoryIds))
        {
            $this->_getAssociationModel()->advertType()->updatedAssociationByAdvertType($advertTypeId, $categoryIds);
        }

        if ($this->getOption(self::OPTION_REBUILD_CACHE))
        {
            $this->_getAdvertTypeModel()->rebuildAdvertTypeCache();
        }
    }

    protected function _postDelete()
    {
        $advertTypeId = $this->get('advert_type_id');

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($advertTypeId));
        $this->_deleteMasterPhrase($this->_getZeroValueTextPhraseName($advertTypeId));
        $this->_deleteMasterPhrase($this->_getCompleteTextPhraseName($advertTypeId));

        $this->_getAssociationModel()->advertType()->removeAssociationByAdvertType($advertTypeId);

        if ($this->getOption(self::OPTION_REBUILD_CACHE))
        {
            $this->_getAdvertTypeModel()->rebuildAdvertTypeCache();
        }
    }

    /**
     * @return GFNClassifieds_Model_AdvertType
     */
    protected function _getAdvertTypeModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_AdvertType');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation');
    }

    protected function _getTitlePhraseName($advertTypeId)
    {
        return $this->_getAdvertTypeModel()->getAdvertTypeTitlePhraseName($advertTypeId);
    }

    protected function _getZeroValueTextPhraseName($advertTypeId)
    {
        return $this->_getAdvertTypeModel()->getZeroValueTextPhraseName($advertTypeId);
    }

    protected function _getCompleteTextPhraseName($advertTypeId)
    {
        return $this->_getAdvertTypeModel()->getCompleteTextPhraseName($advertTypeId);
    }
} 