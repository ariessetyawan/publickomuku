<?php

class KomuKu_ForumExtras_Model_Thread extends XFCP_KomuKu_ForumExtras_Model_Thread
{
    public function canViewThread(array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{ 
        $parent = parent::canViewThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser);
		
		$visitor = XenForo_Visitor::getInstance();
		//Exlude group/s from the extra forum view settings restrictions
		if($visitor->hasPermission('forum', 'exlude_extra_forums'))
		{
		   return $parent;
		}
		
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
		
		$extra = $db->fetchRow("SELECT * FROM `kmk_forum_extra_view_settings` WHERE node_id = ?", $forum['node_id']);
		
	   //Deny access to this forum if user does not have enough posts
	   if($extra['message_count'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	$errorPhraseKey = 'must_be_registered';
	   	  	return false;
	   	  }
		  else if($visitor['message_count'] <= $extra['message_count'])
		  {
			$errorPhraseKey = array('num_posts_forums', 
			   'username' => $visitor['username'],
			   'postforums' => $extra['message_count']);
			return false;
		  }
	   }
	   
	   //Set up the daily post requirement to enter this forum
	   if($extra['daily_posts'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	$errorPhraseKey = 'must_be_registered';
	   	  	return false;
	   	  }
	   	  
		   $daycut = time() - 86400;
		   
		   $query = $db->fetchRow("SELECT COUNT(*) AS total_posts FROM  kmk_post WHERE post_date > $daycut AND user_id=" . $visitor['user_id'] . " ");
		   
		   if($query['total_posts'] <= $extra['daily_posts'])
		   {
			   $errorPhraseKey = array('daily_posts_enter_forum', 
				  'username' => $visitor['username'],
				  'dailyposts' => $extra['daily_posts']);
			   return false;
		   }
	   }
	   
	   //Deny access to this forum if user is a registered member for less than certain days
	   if($extra['register_date'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	$errorPhraseKey = 'must_be_registered';
	   	  	return false;
	   	  }
	   	  
		  $regday = time() - $extra['register_date'] * 86400;
		  
		  if($visitor['register_date'] > $regday)
		  {
			$errorPhraseKey = array('regday_forum', 
				'username' => $visitor['username'],
				'regdaysrequired' => $extra['register_date']);
			return false;
		  }
	   }
	   
	   //Set up the age requirement to enter forums
	   if($extra['user_age'] != 0)
	   {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	$errorPhraseKey = 'must_be_registered';
	   	  	return false;
	   	  }
	   	  
		  //If users have not specified their age, deny access to the age forums
		  if(!isset($userAge))
		  {
			$errorPhraseKey = array('no_age_entered', 
			   'username' => $visitor['username']);
			return false;	
		  }	
	  
		 //Deny access to the user if he/she is below the age allowed for this forum
		 if($userAge < $extra['user_age'])
		 {
			$errorPhraseKey = array('wrong_age_forum', 
			   'username' => $visitor['username'],
			   'userage' => $extra['user_age']);
			return false;
		 }   
	  }
	   
	   //Ban users from forums
	   if($extra['ban'] != "")
	   {
		  if($visitor['user_id'] && in_array($visitor['user_id'], explode( ",", $extra['ban'])))
		  {
			$errorPhraseKey = array('ban_from_forum', 
			   'username' => $visitor['username']);
			return false;	
		  }
	   }
	   
	   //Gender forums
	  if($extra['user_gender'] != "")
	  {
	   	  if (!$visitor['user_id'])
	   	  {
	   	  	$errorPhraseKey = 'must_be_registered';
	   	  	return false;
	   	  }
	   	  
		 //If users have not specified their genders, deny access to the gender forums
		 if(empty($visitor['gender']))
		 {
			$errorPhraseKey = array('no_gender_entered', 
			   'username' => $visitor['username']);
			return false;	
		 }
		 //Make sure that they do not sneek in the wrong forum :D
		 if($visitor['gender'] != $extra['user_gender'])
		 {
			$errorPhraseKey = array('wrong_gender_forums', 
			   'username' => $visitor['username'],
			   'genderforum' => $extra['user_gender']);
			return false;
		 }
	  }
        
		return $parent;
	}
		    
 }
