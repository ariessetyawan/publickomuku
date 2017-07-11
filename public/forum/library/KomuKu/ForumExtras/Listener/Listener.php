<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_Listener_Listener
{
	public static function controller($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Forum')
		{
			$extend[] = 'KomuKu_ForumExtras_ControllerPublic_Forum';
		}

        if ($class == 'XenForo_ControllerPublic_Account')
		{
			$extend[] = 'KomuKu_ForumExtras_ControllerPublic_Account';
		}		
	}
	
	public static function model($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Thread')
		{
			$extend[] = 'KomuKu_ForumExtras_Model_Thread';
		}
	}
	
}