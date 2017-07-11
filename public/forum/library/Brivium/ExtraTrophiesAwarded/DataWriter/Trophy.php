<?php

class Brivium_ExtraTrophiesAwarded_DataWriter_Trophy extends XFCP_Brivium_ExtraTrophiesAwarded_DataWriter_Trophy
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields ['kmk_trophy']['breta_select'] = array('type' => self::TYPE_STRING);
		$fields ['kmk_trophy']['breta_url'] = array('type' => self::TYPE_STRING);
		$fields ['kmk_trophy']['breta_fa'] = array('type' => self::TYPE_STRING);
		$fields ['kmk_trophy']['breta_icon_date'] = array('type' => self::TYPE_UINT);
		
		return $fields;
	}
	
	protected function _preSave()
	{
		switch ($GLOBALS['breta_upload']['choose_upload']) {
		
			case 'default_url':
				$this->set('breta_select', $GLOBALS['breta_upload']['choose_upload']);
				break;
				
			case 'upload_file':
				$icon = reset($GLOBALS['breta_upload']['upload_file']);
				if(!$icon && !empty($GLOBALS['hasTrophyIcon']) && empty($GLOBALS['hasUploadFile'])){
					throw new XenForo_Exception(new XenForo_Phrase('BRETA_please_choose_file'), true);
				}
				$this->set('breta_select', $GLOBALS['breta_upload']['choose_upload']);
				$this->set('breta_icon_date', XenForo_Application::$time);
				break;
				
			case 'upload_url':
				if($GLOBALS['breta_upload']['upload_url'] == ''){
					throw new XenForo_Exception(new XenForo_Phrase('BRETA_please_enter_url'), true);
				}
				$this->set('breta_select', $GLOBALS['breta_upload']['choose_upload']);
				$this->set('breta_url', $GLOBALS['breta_upload']['upload_url']);
				break;
				
			case 'font_awesome':
				if($GLOBALS['breta_upload']['font_awesome'] == ''){
					throw new XenForo_Exception(new XenForo_Phrase('BRETA_please_enter_font_awesome'), true);
				}
				$this->set('breta_select', $GLOBALS['breta_upload']['choose_upload']);
				$this->set('breta_fa', $GLOBALS['breta_upload']['font_awesome']);
				break;
				
			case 'hide_icon':
				$this->set('breta_select', $GLOBALS['breta_upload']['choose_upload']);
			break;
		}
		
		return parent::_preSave();
	}
	
	protected function _postSave()
	{
		$trophyId = $this->get('trophy_id');
		
		if($GLOBALS['breta_upload']['choose_upload'] == 'upload_file'){
			$icon = reset($GLOBALS['breta_upload']['upload_file']);
			$iconModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_Icon');
			if ($icon)
			{	
				$iconModel->uploadIcon($icon, $trophyId);
			}
		}
		return parent::_postSave();
	}
}