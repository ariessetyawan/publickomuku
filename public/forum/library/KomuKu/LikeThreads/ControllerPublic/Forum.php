<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerPublic_Forum extends XFCP_KomuKu_LikeThreads_ControllerPublic_Forum
{
    public function actionIndex()
    {
        $parent = parent::actionIndex();
		
		//Return parent if this is a non View response 
		if (!$parent instanceof XenForo_ControllerResponse_View)
		{
			return $parent;
		}
		
		/** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
		//Get the visitor var
		$visitor = XenForo_Visitor::getInstance();
		
		//Can like threads
        $canviewlikes = $visitor->hasPermission('forum', 'canViewLikes');
		
		//Show most liked threads at the forum index
		if(XenForo_Application::get('options')->showlikedthreads > 0)
		{
		   //The number of threads to show
		   $maxResults = XenForo_Application::get('options')->showlikedthreads;
		   
		   //Get the most liked threads
		   $likedThreads = $model->getLikedThreads($maxResults);
		   
	       //Register the variable for use in our template  
		   $parent->params['canviewlikes'] = $canviewlikes;
		   $parent->params['likedThreads'] = $likedThreads;
           
		}
	
		return $parent;
		
	}
	
	//Sort threads based on likes
	protected function _getDefaultThreadSort(array $forum)
    {
        if ($forum['default_sort_order'] == 'last_post_date' AND XenForo_Application::get('options')->default_likes_thread_sorting) 
		{
            $forum['default_sort_order'] = 'like_count';
        }
		
        return parent::_getDefaultThreadSort($forum);
    }

}