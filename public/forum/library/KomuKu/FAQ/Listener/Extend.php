<?php

class KomuKu_FAQ_Listener_Extend
{
    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += KomuKu_FAQ_FileSums::getHashes();
    }

    public static function load_class($class, array &$extend)
    {
        static $classes = [
            'XenForo_ControllerPublic_Search',
            'XenForo_Model_Search',
        ];

        if (in_array($class, $classes)) {
            $extend[] = str_replace('XenForo_', 'KomuKu_FAQ_', $class);
        }
    }

    /**
     * FAQ Manager Credits.
     *
     * Do not alter or delete these credits unless you have paid for their removal.
     */
    public static function credits(array $matches)
    {
        return $matches[0].
        '';
    }
}

