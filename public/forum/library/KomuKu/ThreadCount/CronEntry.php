<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_CronEntry
{
	public static function recountThreads()
	{
		XenForo_Model::create('KomuKu_ThreadCount_Model')->recount();
	}
}