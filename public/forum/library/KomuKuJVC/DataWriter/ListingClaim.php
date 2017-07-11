<?php

/**
* DataWriter for the ListingClaim: 
* @package XenForo_Node
*/
class KomuKuJVC_DataWriter_ListingClaim extends XenForo_DataWriter
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
			'kmk_jobvacan_listingclaim' => array(	
				'thread_url' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 200 ),
				'origanal_owner' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50 ),
				'claimant' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50 ),
				'claimant_email' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 200 ),
				'claimant_id' => array('type' => self::TYPE_UINT, 'required' => true, 'maxLength' => 10 ),				
				'thread_id' => array('type' => self::TYPE_UINT, 'required' => true, 'maxLength' => 10 ),				
				'claimant_ip' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 20 ),
				'claimant_proof' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 500 ),
			)
		);
	}

	
	


	protected function _getExistingData($data)
	{
		if (!$thread_id = $this->_getExistingPrimaryKey($data, 'thread_id'))
		{
			return false;
		}

		return array('kmk_jobvacan_listingclaim' => $this->getModelFromCache('KomuKuJVC_Model_ListingClaim')->getListingClaimById($thread_id));
	}

	protected function _getUpdateCondition($tableName)	
	{
		return 'thread_id = ' . $this->_db->quote($this->getExisting('thread_id'));
	}


}