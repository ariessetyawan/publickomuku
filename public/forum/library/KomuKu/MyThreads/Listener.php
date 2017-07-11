<?php

class KomuKu_MyThreads_Listener
{	
	public static function Forum($class, array &$extend)
	{
		$extend[] = 'KomuKu_MyThreads_ControllerPublic_Forum';
	}	
}