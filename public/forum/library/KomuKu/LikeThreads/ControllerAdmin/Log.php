<?php

//######################## Dislike Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerAdmin_Log extends XFCP_KomuKu_LikeThreads_ControllerAdmin_Log
{
    //Get all thread likes and log them in the thread like log tool
	public function actionLikesLogViewer()
	{
		$logModel = $this->getModelFromCache('XenForo_Model_Log');
		
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = 25;
		
		//Search for like(s) given by user(s)
		$username = $this->_input->filterSingle('username', XenForo_Input::STRING);
			
		if ($username)
		{
			$user = $this->getModelFromCache('XenForo_Model_User')->getUserByName($username);
			
			$viewParams = array(
				'entries' => $logModel->getLikesLogsByUserId($user['user_id']),
				'noCount' => true
			);
			
			return $this->responseView('KomuKu_LikeThreads_ViewAdmin_Log', 'th_like_threads_log', $viewParams);
		}
		
		$viewParams = array(
			'entries' => $logModel->getLikesLog(array(
				'page' => $page,
				'perPage' => $perPage
			)),
						
			'page' => $page,
			'perPage' => $perPage,
			'total' => $logModel->countLikesLog()
		);
		
		return $this->responseView('KomuKu_LikeThreads_ViewAdmin_Log', 'th_like_threads_log', $viewParams);
	}
	
	//Recount all thread likes
	public function actionLikesRecount()
	{
		$logModel = $this->getModelFromCache('XenForo_Model_Log');
		
		if ($this->isConfirmedPost()) //Recount all thread likes
		{
			$logModel->recountLikes();
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('logs/likes-log-viewer')
			);
		}
		
		else 
		{
			return $this->responseView('KomuKu_LikeThreads_ViewAdmin_Recount', 'th_recount_rebuild_like_log', array());
		}
	}
	
	//Delete thread like
	public function actionLikeDelete()
	{
		$id = $this->_input->filterSingle('id', XenForo_Input::UINT);
		
		$logModel = $this->getModelFromCache('XenForo_Model_Log');
		
		if ($this->isConfirmedPost()) //delete thread like
		{
			$logModel->deleteLikeEntry($id);
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('logs/likes-log-viewer')
			);			
		}
		else
		{
			$viewParams = array(
				'entry' => $logModel->getLikeLogById($id)
			);
			
			return $this->responseView('KomuKu_LikeThreads_ViewAdmin_Delete', 'th_like_log_delete', $viewParams);
		}
	}
	
	//Clear all thread likes
	public function actionLikesClear()
	{
		$logModel = $this->getModelFromCache('XenForo_Model_Log');
		
		if ($this->isConfirmedPost()) //clear all thread likes
		{
			$logModel->clearAllLikes();
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('logs/likes-log-viewer')
			);			
		}
		else
		{
			$viewParams = array();
			
			return $this->responseView('KomuKu_LikeThreads_ViewAdmin_Clear', 'th_like_log_clear', $viewParams);
		}
	}	
}