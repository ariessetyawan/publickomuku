<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Cache
{
    const MINUTE    = 60;
    const HOUR      = 3600;
    const DAY       = 86400;
    const WEEK      = 604800;
    const MONTH     = 2592000;
    const YEAR      = 31536000;

    public static function get($itemName, $forceReload = false)
    {
        if ($forceReload || !XenForo_Application::isRegistered($itemName))
        {
            $value = self::_getCacheModel()->get($itemName);
            XenForo_Application::set($itemName, $value);
            return $value;
        }

        return XenForo_Application::get($itemName);
    }

    public static function set($itemName, $value, $lifetime)
    {
        self::_getCacheModel()->set($itemName, $value, $lifetime);
        XenForo_Application::set($itemName, $value);
    }

    public static function delete($itemName, $onlyIfExpired = false)
    {
        if (self::_getCacheModel()->delete($itemName, $onlyIfExpired) && XenForo_Application::isRegistered($itemName))
        {
            XenForo_Application::getInstance()->offsetUnset($itemName);
        }
    }

    public static function preload(array $itemNames)
    {
        try
        {
            $items = self::_getCacheModel()->getMulti($itemNames);

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
     * @return GFNCore_Model_DataCache
     */
    protected static function _getCacheModel()
    {
        static $model;

        if (!$model)
        {
            $model = XenForo_Model::create('GFNCore_Model_DataCache');
        }

        return $model;
    }
}