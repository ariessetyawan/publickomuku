<?php

/**
* DataWriter for the ThreadMap: 
* // I'm curretly working on this to write all directory data in for KomuKuJVC_ControllerPublic_Directory::actionDirupdate
* // I will then make it work for create action too!
* @package XenForo_Node
*/
class KomuKuJVC_DataWriter_ThreadMap extends XenForo_DataWriter
{

	protected $_existingDataErrorPhrase = 'requested_page_not_found';
	
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'kmk_jobvacan_thread_map' => array(			
				'directory_category' => array('type' => self::TYPE_UINT, 'required' => true, 'maxLength' => 10 ),
				
				'cat2' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 10 ),
				'cat3' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 10 ),
				'cat4' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 10 ),
				'cat5' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 10 ),
				
				'cat1title' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50 ),
				'cat2title' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50 ),
				'cat3title' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50 ),
				'cat4title' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50 ),
				'cat5title' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50 ),
				
				'thread_id' => array('type' => self::TYPE_UINT, 'required' => true, 'maxLength' => 10 ),
				'telephone' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'address_line_1' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'address_line_2' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'town_city' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'postcode' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'website_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'website_anchor_text' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'deeplink1_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'deeplink1_anchor_text' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'deeplink2_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'deeplink2_anchor_text' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),
				'deeplink3_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'deeplink3_anchor_text' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 100 ),			
				'logo_image_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'youtube_url' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfielda1' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfielda2' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfielda3' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfielda4' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfieldb1' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfieldb2' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfieldb3' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),
				'customfieldb4' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 200 ),			
				'is_claimable' => array('type' => self::TYPE_UINT, 'default' => 0, 'maxLength' => 1 ),	

			)
		);
	}

	
	


	protected function _getExistingData($data)
	{
		if (!$thread_id = $this->_getExistingPrimaryKey($data, 'thread_id'))
		{
			return false;
		}

		return array('kmk_jobvacan_thread_map' => $this->getModelFromCache('KomuKuJVC_Model_ThreadMap')->getThreadMapById($thread_id));
	}

	protected function _getUpdateCondition($tableName)	
	{
		return 'thread_id = ' . $this->_db->quote($this->getExisting('thread_id'));
	}


}