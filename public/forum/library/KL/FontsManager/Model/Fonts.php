<?php

/**
 * KL_FontsManager_Model_Fonts
 *
 *	@author: Nerian
 *  @last_edit:	05.07.2016
 */

class KL_FontsManager_Model_Fonts extends XenForo_Model {
    /*
     * @return array
     *  @last_edit:	10.09.2015
     */
    public function getFonts() {
        return $this->_getDb()->fetchAll('
            SELECT *
            FROM kmk_kl_fm_fonts
            ORDER BY `position`
        ');
    }
	
    /*
     * @return array
     *  @last_edit:	05.07.2016
     */
    public function getActiveFonts() {
		if(!XenForo_Application::getSimpleCacheData('kl_fm_active_fonts'))
			$this->rebuildCache();
		return XenForo_Application::getSimpleCacheData('kl_fm_active_fonts');
    }

    /*
     * @return array
     *  @last_edit:	05.09.2015
     */
    public function getFontById($id) {
        return $this->_getDb()->fetchRow('
            SELECT *
            FROM kmk_kl_fm_fonts
            WHERE id = ?
            ORDER BY `position`
        ', $id);
    }

    /*
     * @return array
     *  @last_edit:	17.09.2015
     */
    public function getWebfonts() {
        return $this->_getDb()->fetchAll('
            SELECT *
            FROM kmk_kl_fm_webfonts
        ');
    }
    
    /*
     * @return array
     *  @last_edit:	05.07.2016
     */
    public function getActiveWebfonts() {
		if(!XenForo_Application::getSimpleCacheData('kl_fm_active_webfonts'))
			$this->rebuildCache();
		return XenForo_Application::getSimpleCacheData('kl_fm_active_webfonts');
    }
	
    /*
     * @return array
     *  @last_edit:	17.09.2015
     */
    public function getWebfontById($id) {
        return $this->_getDb()->fetchRow('
            SELECT *
            FROM kmk_kl_fm_webfonts
            WHERE id = ?
        ', $id);
    }
	
    /*
     *  @last_edit:	05.07.2016
     */
	public function rebuildCache() {
		$activeWebfonts = $this->_getDb()->fetchAll('
			SELECT *
			FROM kmk_kl_fm_webfonts
			WHERE `active` = 1
		');
		
		$activeFonts = $this->_getDb()->fetchAll('
			SELECT *
			FROM kmk_kl_fm_fonts
			WHERE `active` = 1
			ORDER BY `position`
		');
		
		XenForo_Application::setSimpleCacheData('kl_fm_active_webfonts',$activeWebfonts);
		XenForo_Application::setSimpleCacheData('kl_fm_active_fonts',$activeFonts);
		
		XenForo_Application::setSimpleCacheData('kl_fm_active_keys', array(
			'webfont_keys' => array_map(function($element) {return $element['title'];}, $activeWebfonts),
			'font_keys' => array_map(function($element) {return $element['title'];}, $activeFonts)
		));
	}
	
    /*
     * @return array
     *  @last_edit:	05.07.2016
     */
    private function _getActiveKeys() {
		if(!XenForo_Application::getSimpleCacheData('kl_fm_active_keys'))
			$this->rebuildCache();
		return XenForo_Application::getSimpleCacheData('kl_fm_active_keys');
    }
	
	/*
     * @return array
     *  @last_edit:	05.07.2016
     */
    public function getFontData() {
        return array_merge(
			array(
				'fonts' => $this->getActiveFonts(),
				'webfonts' => $this->getActiveWebfonts(),
			),
			$this->_getActiveKeys()
		);
    }
	
	/*
     *  @last_edit:	05.07.2016
	 */
	public function cleanFontDirectory() {
		$dataDirectory = XenForo_Helper_File::getExternalDataPath().'/fonts/';
		$files = scandir($dataDirectory);
		unset($files[0],$files[1]);
		$fonts = $this->getFonts();
		foreach($fonts as $key => &$font) {
			if($font['type'] !== 'local') {
				unset($fonts[$key]);
			}
			else {
				$fonts[$key] = $font['family'].'.woff';
			}
		}
		
		foreach($files as $file) {
			if(!in_array($file, $fonts)) {
				unlink($dataDirectory . $file);
			}
		}
	}
}