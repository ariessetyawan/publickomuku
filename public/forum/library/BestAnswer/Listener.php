<?php

class BestAnswer_Listener
{
	public static function load_class_controller($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Post')
		{
			$extend[] = 'BestAnswer_ControllerPublic_Post';
		}
		
		if ($class == 'XenForo_ControllerPublic_Member')
		{
			$extend[] = 'BestAnswer_ControllerPublic_Member';
		}
		
		if ($class == 'XenForo_ControllerPublic_Forum')
		{
			$extend[] = 'BestAnswer_ControllerPublic_Forum';
		}
		
		if ($class == 'XenForo_ControllerPublic_Thread')
		{
			$extend[] = 'BestAnswer_ControllerPublic_Thread';
		}
		
		if ($class == 'XenForo_ControllerAdmin_Forum')
		{
			$extend[] = 'BestAnswer_ControllerAdmin_Forum';
		}
	}
	
	public static function load_class_model($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Post')
		{
			$extend[] = 'BestAnswer_Model_Post';
		}
		
		if ($class == 'XenForo_Model_Thread')
		{
			$extend[] = 'BestAnswer_Model_Thread';
		}
		
		if ($class == 'XenForo_Model_User')
		{
			$extend[] = 'BestAnswer_Model_User';
		}
	}
	
	public static function load_class_datawriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_Discussion_Thread')
		{
			$extend[] = 'BestAnswer_DataWriter_Discussion_Thread';
		}
		
		if ($class == 'XenForo_DataWriter_User')
		{
			$extend[] = 'BestAnswer_DataWriter_User';
		}
		
		if ($class == 'XenForo_DataWriter_DiscussionMessage_Post')
		{
			$extend[] = 'BestAnswer_DataWriter_DiscussionMessage_Post';
		}
	}
	
	public static function load_class_view($class, array &$extend)
	{
		
	}
	
	public static function criteria_user($rule, array $data, array $user, &$returnValue)
	{
		if ($rule == 'best_answer_count' && $user['best_answer_count'] >= $data['answers'])
		{
			$returnValue = true;
		}
	}
}