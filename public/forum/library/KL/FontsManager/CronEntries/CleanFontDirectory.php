<?php

/**
 * KL_FontsManager_CronEntries_ClearFontDirectory
 *
 * @author: Nerian
 * @last_edit:    05.07.2016
 */

class KL_FontsManager_CronEntries_CleanFontDirectory {
	public static function cleanup() {
		XenForo_Model::create('KL_FontsManager_Model_Fonts')->cleanFontDirectory();
	}
}