<?php

class KomuKu_SimpleForms_Helper_Criteria
{
	public static function UserMatchesCriteria($rule, array $data, array $user, &$returnValue) 
	{ 
		switch ($rule) 
		{ 
			case 'kmkform__responded': 
			{
				$responseModel = XenForo_Model::create('KomuKu_SimpleForms_Model_Response');
				$numResponses = $responseModel->getNumFormResponsesByUserId($user['user_id']);
				
				foreach ($data['form_ids'] as $formId)
				{
					if (array_key_exists($formId, $numResponses))
					{
						$returnValue = true;
					}
				}
				
				break;
			}
			case 'kmkform__responded_any':
			{
				$responseModel = XenForo_Model::create('KomuKu_SimpleForms_Model_Response');
				$numResponses = $responseModel->getNumFormResponsesByUserId($user['user_id']);

				if (count($numResponses) > 0)
					$returnValue = true;
				
				break;
			}
		}
	}
}