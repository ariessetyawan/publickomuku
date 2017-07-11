<?php

/**
 * KL_EditorPostTemplates_Listener_Helper
 *
 * @author: Nerian
 * @last_edit:    10.09.2015
 */
class KL_FontsManager_Listener_Helper
{
    public static function extend(XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        //Get the static variable $helperCallbacks and add a new item in the array.
        XenForo_Template_Helper_Core::$helperCallbacks += array(
            'fonts' => array('KL_FontsManager_Helpers', 'helperFonts'),
            'additionalfonts' => array('KL_FontsManager_Helpers', 'helperAdditionalFonts'),
        );
    }
}