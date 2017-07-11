<?php

class KomuKu_NodeConverter_Listener
{
	protected static $_classes = array(
		'XenForo_ControllerAdmin_Forum',
		'XenForo_ControllerAdmin_Category',
		'XenForo_ControllerPublic_Forum',
		'XenForo_ControllerPublic_Category',
		'XenForo_Model_Forum',
		'XenForo_Model_Node'
	);

    public static function load_class($class, array &$extend)
    {
       if (in_array($class, self::$_classes))
       {
	       $extend[] = 'KomuKu_NodeConverter_' . $class;
       }
    }
}