<?php

class KomuKu_MyThreads_ControllerPublic_MyThreads extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{		
		// get nodeId
		$nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		// get userId
		$userId = XenForo_Visitor::getUserId();	
		
		// define viewParams
		$viewParams = array();								

		if ($nodeId)
		{
			// get database
			$db = XenForo_Application::get('db');
			
			// run query
			$threads = $db->fetchAll("
				SELECT kmk_thread.thread_id, 
				kmk_thread.title, username, 
				kmk_thread.post_date, 
				kmk_node.title AS forumTitle
				FROM kmk_thread
				INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
				WHERE kmk_thread.node_id = ?
				AND kmk_thread.user_id = ?
				AND kmk_thread.discussion_state = 'visible'
				ORDER BY kmk_thread.post_date DESC
			", array($nodeId,$userId));
	
			$total = count($threads);			
			
			// get options from Admin CP -> Options -> My Threads -> Limit
			$limit = XenForo_Application::get('options')->myThreadsLimit;				
	
			// prepare viewParams
			$viewParams = array(
				'limit' => $limit,
				'total' => $total,
				'threads' => $threads
			); 
		}
			
		// send to template
		return $this->responseView('KomuKu_MyThreads_ViewPublic_MyThreads', 'KomuKu_mythreads', $viewParams);
	}
}