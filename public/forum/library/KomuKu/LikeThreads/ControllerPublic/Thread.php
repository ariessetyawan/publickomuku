<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerPublic_Thread extends XFCP_KomuKu_LikeThreads_ControllerPublic_Thread
{
    public function actionIndex() 
	{
		$parent = parent::actionIndex();
		
		if ($parent instanceof XenForo_ControllerResponse_View) 
		{
		   //Define thread variables
		   $ftpHelper = $this->getHelper('ForumThreadPost');
		   $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		   list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		   
		   /** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		   $model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		   
		   //View thread likes permisisons
		   $viewthreadlikes = $model->canViewThreadLikes($thread);
		   
		   //Delete thread likes permisisons
		   $deletethreadlikes = $model->canDeleteThreadLikes($thread);
		   
		   //Register the variables for use in our template
		   $parent->params['viewthreadlikes'] = $viewthreadlikes;
		   $parent->params['deletethreadlikes'] = $deletethreadlikes;
		   
		   //Show the number of likes a thread has received
		   $model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		   
		   //Register the variable for use in our template
		   $parent->params['likes'] = $model->countLikesForThreadId($parent->params['thread']['thread_id']);
		   
	       //Exclude thread liking from certain forum(s)
		   $options = XenForo_Application::get('options');
		   $exclude = $options->exluded_liked_threads_fids;
		
		   $visitor = XenForo_Visitor::getInstance();
		
		   $nodeId = $parent->params['thread']['node_id'];
           
	       if(isset($nodeId) AND !in_array($nodeId, $exclude))
		   {   
		       //Can like threads
               $canviewlikes = $visitor->hasPermission('forum', 'canViewLikes');
			   
	           //Register the variable for use in our template
	           $parent->params['canviewlikes'] = $canviewlikes;
		 
		   }	   
		}
		
		return $parent;
	}
	
	//Like this thread form
	public function actionLikeThread()
	{
	    /** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
		//Get the $visitor var
		$visitor = XenForo_Visitor::getInstance();
		
		//Get the $options var
		$options = XenForo_Application::getOptions();
		
		//Filter the columns
		$input = $this->_input->filter(array(
		    'like_id' => XenForo_Input::UINT,
			'thread_id' => XenForo_Input::UINT,
			'message' => XenForo_Input::STRING,
		));
		
		//Define thread variables
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($input['thread_id']);
		
		//Can like threads
        if (!$model->canLikeThreads($thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
		
		//Exclude thread likes from certain forum(s). 
		$exclude = $options->exluded_liked_threads_fids;

		if (in_array($forum['node_id'], $exclude))
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_page_not_found'), 404));
		}
		
		//Get likes for this user at this thread
		$like = $model->getLikesByUserForThreadId(XenForo_Visitor::getUserId(), $thread['thread_id']);
		
		//You aready liked this thread
		if (!empty($like)) 
		{
			return $this->responseError(new XenForo_Phrase('th_you_already_liked_this_thread'));
		}
		
		//Set some criteria for threads to exclude from likes. Staff is excluded.
		$startdatelimit = $options->like_time_limit;
					
		if ($startdatelimit > 0)
		{
			$time = time() - ($startdatelimit * 86400);
				
			if ($thread['post_date'] <= $time AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff'])
			{	
			  return $this->responseNoPermission();		
			}
		}
		
		//Last reply date criteria. Staff is excluded.
		$replydatelimit = $options->like_reply_time_limit;
					
		if ($replydatelimit > 0)
		{
			$replytime = time() - ($replydatelimit * 86400);
				
			if ($thread['last_post_date'] <= $replytime AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff'])
			{	
			  return $this->responseNoPermission();		
			}
		}
		
		//Replies criteria. Staff is excluded.
		if($options->min_replies_likes != 0 AND $options->min_replies_likes >= $thread['reply_count'] AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff'])
		{
		  return $this->responseNoPermission();
        }
		
		//Views criteria. Staff is excluded.
		if($options->min_views_likes != 0 AND $options->min_views_likes >= $thread['view_count'] AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff'])
		{
		  return $this->responseNoPermission();
        }
		
		//Prevent abuse of liking threads by setting up a daily limit
		if (!$model->dailyLikeLimit($thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
		
		//Log thread likes
		if ($this->_request->isPost())
		{
		    //Captcha for guests
		    if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
			{
				return $this->responseCaptchaFailed();
			}
			
			
			//Guest is liking this thread. Show some restrictions.
			if (empty($visitor['user_id']))
			{
				$visitor['username'] = $this->_input->filterSingle('username', XenForo_Input::STRING);
				$visitor['email'] = $this->_input->filterSingle('email', XenForo_Input::STRING);
				
				//Make sure guests enter valid email address.
				if (!Zend_Validate::is($visitor['email'], 'EmailAddress') )
				{
					return $this->responseError ( new XenForo_Phrase ( 'please_enter_valid_email' ) );
				}
				
				//Don't allow banned emails to be used
				if (XenForo_Helper_Email::isEmailBanned($visitor['email']))
				{
					return $this->responseError(new XenForo_Phrase('email_address_you_entered_has_been_banned_by_administrator'));
				}
				
				//Get user 's registered emails and disallow them from being used by guests. 
				$user = $this->getModelFromCache('XenForo_Model_User')->getUserByEmail($visitor['email']);
				
				if(!empty($user))
				{
					return $this->responseError(new XenForo_Phrase('th_email_address_you_entered_is_registered'));
				}
			}
			
		    //Require like comments. Staff is excluded.
		    if ($options->like_thread_comment_required AND strlen($input['message']) == 0 AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff'])
			{
				return $this->responseError(new XenForo_Phrase('th_must_give_like_comment'));
			}
			
			$this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads')->insertLikes($input);
			
			//Send alert to thread starters	when their threads have been liked	
		    if ($thread['user_id'] != $visitor['user_id'])
            {	
			   //User x y liked your thread
			   $threadstarters = array($thread['username']);
			   $model->sendThreadLikeAlert('like_thread_starters', $thread['thread_id'], $threadstarters, $visitor);
			}
			
		}
		else
		{
			//Register the variable for use in our template
			$viewParams = array(
				'thread' => $thread,
				'like' => $like,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
				'captcha' => XenForo_Captcha_Abstract::createDefault()
			);

			return $this->responseView('KomuKu_LikeThreads_ViewPublic_LikeThread', 'th_like_this_thread', $viewParams);
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $thread));
	}
	
	//Show thread likes form
	public function actionViewLikes()
	{
	    //Define thread variables
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		/** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
		//View thread likes permisisons   
		if (!$model->canViewThreadLikes($thread))
		{
			return $this->responseNoPermission();
		}  
		
		//Pagination
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = 20;
		
		//Fetch options
		$fetchOptions = array(
			'join' => KomuKu_LikeThreads_Model_LikeThreads::FETCH_USER,
			'page' => $page,
            'perPage' => $perPage
		);
		
		//Conditions
		$conditions = array(
			'thread_id' => $threadId
		);
		
		//Count all likes for this thread
		$count = $model->countLikes($conditions);
		
		//Get all likes for this thread
		$likes = $model->getLikesByThread($conditions, $fetchOptions);

		//Register the variables for use in our template
		$viewParams = array(
			'thread' => $thread,
			'likes' => $likes,
			'viewthreadlikes' => $model->canViewThreadLikes($thread),
			'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			'count' => $count,
            'perPage' => $perPage,
			'page' => $page
		);

		return $this->responseView('KomuKu_LikeThreads_ViewPublic_ViewLikes', 'th_view_thread_likes', $viewParams);
	}
	
	
	//Prune likes a thread has received
	public function actionPruneLikes()
	{
		//Define thread variables
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		/** @var $model KomuKu_LikeThreads_Model_LikeThreads */
			$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
		
		//Delete thread likes permisisons   
		if (!$model->canDeleteThreadLikes($thread))
		{
			return $this->responseNoPermission();
		}

		if ($this->isConfirmedPost())
		{
		    //Delete all likes for this thread
			$model->deleteLikes($thread['thread_id']);
		}
        else
		{	
            //Register the variables for use in our template		
			$viewParams = array(
				'thread' => $thread,
				'forum' => $forum,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('KomuKu_LikeThreads_ViewPublic_PruneLikes', 'th_delete_thread_likes', $viewParams);
		}

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $thread));		
	}
}