<?php

class KomuKu_DiscussionsPerPage_Option
{
	public static function verifyOption(array &$choices, XenForo_DataWriter $dw, $fieldName)
	{
		$output = array();

		foreach ($choices AS $choice)
		{
			$choice = trim($choice);

			if ($choice)
			{
				if (!is_numeric($choice))
				{
					$dw->error(new XenForo_Phrase('please_using_integers_for_this'), $fieldName);
					return false;
				}

				$output[] = intval($choice);
			}
		}

		$choices = $output;

		return true;
	}
}