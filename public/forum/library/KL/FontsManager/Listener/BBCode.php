<?php

/**
 * KL_FontsManager_Listener_BBCode
 *
 *	@author: Katsulynx
 *  @last_edit:	17.09.2015
 */

class KL_FontsManager_Listener_BBCode {
    public static function extend($class, array &$extend) {
        $extend[] = 'KL_FontsManager_BbCode_Formatter_Font';
    }
}