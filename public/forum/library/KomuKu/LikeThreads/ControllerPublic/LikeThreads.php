<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerPublic_LikeThreads extends XenForo_ControllerPublic_Abstract
{
    //Display most liked threads
	public function actionIndex()
	{
		/** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
	    //Get the $visitor var.
		$visitor = XenForo_Visitor::getInstance();
		
		//Get the $options var
		$options = XenForo_Application::getOptions();
		
		//Is enabled?
		if(empty($options->most_liked_threads_archive))
		{
		   return $this->responseNoPermission();	
		}
       
		//Can like threads archive 
		if(!$model->canViewMostLikedPage())
		{ 
		   return $this->responseNoPermission();	
        }
		
		//Show the online users sidebar.
		$sessionModel = $this->getModelFromCache('XenForo_Model_Session');

		$onlineUsers = $sessionModel->getSessionActivityQuickList(
			$visitor->toArray(),
			array('cutOff' => array('>', $sessionModel->getOnlineStatusTimeout())),
			($visitor['user_id'] ? $visitor->toArray() : null)
		);
		
		//Show the board stats.
		$boardTotals = $this->getModelFromCache('XenForo_Model_DataRegistry')->get('boardTotals');
		
        if (!$boardTotals)
		{
            $boardTotals = $this->getModelFromCache('XenForo_Model_Counters')->rebuildBoardTotalsCounter();
		}
		
		//Set up the pagination.
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = 25;
			
		//Set the vars to use the in the templates.
		$viewParams = array(
		    'likedThreads' => $model->getLikedThreadsArchieve(array(
			'page' => $page,
			'perPage' => $perPage
		)),
			'page' => $page,
			'perPage' => $perPage,
			'total' => $model->countLikes(),
			'onlineUsers' => $onlineUsers,
			'boardTotals' => $boardTotals
		);
		
        return $this->responseView('', 'th_liked_threads_page', $viewParams);
    }
	
	//Show users viewing the best threads page in the online list
	public static function getSessionActivityDetailsForList(array $activities)
	{
		return new XenForo_Phrase('viewing_affiliates');
	}
    
}
?>