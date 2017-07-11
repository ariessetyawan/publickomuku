<?php

class KomuKu_Bookmark_Listener
{
	public static function Post($class, array &$extend)
	{
		$extend[] = 'KomuKu_Bookmark_ControllerPublic_Post';
	}
	
	public static function Thread($class, array &$extend)
	{
		$extend[] = 'KomuKu_Bookmark_ControllerPublic_Thread';
	}		
}