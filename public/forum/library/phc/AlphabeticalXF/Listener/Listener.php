<?php

class phc_AlphabeticalXF_Listener_Listener extends XenForo_ControllerPublic_Abstract
{
    public static function load_class_controller($class, array &$extend)
    {
        switch($class)
        {
            case 'XenResource_ControllerPublic_Resource':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenResource_ControllerPublic_Resource';
                break;

            case 'XenGallery_ControllerPublic_Album':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenGallery_ControllerPublic_Album';
                break;

            case 'XenForo_ControllerPublic_Forum':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Forum';
                break;

            case 'XenForo_ControllerPublic_Member':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Member';
                break;

            case 'XenForo_ControllerPublic_Conversation':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Conversation';
                break;
        }
    }

    public static function load_class_model($class, array &$extend)
    {
        switch($class)
        {
            case 'XenResource_Model_Resource':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenResource_Model_Resource';
                break;

            case 'XenGallery_Model_Album':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenGallery_Model_Album';
                break;

            case 'XenGallery_Model_Category':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenGallery_Model_Category';
                break;

            case 'XenForo_Model_Thread':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_Model_Thread';
                break;

            case 'XenForo_Model_Conversation':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_Model_Conversation';
                break;

            case 'XenForo_Model_User':
                $extend[] = 'phc_AlphabeticalXF_Extend_XenForo_Model_User';
                break;
        }
    }

    public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        $GLOBALS['alpha'] = '';
    }
}