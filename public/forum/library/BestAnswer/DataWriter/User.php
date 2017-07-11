<?php

class BestAnswer_DataWriter_User extends XFCP_BestAnswer_DataWriter_User
{
	protected function _getFields()
	{
		$response = parent::_getFields();

		$response['kmk_user']['best_answer_count'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 0);

		return $response;
	}
}