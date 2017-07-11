<?php

class KomuKu_Emoticons_String
{
	/**
	 * Attach an user id into string
	 *
	 * @return string
	 */
	public static function attach($userId, $string)
	{
		return sprintf('%032d', (int)$userId) . $string;
	}

	/**
	 * Deattach user id from string
	 *
	 * @return integer|null
	 */
	public static function deattach(&$string)
	{
		$encoded = substr($string, 0, 32);
		if(!preg_match('/^[0-9]+$/', $encoded))
		{
			return;
		}

		$string = substr($string, 32);
		return intval($encoded);
	}
}