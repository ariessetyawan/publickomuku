<?php

/**
* Data writer for Directory (clone of nodes).
*
* 
*/
class KomuKuJVC_DataWriter_Directory extends XenForo_DataWriter
{

	protected function _getFields()
	{
		return array(
			'kmk_jobvacan_directory_node' => array(
				'node_id'            => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'title'              => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
						'requiredError' => 'please_enter_valid_title'
				),
				'node_name'          => array('type' => self::TYPE_STRING, 'default' => null, 'verification' => array('$this', '_verifyNodeName'), 'maxLength' => 50),
				'description'        => array('type' => self::TYPE_STRING, 'default' => ''),
				'node_type_id'       => array('type' => self::TYPE_BINARY, 'required' => true, 'maxLength' => 25),
				'parent_node_id'     => array('type' => self::TYPE_UINT, 'default' => 0, 'required' => true),
				'display_order'      => array('type' => self::TYPE_UINT, 'default' => 1),
				'lft'                => array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'rgt'                => array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'depth'              => array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'style_id'           => array('type' => self::TYPE_UINT, 'default' => 0),
				'effective_style_id' => array('type' => self::TYPE_UINT, 'default' => 0),
				'display_in_list'    => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
			)
		);
	}


	protected function _getNodeModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_Directory');
	}


	protected function _getExistingData($data)
	{
		if (!$nodeId = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('kmk_jobvacan_directory_node' => $this->_getNodeModel()->getNodeById($nodeId));
	}


	protected function _getUpdateCondition($tableName)
	{
		return 'node_id = ' . $this->_db->quote($this->getExisting('node_id'));
	}


}