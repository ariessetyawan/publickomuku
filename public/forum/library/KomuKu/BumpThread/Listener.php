<?php

class KomuKu_BumpThread_Listener
{

	/**
	 * Modify XenForo controllers
	 */
	public static function loadController($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Thread')
		{
			$extend[] = 'KomuKu_BumpThread_ControllerPublic_Thread';
		}
	}

	/**
	 * Modify XenForo models
	 */
	public static function loadModel($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Thread')
		{
			$extend[] = 'KomuKu_BumpThread_Model_Thread';
		}
	}

}
