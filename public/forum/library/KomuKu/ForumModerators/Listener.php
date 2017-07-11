<?php

class KomuKu_ForumModerators_Listener
{
	public static function Forum($class, array &$extend)
	{
		$extend[] = 'KomuKu_ForumModerators_ControllerPublic_Forum';
	}	
}