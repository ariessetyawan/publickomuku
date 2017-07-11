<?php
// Team NullXF

class phc_AdvancedRules_DataWriter_AR extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'phc_advanced_rules' => array(
				'ar_id'	        => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'title'			=> array('type' => self::TYPE_STRING, 'maxLength' => 250, 'required' => true, 'requiredError' => 'ar_enter_valid_title'),
				'text'			=> array('type' => self::TYPE_STRING, 'required' => true, 'requiredError' => 'ar_enter_valid_text'),
                'active'        => array('type' => self::TYPE_UINT, 'default' => 1, 'required' => true),
                'time'          => array('type' => self::TYPE_UINT, 'default' => 10),

                'actions'       => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
                'node_ids'      => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
                'group_ids'     => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if(!$ar_id = $this->_getExistingPrimaryKey($data, 'ar_id'))
		{
			return false;
		}
		return array('phc_advanced_rules' => $this->_getARModel()->fetchARByID($ar_id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'ar_id = ' . $this->_db->quote($this->getExisting('ar_id'));
	}

    protected function _postSave()
    {
        $this->_getARModel()->rebuildARCache();
    }

	protected function _postDelete()
	{
        $db = $this->_db;
        $db->delete('phc_advanced_rules_accepted', 'ar_id = ' . $this->get('ar_id'));
        $this->_getARModel()->rebuildARCache();
	}

    protected function _getARModel()
    {
        return $this->getModelFromCache('phc_AdvancedRules_Model_ARModel');
    }
}