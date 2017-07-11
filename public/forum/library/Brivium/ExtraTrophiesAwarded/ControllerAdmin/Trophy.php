<?php

class Brivium_ExtraTrophiesAwarded_ControllerAdmin_Trophy extends XFCP_Brivium_ExtraTrophiesAwarded_ControllerAdmin_Trophy
{
	public function actionSave()
	{
		$trophyId = $this->_input->filterSingle('trophy_id', XenForo_Input::UINT);
		if ($trophyId)
		{
			$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
			$trophy = $trophyModel->getTrophyById($trophyId);
			
			$GLOBALS['hasTrophyIcon'] = $trophy['breta_icon_date'];
			
			if ($trophy['breta_select'] == 'upload_file') {
				$GLOBALS['hasUploadFile'] = true;
			}
		}
 		$GLOBALS['breta_upload'] = $this->_input->filter(array(
			'choose_upload' => XenForo_Input::STRING,
			'upload_url' => XenForo_Input::STRING,
			'font_awesome' => XenForo_Input::STRING
			));
		$GLOBALS['breta_upload']['upload_file'] = XenForo_Upload::getUploadedFiles('ImageUpload');
		
		return parent::actionSave();
	}
	
	public function actionEdit()
	{
		$response = parent::actionEdit();
		
		if($response->params['trophy']['trophy_id'] > 9){
			$response->params['noDefault'] = true;
		}
		return $response;
	}
	
	public function actionAdd()
	{
		$response = parent::actionAdd();
		
		$response->params['noDefault'] = true;
		$response->params['newTrophy'] = true;

		return $response;
	}
}