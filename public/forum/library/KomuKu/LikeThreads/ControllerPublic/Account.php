<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerPublic_Account extends XFCP_KomuKu_LikeThreads_ControllerPublic_Account
{
    //Display threads that this user has liked
    public function actionGivenLikes() 
	{
		$userId = XenForo_Visitor::getUserId();
		
		$fetchOptions = array(
		    'join' => KomuKu_LikeThreads_Model_LikeThreads::FETCH_USER |  KomuKu_LikeThreads_Model_LikeThreads::FETCH_THREAD,
			'order' => 'like_date',
			'direction' => 'desc',
		);
		
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$perPage = 20;
		$fetchOptions['page'] = $page;
		$fetchOptions['perPage'] = $perPage;
		
		$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
		$likes = $model->getAllForGiverUser($userId, $fetchOptions);
		
		$count = $model->countAllForGiverUser($userId , $fetchOptions);
		
		$viewParams = array(
			'user' => $userId,
		    'likes' => $likes,
			'page' => $page,
			'perPage' => $perPage,
			'count' => $count
		);
		
		return $this->_getWrapper(
			'account', 'givenlikes',
			$this->responseView('XenForo_ViewPublic_Base', 'th_given_thread_likes', $viewParams)
		);
	}
	
}