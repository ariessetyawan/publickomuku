<?php

class BestAnswer_ControllerPublic_Forum extends XFCP_BestAnswer_ControllerPublic_Forum
{
	protected function _getDisplayConditions(array $forum)
	{
		$response = parent::_getDisplayConditions($forum);
		
		if ($this->_input->inRequest('answered'))
		{
			$answered = $this->_input->filterSingle('answered', XenForo_Input::INT);
			
			if ($answered === 1)
			{
				$response['answered'] = 1;
			}
			else if ($answered === 0)
			{
				$response['answered'] = 0;
			}
			else if ($answered === -1)
			{
				$response['answered'] = -1;
			}
		}
		
		return $response;
	}
}