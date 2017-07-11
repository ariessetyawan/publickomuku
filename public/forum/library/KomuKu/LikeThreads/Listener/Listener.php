<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Listener_Listener
{
    public static function controller($class, array &$extend)
	{
	    if ($class == 'XenForo_ControllerPublic_Account')
        {
			$extend[] = 'KomuKu_LikeThreads_ControllerPublic_Account';
        }
		
		if ($class == 'XenForo_ControllerPublic_Forum')
		{
			$extend[] = 'KomuKu_LikeThreads_ControllerPublic_Forum';
		}
		
		if ($class == 'XenForo_ControllerAdmin_Log')
		{
			$extend[] = 'KomuKu_LikeThreads_ControllerAdmin_Log';
		}
		
		if ($class == 'XenForo_ControllerPublic_Member')
		{
			$extend[] = 'KomuKu_LikeThreads_ControllerPublic_Member';
		}
		
		if ($class == 'XenForo_ControllerPublic_Thread')
		{
			$extend[] = 'KomuKu_LikeThreads_ControllerPublic_Thread';
		}
		
	    if ($class == 'XenForo_ControllerAdmin_User')
        {
            $extend[] = 'KomuKu_LikeThreads_ControllerAdmin_User';
        }
	}
	
	public static function model($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Log')
		{
			$extend[] = 'KomuKu_LikeThreads_Model_Log';
		}

        if ($class == 'XenForo_Model_Thread')
		{
			$extend[] = 'KomuKu_LikeThreads_Model_Thread';
		}

        if ($class == 'XenForo_Model_User')
		{
			$extend[] = 'KomuKu_LikeThreads_Model_User';
		}		
	}
	
	public static function dataWriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_Forum')
		{ 
			$extend[] = 'KomuKu_LikeThreads_DataWriter_Forum';
		}
	}
	
	public static function criteriaUser($rule, array $data, array $user, &$returnValue)
	{
		switch ($rule)
		{
			case 'liked_thread_count':
			
				if (!isset($user['liked_thread_count']))
				{
					$returnValue = false;
				}
				
				if (isset($user['liked_thread_count']) && $user['liked_thread_count'] >= $data['items'])
				{
					$returnValue = true;
				}
				
				break;
		}
	}
	
	//Controls the most liekd threads tab in the navbar and where it will display
	public static function popularTab(array &$extraTabs, $selectedTabId)
	{
	    /** @var $model KomuKu_LikeThreads_Model_LikeThreads */
		$model = XenForo_Model::create('KomuKu_LikeThreads_Model_LikeThreads');
		
		//Get the $options variables
		$options = XenForo_Application::get('options');
		
		//Enable the tab and get permissions to view it
		if ($options->most_liked_threads_archive AND $model->canViewMostLikedPage())
		{	
			$extraTabs['popular'] = array(
				'title'			=> new XenForo_Phrase ('th_most_liked_threads'),
				'href'			=> XenForo_Link::buildPublicLink('popular'),
				'selected'      => ($selectedTabId == 'popular'),
				'position'		=> $options->liked_threads_tab_position,
				'linksTemplate' => 'th_most_liked_threads_subnavbar',
			);    
		}
	}
}