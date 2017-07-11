<?php

class KomuKu_ProfileCover_Option
{
	public static function get($key)
	{
		return XenForo_Application::getOptions()->get('KomuKu_ProfileCover_' . $key);
	}
}