<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 * @include    ./
 */
class GFNCore_Application
{

    public static $version = '1.0.0 Beta 1';
    public static $versionId = 1000031; // abbccde = a.b.c d (alpha: 1, beta: 3, RC: 5, stable: 7, PL: 9) e

    protected static $_instance;

    public static function init(XenForo_Dependencies_Abstract $dependencies)
    {
        $obj = new self($dependencies);
        self::$_instance = &$obj;
    }

    /**
     * @var XenForo_Dependencies_Abstract
     */
    protected $_dependencies;

    protected $_preloadRegistry = array('gfncache');

    public function __construct(XenForo_Dependencies_Abstract $dependencies)
    {
        $this->_dependencies = $dependencies;

        $this->setTemplateHelper('colorIsBright', 'GFNCore_Template_Helper_Core', 'helperColorIsBright', true);
        $this->setTemplateHelper('colorBrightnessAdjustment', 'GFNCore_Template_Helper_Core', 'helperBrightnessAdjustment', true);

        XenForo_CodeEvent::fire('gfncore_init', array($this));

        $this->_preloadRegistry();
    }

    public function getDependencies()
    {
        return $this->_dependencies;
    }

    /**
     * @param $item
     * @deprecated
     */
    public function preload($item)
    {
        $this->preloadRegistry($item);
    }

    public function preloadRegistry($item)
    {
        if (!is_array($item))
        {
            $item = func_get_args();
        }

        $this->_preloadRegistry = array_merge($this->_preloadRegistry, $item);
    }

    public function preloadCache($item)
    {
        if (!is_array($item))
        {
            $item = func_get_args();
        }

        $this->_preloadCache = array_merge($this->_preloadCache, $item);
    }

    public function setTemplateHelper($name, $class, $method, $overwrite = false)
    {
        $name = strtolower($name);

        if (!$overwrite && array_key_exists($name, XenForo_Template_Helper_Core::$helperCallbacks))
        {
            throw new GFNCore_Exception(new XenForo_Phrase('gfncore_helper_already_exists'));
        }

        XenForo_Template_Helper_Core::$helperCallbacks[$name] = array($class, $method);
    }

    protected function _preloadRegistry()
    {
        $toLoad = array();

        foreach ($this->_preloadRegistry as $entry)
        {
            if (!XenForo_Application::isRegistered($entry))
            {
                $toLoad[] = $entry;
            }
        }

        if ($toLoad)
        {
            GFNCore_Registry::preload($toLoad);
        }
    }

    protected function _preloadCache()
    {
        $toLoad = array();

        foreach ($this->_preloadCache as $entry)
        {
            if (!XenForo_Application::isRegistered($entry))
            {
                $toLoad[] = $entry;
            }
        }

        if ($toLoad)
        {
            GFNCore_Cache::preload($toLoad);
        }
    }

    /**
     * @return GFNCore_Application
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    public static function isActive($addOnId)
    {
        return @array_key_exists($addOnId, GFNCore_Registry::get('addOns'));
    }

    public static function getInstalledVersion($addOnId)
    {
        $addOns = GFNCore_Registry::get('addOns');

        if (isset($addOns[$addOnId]))
        {
            return $addOns[$addOnId];
        }

        return null;
    }

    public static function getCache($index)
    {
        $cache = GFNCore_Registry::get('gfncore');

        if (isset($cache[$index]))
        {
            return $cache[$index];
        }

        return null;
    }

    public static function setCache($index, $value)
    {
        $cache = GFNCore_Registry::get('gfncore');

        if (!is_array($cache))
        {
            $cache = array();
        }

        $cache[$index] = $value;
        GFNCore_Registry::set('gfncore', $cache);
    }

    protected $_optionGroupCache = array();

    public function addTabbedOptionGroup($groupId)
    {
        $this->_optionGroupCache[$groupId] = true;
    }

    public function getTabbedOptionGroupList()
    {
        return array_keys($this->_optionGroupCache);
    }
}