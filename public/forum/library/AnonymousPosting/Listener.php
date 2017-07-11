<?php

class AnonymousPosting_Listener
{
    protected static $_originalHelperAvatarUrl = null;

    public static function load_class_XenForo_ControllerPublic_Forum($class, array &$extend)
    {
        if ($class === 'XenForo_ControllerPublic_Forum') {
            $extend[] = 'AnonymousPosting_XenForo_ControllerPublic_Forum';
        }
    }

    public static function load_class_XenForo_ControllerPublic_Thread($class, array &$extend)
    {
        if ($class === 'XenForo_ControllerPublic_Thread') {
            $extend[] = 'AnonymousPosting_XenForo_ControllerPublic_Thread';
        }
    }

    public static function load_class_4f477c58235ffb475271e2521731d700($class, array &$extend)
    {
        if ($class === 'XenForo_DataWriter_DiscussionMessage_Post') {
            $extend[] = 'AnonymousPosting_XenForo_DataWriter_DiscussionMessage_Post';
        }
    }

    public static function load_class_XenForo_DataWriter_Discussion_Thread($class, array &$extend)
    {
        if ($class === 'XenForo_DataWriter_Discussion_Thread') {
            $extend[] = 'AnonymousPosting_XenForo_DataWriter_Discussion_Thread';
        }
    }

    public static function load_class_XenForo_Model_Forum($class, array &$extend)
    {
        if ($class === 'XenForo_Model_Forum') {
            $extend[] = 'AnonymousPosting_XenForo_Model_Forum';
        }
    }

    public static function load_class_XenForo_Model_Session($class, array &$extend)
    {
        if ($class === 'XenForo_Model_Session') {
            $extend[] = 'AnonymousPosting_XenForo_Model_Session';
        }
    }

    public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        if (XenForo_Application::getOptions()->get('anonymous_roboHash') > 0) {
            self::$_originalHelperAvatarUrl = XenForo_Template_Helper_Core::$helperCallbacks['avatar'];
            if (!empty(self::$_originalHelperAvatarUrl)
                && self::$_originalHelperAvatarUrl[0] === 'self'
            ) {
                self::$_originalHelperAvatarUrl[0] = 'XenForo_Template_Helper_Core';
            }
            XenForo_Template_Helper_Core::$helperCallbacks['avatar'] = array(__CLASS__, 'helperRoboHashUrl');
        }

        AnonymousPosting_ShippableHelper_Updater::onInitDependencies($dependencies, null, 'anonymous_posting');
    }

    public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += AnonymousPosting_FileSums::getHashes();
    }

    public static function helperRoboHashUrl()
    {
        $args = func_get_args();

        $url = call_user_func_array(array('AnonymousPosting_Engine', 'helperRoboHashUrl'), $args);
        if (!empty($url)) {
            return $url;
        }

        if (!empty(self::$_originalHelperAvatarUrl)) {
            return call_user_func_array(self::$_originalHelperAvatarUrl, $args);
        }

        return '';
    }
}
