<?php

class KomuKu_SimilarThreads_Listener
{
	public static function Thread($class, array &$extend)
	{
		$extend[] = 'KomuKu_SimilarThreads_ControllerPublic_Thread';
	}
	
	public static function Forum($class, array &$extend)
	{
		$extend[] = 'KomuKu_SimilarThreads_ControllerPublic_Forum';
	}	
}