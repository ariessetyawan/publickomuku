<?php

//######################## Extra Thread View Settings By KomuKu ###########################
class KomuKu_ThreadExtras_Listener_Listener
{
    //Load Class Controller
	public static function controller($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Thread')
		{
			$extend[] = 'KomuKu_ThreadExtras_ControllerPublic_Thread';
		}

        if ($class == 'XenForo_ControllerPublic_Account')
		{
			$extend[] = 'KomuKu_ThreadExtras_ControllerPublic_Account';
		}		
	}
	
	//Load Class Datawriter
	public static function dataWriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_Discussion_Thread')
		{ 
			$extend[] = 'KomuKu_ThreadExtras_DataWriter_Discussion_Thread';
		}
	}
	
	//Load Class Model
	public static function model($class, array &$extend)
	{
	    if ($class == 'XenForo_Model_Post')
		{
			$extend[] = 'KomuKu_ThreadExtras_Model_Post';
		}
	}
	
}