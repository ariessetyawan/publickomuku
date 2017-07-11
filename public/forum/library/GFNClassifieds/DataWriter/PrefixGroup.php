<?php /*4ff6bf4801b9bfec74f2c018b83be9ebd61966cc*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_PrefixGroup extends XenForo_DataWriter
{
    const DATA_TITLE = 'phraseTitle';

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_prefix_group' => array(
                'prefix_group_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $groupId = $this->_getExistingPrimaryKey($data);
        $group = $this->_getPrefixModel()->getPrefixGroupById($groupId);

        if (!$group)
        {
            return false;
        }

        return array('kmk_classifieds_prefix_group' => $group);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'prefix_group_id = ' . $this->_db->quote($this->getExisting('prefix_group_id'));
    }

    protected function _preSave()
    {
        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }
    }

    protected function _postSave()
    {
        $groupId = $this->get('prefix_group_id');

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getTitlePhraseName($groupId), $titlePhrase,
                '', array('global_cache' => 1)
            );
        }

        if ($this->isChanged('display_order'))
        {
            $this->_getPrefixModel()->rebuildPrefixMaterializedOrder();
        }

        $this->_getPrefixModel()->rebuildPrefixCache();
    }

    protected function _postDelete()
    {
        $groupId = $this->get('prefix_group_id');
        $this->_deleteMasterPhrase($this->_getTitlePhraseName($groupId));

        $this->_db->update('kmk_classifieds_prefix_group', array('prefix_group_id' => 0), 'prefix_group_id = ' . $this->_db->quote($groupId));

        $this->_getPrefixModel()->rebuildPrefixMaterializedOrder();
        $this->_getPrefixModel()->rebuildPrefixCache();
    }

    /**
     * @return GFNClassifieds_Model_Prefix
     */
    protected function _getPrefixModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Prefix');
    }

    protected function _getTitlePhraseName($packageId)
    {
        return $this->_getPrefixModel()->getPrefixGroupTitlePhraseName($packageId);
    }
} 