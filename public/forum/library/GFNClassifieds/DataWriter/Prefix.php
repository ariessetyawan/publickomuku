<?php /*06b634d071e928b99bdafd024ee3a6b85fe2074c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 8
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_Prefix extends XenForo_DataWriter
{
    const DATA_TITLE = 'phraseTitle';
    const DATA_CATEGORIES = 'categories';

    const OPTION_MASS_UPDATE = 'massUpdate';

    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_MASS_UPDATE => false
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_prefix' => array(
                'prefix_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'prefix_group_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'materialized_order' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'css_class' => array(
                    'type' => self::TYPE_STRING,
                    'maxLength' => 50,
                    'default' => ''
                ),
                'allowed_user_group_ids' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'default' => '',
                    'verification' => array('$this', '_validateAllowedUserGroupIds')
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $prefixId = $this->_getExistingPrimaryKey($data);
        $prefix = $this->_getPrefixModel()->getPrefixById($prefixId);

        if (!$prefix)
        {
            return false;
        }

        return array('kmk_classifieds_prefix' => $prefix);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'prefix_id = ' . $this->_db->quote($this->getExisting('prefix_id'));
    }

    protected function _preSave()
    {
        if (!$this->getOption(self::OPTION_MASS_UPDATE))
        {
            $titlePhrase = $this->getExtraData(self::DATA_TITLE);
            if ($titlePhrase !== null && strlen($titlePhrase) == 0)
            {
                $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
            }
        }
    }

    protected function _postSave()
    {
        if (!$this->getOption(self::OPTION_MASS_UPDATE))
        {
            $prefixId = $this->get('prefix_id');

            $titlePhrase = $this->getExtraData(self::DATA_TITLE);
            if ($titlePhrase !== null)
            {
                $this->_insertOrUpdateMasterPhrase(
                    $this->_getTitlePhraseName($prefixId), $titlePhrase,
                    '', array('global_cache' => 1)
                );
            }

            if ($this->isChanged('display_order') || $this->isChanged('prefix_group_id'))
            {
                $this->_getPrefixModel()->rebuildPrefixMaterializedOrder();
            }

            $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
            if (is_array($categoryIds))
            {
                $this->_getAssociationModel()->prefix()->updateAssociationByPrefix($prefixId, $categoryIds);
            }

            $this->_getPrefixModel()->rebuildPrefixCache();
        }
    }

    protected function _postDelete()
    {
        $prefixId = $this->get('prefix_id');
        $db = $this->_db;

        $db->update('kmk_classifieds_classified', array('prefix_id' => 0), 'prefix_id = ' . $db->quote($prefixId));

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($prefixId));

        $this->_getPrefixModel()->rebuildPrefixCache();
        $this->_getAssociationModel()->prefix()->removeAssociationByPrefix($prefixId);
    }

    /**
     * @return GFNClassifieds_Model_Prefix
     */
    protected function _getPrefixModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Prefix');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation');
    }

    protected function _getTitlePhraseName($prefixId)
    {
        return $this->_getPrefixModel()->getPrefixTitlePhraseName($prefixId);
    }

    protected function _validateAllowedUserGroupIds(&$data)
    {
        if (!is_array($data))
        {
            $data = preg_split('#,\s*#', $data);
        }

        $data = array_map('intval', $data);
        $data = array_unique($data);
        sort($data, SORT_NUMERIC);
        $data = implode(',', $data);
        return true;
    }
}