<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_ControllerPublic_Forum extends XFCP_KomuKu_ForumExtras_ControllerPublic_Forum
{
    public function actionIndex()
	{	
        $parent = parent::actionIndex();
		
		$visitor = XenForo_Visitor::getInstance();
		//Exlude group/s from the extra forum view settings restrictions
		if($visitor->hasPermission('forum', 'exlude_extra_forums'))
		{
		   return $parent;
		}
		
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
	    $forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($forumId);
		
		$userId = $visitor['user_id'];
		$userModel = $this->getModelFromCache('XenForo_Model_User');
		$userProfileModel = $this->getModelFromCache('XenForo_Model_UserProfile');
		
		//Get the registered users age
		if($userId > 0)
        {
         $userFullId = $userModel->getFullUserById($userId);
         $userAge = $userProfileModel->getUserAge($userFullId,true);
        }
		
	    $db = XenForo_Application::get('db');
		
		$extra = $db->fetchRow("SELECT * FROM `kmk_forum_extra_view_settings` WHERE node_id = ?", $forumId);
		
	   //Deny access to this forum if user does not have enough posts
	   if($extra['message_count'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	  }
	   	  else if($visitor['message_count'] <= $extra['message_count'])
		  {
			 return $this->responseError(new XenForo_Phrase('num_posts_forums', array('username' => $visitor['username'],
			 'postforums' => $extra['message_count'])));
		  }
	   }
	   
	   //Set up the daily post requirement to enter this forum
	   if($extra['daily_posts'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	  }
	   	  
		   $daycut = time() - 86400;
		   
		   $query = $db->fetchRow("SELECT COUNT(*) AS total_posts FROM  kmk_post WHERE post_date > $daycut AND user_id=" . $visitor['user_id'] . " ");
		   
		   if($query['total_posts'] <= $extra['daily_posts'])
		   {
			   return $this->responseError(new XenForo_Phrase('daily_posts_enter_forum', array('username' => $visitor['username'],
			   'dailyposts' => $extra['daily_posts'])));
		   }
	   }
	   
	   //Deny access to this forum if user is a registered member for less than certain days
	   if($extra['register_date'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	  }
	   	  
		  $regday = time() - $extra['register_date'] * 86400;
		  
		  if($visitor['register_date'] > $regday)
		  {
			 return $this->responseError(new XenForo_Phrase('regday_forum', array('username' => $visitor['username'],
			 'regdaysrequired' => $extra['register_date'])));
		  }
	   }
	   
	   //Set up the age requirement to enter forums
	   if($extra['user_age'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	  }
	   	  
		  //If users have not specified their age, deny access to the age forums
		  if(empty($userAge))
		  {
			return $this->responseError(new XenForo_Phrase('no_age_entered', array('username' => $visitor['username'])));
		  }	
			
		 //Deny access to the user if he/she is below the age allowed for this forum
		 if($userAge < $extra['user_age'])
		 {
			return $this->responseError(new XenForo_Phrase('wrong_age_forum', array('username' => $visitor['username'],
		   'userage' => $extra['user_age'])));
		 }   
	  }
	   
	   //Ban users from forums
	   if($extra['ban'] != "")
	   {
		  if($visitor['user_id'] && in_array($visitor['user_id'], explode( ",", $extra['ban'])))
		  {
			 return $this->responseError(new XenForo_Phrase('ban_from_forum', array('username' => $visitor['username'])));
		  }
	   }
	   
	   //Gender forums
	  if($extra['user_gender'] != "")
	  {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	return $this->responseError(new XenForo_Phrase('must_be_registered'));
	   	  }
	   	  
		 //If users have not specified their genders, deny access to the gender forums
		 if(empty($visitor['gender']))
		 {
		   return $this->responseError(new XenForo_Phrase('no_gender_entered', array('username' => $visitor['username'])));
		 }
		 //Make sure that they do not sneek in the wrong forum :D Admins are excluded
		 if($visitor['gender'] != $extra['user_gender'])
		 {
		   return $this->responseError(new XenForo_Phrase('wrong_gender_forums', array('username' => $visitor['username'],
		   'genderforum' => $extra['user_gender'])));
		 }
	  }	
		
	    return $parent;
    }
}