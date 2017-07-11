<?php

class KomuKu_MyThreads_ControllerPublic_Forum extends XFCP_KomuKu_MyThreads_ControllerPublic_Forum
{
	public function actionForum()
	{		
		// get parent	
		$parent = parent::actionForum();
				
		// get nodeId
		//TRY to do something.
		$nodeId = $this->_input->filterSingle('node_id',array(XenForo_Input::UINT, 'array' =>false));
					
		// get options from Admin CP -> Options -> My Threads -> Include Forums
		$includeForumsArray = XenForo_Application::get('options')->myThreadsIncludeForums;
		
		// search array
		$key = array_search($nodeId, $includeForumsArray);					
		
		// if key
		if (is_numeric($key))
		{
			// declare showMyThreadsLink
			$showMyThreadsLink = true;
			
			// declare viewParams
			$viewParams = array(
				'showMyThreadsLink' => $showMyThreadsLink
			);
			
			// add viewParams to parent params
			$parent->params += $viewParams;
		}
			
		// return parent
		return $parent;
	}
}