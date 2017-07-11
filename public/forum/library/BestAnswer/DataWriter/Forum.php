<?php

class BestAnswer_DataWriter_Forum extends XFCP_BestAnswer_DataWriter_Forum
{
	protected function _getFields()
	{
		$response = parent::_getFields();
		
		$response['kmk_forum']['allow_best_answer'] = array('type' => self::TYPE_BOOLEAN, 'default' => 1);
		
		return $response;
	}
	
	protected function _preSave()
	{
		parent::_preSave();
		
		if (!empty($_POST['forum_edit_form']))
		{
			if (!isset($_POST['allow_best_answer']))
			{
				$_POST['allow_best_answer'] = false;
			}
			
			$this->set('allow_best_answer', ((bool) $_POST['allow_best_answer']));
		}
	}
}