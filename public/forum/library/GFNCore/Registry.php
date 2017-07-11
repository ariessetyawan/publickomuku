<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Registry
{
    public static function get($itemName, $forceReload = false)
    {
        if ($forceReload || !XenForo_Application::isRegistered($itemName))
        {
            $value = self::_getRegistryModel()->get($itemName);
            XenForo_Application::set($itemName, $value);
            return $value;
        }

        return XenForo_Application::get($itemName);
    }

    public static function set($itemName, $value)
    {
        self::_getRegistryModel()->set($itemName, $value);
        XenForo_Application::set($itemName, $value);
    }

    public static function delete($itemName)
    {
        self::_getRegistryModel()->delete($itemName);

        if (XenForo_Application::isRegistered($itemName))
        {
            XenForo_Application::getInstance()->offsetUnset($itemName);
        }
    }

    public static function preload(array $itemNames)
    {
        try
        {
            $items = self::_getRegistryModel()->getMulti($itemNames);

            foreach ($items as $itemName => $value)
            {
                XenForo_Application::set($itemName, $value);
            }
        }
        catch (Exception $e)
        {
            XenForo_Error::logException($e);
        }
    }

    /**
     * @return XenForo_Model_DataRegistry
     */
    protected static function _getRegistryModel()
    {
        static $model;

        if (!$model)
        {
            $model = XenForo_Model::create('XenForo_Model_DataRegistry');
        }

        return $model;
    }
} 