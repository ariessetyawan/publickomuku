<?php

class KomuKu_Emoticons_Option
{
	public static function get($key)
	{
		$value = XenForo_Application::getOptions()->get('emoticons_'.$key);
		if($key === 'maxSize')
		{
			$value *= 1024;
		}

		return $value;
	}
}