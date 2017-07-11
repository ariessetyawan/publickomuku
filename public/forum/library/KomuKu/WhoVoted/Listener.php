<?php

class KomuKu_WhoVoted_Listener
{
	public static function Thread($class, array &$extend)
	{
		$extend[] = 'KomuKu_WhoVoted_ControllerPublic_Thread';
	}
}