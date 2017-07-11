<?php

/**
 * KL_FontsManager_Listener_TinyMCE
 *
 * @author: Nerian
 * @last_edit:    05.07.2016
 */
class KL_FontsManager_Listener_TinyMCE {
	public static function mceConfiguration($mceConfigObj) {
		$fontModel = XenForo_Model::create('KL_FontsManager_Model_Fonts');
		$fontCache = $fontModel->getActiveFonts();
		
		$fonts = array();
		foreach($fontCache as $font) {
			if($font['position'] != 0)
				$fonts[] = $font['title'].'='.$font['family'];
		}
		$fonts = implode(';',$fonts);
		
		$mceConfigObj->setMceSetting('font_formats', $fonts);
	}
}