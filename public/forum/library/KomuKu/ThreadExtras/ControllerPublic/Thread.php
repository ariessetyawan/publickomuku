<?php

//######################## Extra Thread View Settings By KomuKu ###########################
class KomuKu_ThreadExtras_ControllerPublic_Thread extends XFCP_KomuKu_ThreadExtras_ControllerPublic_Thread
{
    public function actionIndex() 
	{
		$parent = parent::actionIndex();
		
		$userModel = $this->getModelFromCache('KomuKu_ThreadExtras_Model_User');
		
		if ($parent instanceof XenForo_ControllerResponse_View) 
		{
			$ftpHelper = $this->getHelper('ForumThreadPost');
			$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
			list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
			
			$visitor = XenForo_Visitor::getInstance();
			
			//Get the model
			$model = $this->getModelFromCache('KomuKu_ThreadExtras_Model_Model');
			   
			//Exlude group/s from the extra thread view settings restrictions
			if($visitor->hasPermission('forum', 'excludeThreads'))
			{
			   return $parent;
			}
			
			//Exlude thread starters from the extra thread view settings restrictions
			if($visitor['user_id'] == $thread['user_id'])
			{
			   return $parent;
			}
			
				//Deny access to this thread if user does not have enough posts
				if($thread['posts'] != 0)
				{
				   if (!$visitor['user_id'])
				   {
					  return $this->responseError(new XenForo_Phrase('must_be_registered'));
				   }
				   else if($visitor['message_count'] <= $thread['posts'])
				   {
					   return $this->responseError(new XenForo_Phrase('num_posts_threads', 
							array('username' => $visitor['username'],
								 'postthreads' => $thread['posts'])
					   ));
				   }
			  
				}
				
				//Set up the daily post requirement to enter this thread
				if (!$model->dailyPosts($thread, $errorPhraseKey))
				{
				   throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
				}
			 
				//Set up the thread count requirement to enter this thread
				if (!$model->threadCount($thread, $errorPhraseKey))
				{
				   throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
				}
		  
				//Deny access to this thread if user does not have enough likes
				if($thread['user_likes'] != 0)
				{
					if (!$visitor['user_id'])
					{
						return $this->responseError(new XenForo_Phrase('must_be_registered'));
					}
					else if($visitor['like_count'] <= $thread['user_likes'])
					{
						return $this->responseError(new XenForo_Phrase('num_likes_threads', 
							array('username' => $visitor['username'],
								'likethreads' => $thread['user_likes'])
						));
					}
				}
				
				//Deny access to this thread if user does not have enough trophy points
				if($thread['user_trophy'] != 0)
				{
					if (!$visitor['user_id'])
					{
						return $this->responseError(new XenForo_Phrase('must_be_registered'));
					}
					else if($visitor['trophy_points'] <= $thread['user_trophy'])
					{
						return $this->responseError(new XenForo_Phrase('num_trophies_threads', 
							array('username' => $visitor['username'],
								'trophiesthreads' => $thread['user_trophy'])
						));
					}
			  
				}
		  
				//Deny access to this thread if user is a registered member for less than certain days
				if($thread['reg_days'] != 0)
				{
					if (!$visitor['user_id'])
					{
						return $this->responseError(new XenForo_Phrase('must_be_registered'));
					}
			  
					$regday = time() - $thread['reg_days'] * 86400;
			  
					if($visitor['register_date'] > $regday)
					{
					   return $this->responseError(new XenForo_Phrase('regday_thread', 
							array('username' => $visitor['username'],
								'regdaysrequired' => $thread['reg_days'])
					   ));
					}
				}
		  
				//Set up the age requirement to view this thread
				$userId = $visitor['user_id'];

				$userProfileModel = $this->getModelFromCache('XenForo_Model_UserProfile');
			
				//Get the registered users age
				if($userId > 0)
				{
				   $userFullId = $userModel->getFullUserById($userId);
				   $userAge = $userProfileModel->getUserAge($userFullId,true);
				}
			
				if($thread['age'] != 0)
				{
				   if (!$visitor['user_id'])
				   {
					  return $this->responseError(new XenForo_Phrase('must_be_registered'));
				   }
			  
				//If users have not specified their age, deny access to the age threads
				if(empty($userAge))
				{
				   return $this->responseError(new XenForo_Phrase('no_age_entered', 
							array('username' => $visitor['username'])
					));
				}	
		  
				//Deny access to the user if he/she is below the age allowed for this thread
				if($userAge <= $thread['age'])
				{
					return $this->responseError(new XenForo_Phrase('wrong_age_thread', 
							array('username' => $visitor['username'],
								'userage' => $thread['age'])
					));
				}   
			}
			
			//Gender threads
			if($thread['user_gender'] != "")
			{
			   if (!$visitor['user_id'])
			   {
					return $this->responseError(new XenForo_Phrase('must_be_registered'));
			   }
			  
			   //If users have not specified their genders, deny access to the gender threads
			   if(empty($visitor['gender']))
			   {
					return $this->responseError(new XenForo_Phrase('no_gender_entered', 
						array('username' => $visitor['username'])
					));
			   }
			 
			   //Make sure that they do not sneek in the wrong thread :D
			   if($visitor['gender'] != $thread['user_gender'])
			   {
					return $this->responseError(new XenForo_Phrase('wrong_gender_threads', 
							array('username' => $visitor['username'],
								 'genderthreads' => $thread['user_gender'])
					));
			   }
			}
			
			//Specific users
			if($thread['specific_users'] != "")
			{
				$sepecificExtra = unserialize($thread['specific_users_extra']);
				
				if (!empty($sepecificExtra['user_ids'])) {
		
					if ($sepecificExtra['specific_type'] == 'viewable' && !in_array($visitor['user_id'], $sepecificExtra['user_ids'])) {
						
						return $this->responseError(new XenForo_Phrase('KomuKu_you_are_not_allowed_to_view_this_thread'));
						
					} elseif ($sepecificExtra['specific_type'] == 'not_viewable') {
						
						if (in_array($visitor['user_id'], $sepecificExtra['user_ids'])){
							if ($sepecificExtra['specific_length'] == 'temporary' && $sepecificExtra['expiry_date'])
							{
								return $this->responseError(new XenForo_Phrase(
										'KomuKu_you_are_not_allowed_to_view_this_thread_until_x',
										array('date' => $sepecificExtra['expiry_date'])
									)
								);
								
							} else {
								
								return $this->responseError(new XenForo_Phrase('KomuKu_you_are_not_allowed_to_view_this_thread'));
								
							}
						}
					}
				}
			}
		  
		}
		
		return $parent;
	}
	
    //Displays the form to add the post count requirement to view thread
	public function actionPostsForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_thread_posts_add', $viewParams);
    }
	
	//Add the post count requirement to view this thread
	public function actionPostsAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'posts' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$posts = intval($input['posts']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('posts', $posts);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the post count requirement to view this thread
	public function actionPostsEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_thread_posts_edit', $viewParams);
    }
	
	//Edit post count requirement to view this thread
	public function actionPostsSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'posts' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$posts = intval($input['posts']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('posts', $posts);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add daily posts requirement to view this thread
	public function actionDailyForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_thread_dailyposts_add', $viewParams);
    }
	
	//Add the daily posts requirement to view this thread
	public function actionDailyAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'daily_posts' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$daily_posts = intval($input['daily_posts']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('daily_posts', $daily_posts);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the daily posts requirement to view this thread
	public function actionDailyEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_thread_dailyposts_edit', $viewParams);
    }
	
	//Edit daily posts requirement to view this thread
	public function actionDailySave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'daily_posts' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$daily_posts = intval($input['daily_posts']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('daily_posts', $daily_posts);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add nr of threads requirement to view this thread
	public function actionThreadsForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_threads_add', $viewParams);
    }
	
	//Add the nr of threads requirement to view this thread
	public function actionThreadsAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'thread_count' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$thread_count = intval($input['thread_count']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('thread_count', $thread_count);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the nr of threads requirement to view this thread
	public function actionThreadsEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_threads_edit', $viewParams);
    }
	
	//Edit nr of threads requirement to view this thread
	public function actionThreadsSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'thread_count' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$thread_count = intval($input['thread_count']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('thread_count', $thread_count);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add the likes count requirement to view this thread
	public function actionLikesForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_likes_add', $viewParams);
    }
	
	//Add the likes count requirement to view this thread
	public function actionLikesAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_likes' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$user_likes = intval($input['user_likes']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_likes', $user_likes);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the like count requirement to view this thread
	public function actionLikesEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_likes_edit', $viewParams);
    }
	
	//Edit likes count requirement requirement to view this thread
	public function actionLikesSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_likes' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$user_likes = intval($input['user_likes']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_likes', $user_likes);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add the trophies count requirement to view this thread
	public function actionTrophiesForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_trophies_add', $viewParams);
    }
	
	//Add the trophies count requirement to view this thread
	public function actionTrophiesAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_trophy' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$user_trophy = intval($input['user_trophy']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_trophy', $user_trophy);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the trophies count requirement to view this thread
	public function actionTrophiesEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_trophies_edit', $viewParams);
    }
	
	//Edit trophies count requirement to view this thread
	public function actionTrophiesSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_trophy' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$user_trophy = intval($input['user_trophy']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_trophy', $user_trophy);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add registered days requirement to view this thread
	public function actionRegisterForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_register_add', $viewParams);
    }
	
	//Add the registered days requirement to view this thread
	public function actionRegisterAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'reg_days' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$reg_days = intval($input['reg_days']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('reg_days', $reg_days);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the registered days requirement to view this thread
	public function actionRegisterEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_register_edit', $viewParams);
    }
	
	//Edit registered days requirement to view this thread
	public function actionRegisterSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'reg_days' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$reg_days = intval($input['reg_days']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('reg_days', $reg_days);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add age requirement to view this thread
	public function actionAgeForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_age_add', $viewParams);
    }
	
	//Add the age requirement for this thread
	public function actionAgeAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'age' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$age = intval($input['age']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('age', $age);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the age requirement to view this thread
	public function actionAgeEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_age_edit', $viewParams);
    }
	
	//Edits the age requirement to view this thread
	public function actionAgeSave()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'age' => XenForo_Input::UINT,
		));
		
		//Prepare to add them to the database with intval for security
		$age = intval($input['age']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('age', $age);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to add gender requirement to view this thread
	public function actionGenderForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_gender_add', $viewParams);
    }
	
	//Add the gender requirement to view this thread
	public function actionGenderAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_gender' => XenForo_Input::STRING,
		));
		
		//Prepare to add them to the database with addslashes for security
		$user_gender = addslashes($input['user_gender']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_gender', $user_gender);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the gender requirement to view this thread
	public function actionGenderEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_gender_edit', $viewParams);
    }
	
	//Edit gender requirement to view this thread
	public function actionGenderSave()
    {
	   //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_gender' => XenForo_Input::STRING,
		));
		
		//Prepare to add them to the database with adslash for security
		$user_gender = addslashes($input['user_gender']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_gender', $user_gender);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		); 
    }
	
	//Displays the form to moderate users for this thread
	public function actionModerateForm()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_moderate_add', $viewParams);
    }
	
	//Add the moderated users for this thread
	public function actionModerateAdd()
    {
	    //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_moderation' => XenForo_Input::STRING,
		));
		
		//Prepare to add them to the database with addslashes for security
		$user_moderation = addslashes($input['user_moderation']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_moderation', $user_moderation);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	//Displays the form to edit the moderated users for this thread
	public function actionModerateEdit()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		//Register variable for use in our template
		$viewParams = array(
			'thread' => $thread
		);
		
        return $this->responseView('', 'KomuKu_moderate_edit', $viewParams);
    }
	
	//Edit moderated users for this thread
	public function actionModerateSave()
    {
	     //Action via $_POST
    	$this->_assertPostOnly();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		//Filter the field through input
		$input = $this->_input->filter(array(
			'user_moderation' => XenForo_Input::STRING,
		));
		
		//Prepare to add them to the database with addslashes for security
		$user_moderation = addslashes($input['user_moderation']);
		
		//Get the thread data writer
		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		
		$threadDw->setExistingData($thread['thread_id']);
		$threadDw->set('user_moderation', $user_moderation);
		$threadDw->save();
		
		//Redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread),
			new XenForo_Phrase('changes_saved')
		);
    }
	
	public function actionSpecificUsers()
    {
	    $ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$visitor = XenForo_Visitor::getInstance();
		
		//Only admins can add restrictions
		if(!$visitor['is_admin'])
		{
		   return $this->responseNoPermission();
		}
		
		if ($this->isConfirmedPost()) {
			
			$input = $this->_input->filter(array(
				'username' => XenForo_Input::STRING,
				'specific_type' => XenForo_Input::STRING,
				
				'specific_length' => XenForo_Input::STRING,
				'specific_length_value' => XenForo_Input::UINT,
				'specific_length_unit' => XenForo_Input::STRING,

				'send_alert' => XenForo_Input::UINT,
				'alert_reason' => XenForo_Input::STRING,
			));
			
			$userModel = $this->getModelFromCache('KomuKu_ThreadExtras_Model_User');
			
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
			
			$threadDw->setExistingData($thread['thread_id']);
			
			if ($input['username'])
			{
				$usersName = explode(',', $input['username']);
				$usersName = array_map('trim', $usersName);
					
				foreach ($usersName AS $key => $userName)
				{
					if ($userName === '')
					{
						unset($usersName[$key]);
					}
				}

				if (count($usersName) >= 1) {	
					$criteria['usernames'] = $usersName;	
				}
				
				$userIds = $userModel->getUserIdsByNames($criteria);
			}
			
			$input['user_ids'] = array();
			
			if (!empty($userIds)) {
				
				$input['user_ids'] = $userIds;
				
				$users = $userModel->getUsersByIds($userIds);
				
				if ($input['specific_length'] == 'permanent')
				{
					$expiryDate = null;
				}
				else
				{
					$expiryDate = min(
						pow(2,32) - 1,
						strtotime("+$input[specific_length_value] $input[specific_length_unit]")
					);
				}
				
				$input['expiry_date'] = $expiryDate;
				
				if (!empty($users) && $input['send_alert']) {
					foreach ($users AS $userId => $user) {
						$this->_KomuKuSendAlert($visitor, $user, $input, $threadId);
					}
				}
			}
			
			$threadDw->set('specific_users', $input['username']);
			$threadDw->set('specific_users_extra', serialize($input));
			
			$threadDw->save();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('threads', $thread),
				new XenForo_Phrase('changes_saved')
			);
		}else{
			
			$thread['specific_users_extra'] = unserialize($thread['specific_users_extra']);

			$viewParams = array(
				'thread' => $thread
			);
			
			return $this->responseView('', 'KomuKu_thread_specific_users', $viewParams);
			
		}
    }
	
	//Prevent from viewing thread previews as well
	public function actionPreview()
	{
	    $parent = parent::actionPreview();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
	    $visitor = XenForo_Visitor::getInstance();
		
		//Get the model
		$model = $this->getModelFromCache('KomuKu_ThreadExtras_Model_Model');
		   
		//Exlude group/s from the extra thread view settings restrictions
		if($visitor->hasPermission('forum', 'excludeThreads'))
		{
		   return $parent;
		}
		
		//Exlude thread starters from the extra thread view settings restrictions
		if($visitor['user_id'] == $thread['user_id'])
		{
		   return $parent;
		}
		
		    //Deny access to this thread if user does not have enough posts
	        if($thread['posts'] != 0)
	        {
	   	       if (!$visitor['user_id'])
	   	       {
	   	  	      return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	       }
		       else if($visitor['message_count'] <= $thread['posts'])
	           {
				   return $this->responseError(new XenForo_Phrase('num_posts_threads', 
				        array('username' => $visitor['username'],
			                 'postthreads' => $thread['posts'])
				   ));
		       }
		  
	        }
			
			//Set up the daily post requirement to enter this thread
	        if (!$model->dailyPosts($thread, $errorPhraseKey))
		    {
			   throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		    }
	     
		    //Set up the thread count requirement to enter this thread
	        if (!$model->threadCount($thread, $errorPhraseKey))
		    {
			   throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		    }
	  
	        //Deny access to this thread if user does not have enough likes
	        if($thread['user_likes'] != 0)
	        {
	   	        if (!$visitor['user_id'])
	   	        {
	   	  	        return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	        }
		        else if($visitor['like_count'] <= $thread['user_likes'])
	            {
				    return $this->responseError(new XenForo_Phrase('num_likes_threads', 
				        array('username' => $visitor['username'],
			                'likethreads' => $thread['user_likes'])
				    ));
		        }
		    }
			
			//Deny access to this thread if user does not have enough trophy points
	        if($thread['user_trophy'] != 0)
	        {
	   	        if (!$visitor['user_id'])
	   	        {
	   	  	        return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	        }
		        else if($visitor['trophy_points'] <= $thread['user_trophy'])
	            {
				    return $this->responseError(new XenForo_Phrase('num_trophies_threads', 
				        array('username' => $visitor['username'],
			                'trophiesthreads' => $thread['user_trophy'])
				    ));
			    }
		  
	        }
	  
	        //Deny access to this thread if user is a registered member for less than certain days
	        if($thread['reg_days'] != 0)
	        {
	   	        if (!$visitor['user_id'])
	   	        {
	   	  	        return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	        }
	   	  
		        $regday = time() - $thread['reg_days'] * 86400;
		  
		        if($visitor['register_date'] > $regday)
		        {
				   return $this->responseError(new XenForo_Phrase('regday_thread', 
				        array('username' => $visitor['username'],
			                'regdaysrequired' => $thread['reg_days'])
				   ));
			    }
	        }
	  
	        //Set up the age requirement to view this thread
	        $userId = $visitor['user_id'];
	        
	        $userProfileModel = $this->getModelFromCache('XenForo_Model_UserProfile');
		
	        //Get the registered users age
	        if($userId > 0)
            {
               $userFullId = $userModel->getFullUserById($userId);
               $userAge = $userProfileModel->getUserAge($userFullId,true);
            }
		
	        if($thread['age'] != 0)
	        {
	   	       if (!$visitor['user_id'])
	   	       {
	   	  	      return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	       }
	   	  
		    //If users have not specified their age, deny access to the age threads
		    if(empty($userAge))
		    {
			   return $this->responseError(new XenForo_Phrase('no_age_entered', 
				        array('username' => $visitor['username'])
				));
			}	
	  
		    //Deny access to the user if he/she is below the age allowed for this thread
		    if($userAge <= $thread['age'])
		    {
			    return $this->responseError(new XenForo_Phrase('wrong_age_thread', 
				        array('username' => $visitor['username'],
			                'userage' => $thread['age'])
			    ));
			}   
	    }
	    
		//Gender threads
	    if($thread['user_gender'] != "")
	    {
	   	   if (!$visitor['user_id'])
	   	   {
	   	  	    return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	   }
	   	  
		   //If users have not specified their genders, deny access to the gender threads
		   if(empty($visitor['gender']))
		   {
		        return $this->responseError(new XenForo_Phrase('no_gender_entered', 
				    array('username' => $visitor['username'])
			    ));
	       }
		 
		   //Make sure that they do not sneek in the wrong thread :D
		   if($visitor['gender'] != $thread['user_gender'])
		   {
		        return $this->responseError(new XenForo_Phrase('wrong_gender_threads', 
				        array('username' => $visitor['username'],
			                 'genderthreads' => $thread['user_gender'])
			    ));
	       }
	    }
		
		//Specific users
		if($thread['specific_users'] != "")
		{
			$sepecificExtra = unserialize($thread['specific_users_extra']);
			
			if (!empty($sepecificExtra['user_ids'])) {
	
				if ($sepecificExtra['specific_type'] == 'viewable' && !in_array($visitor['user_id'], $sepecificExtra['user_ids'])) {
					
					return $this->responseError(new XenForo_Phrase('KomuKu_you_are_not_allowed_to_view_this_thread'));
					
				} elseif ($sepecificExtra['specific_type'] == 'not_viewable') {
					
					if (in_array($visitor['user_id'], $sepecificExtra['user_ids'])){
						if ($sepecificExtra['specific_length'] == 'temporary' && $sepecificExtra['expiry_date'])
						{
							return $this->responseError(new XenForo_Phrase(
									'KomuKu_you_are_not_allowed_to_view_this_thread_until_x',
									array('date' => $sepecificExtra['expiry_date'])
								)
							);
							
						} else {
							
							return $this->responseError(new XenForo_Phrase('KomuKu_you_are_not_allowed_to_view_this_thread'));
							
						}
					}
				}
			}
		}
		
		return $parent;	
	}
	
	protected function _KomuKuSendAlert($user, $alertUserId, $extraData, $threadId) 
	{
		
		if (!$alertUserId) {
			return false;
		}
		
		XenForo_Model_Alert::alert(
			$alertUserId,
			$user['user_id'], $user['username'],
			'thread', $alertUserId,
			'KomuKu_ban_user',
			$extraData
		);
	}
	
}