<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfncore.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Autoloader extends XenForo_Autoloader
{
    protected static $__setup = false;

    protected static $_internalDataPath = null;

    protected static $_classTypePath = '';

    protected static $_classTypes = array();

    protected static $_proxyClasses = array();

    /**
     * @var XenForo_Autoloader
     */
    protected static $_oldInstance;

    public static function setup()
    {
        if (self::$__setup)
        {
            return;
        }

        self::$_oldInstance = static::getInstance();

        $obj = new self();
        $obj->setupAutoloader(self::$_oldInstance->getRootDir());

        static::setInstance($obj);
        self::$__setup = true;

        if (!self::$_internalDataPath)
        {
            self::$_internalDataPath = realpath($obj->getRootDir() . '/../internal_data');
        }

        self::$_classTypePath = self::$_internalDataPath . '/gfncore/autoloader/types.dat';
        if (file_exists(self::$_classTypePath))
        {
            /** @noinspection PhpIncludeInspection */
            self::$_classTypes = unserialize(file_get_contents(self::$_classTypePath));
        }
    }

    public static function revert()
    {
        if (!self::$__setup || !self::$_oldInstance)
        {
            return;
        }

        $thisInstance = static::getInstance();
        static::setInstance(self::$_oldInstance);
        unset($thisInstance);
    }

    protected function _setupAutoloader()
    {
        if (self::$_oldInstance)
        {
            spl_autoload_unregister(array(self::$_oldInstance, 'autoload'));
            spl_autoload_register(array($this, 'autoload'));
        }
        else
        {
            parent::_setupAutoloader();
        }
    }

    public function autoload($class)
    {
        if (!isset(self::$_proxyClasses[$class]) || !self::$_proxyClasses[$class])
        {
            return parent::autoload($class);
        }

        $createClass = 'GFNProxy_' . $class;

        if ($this->_getClassType($createClass, true) == 'final class')
        {
            return parent::autoload($class);
        }

        if (class_exists($class, false) || interface_exists($class, false))
        {
            return true;
        }

        if ($class == 'utf8_entity_decoder')
        {
            return true;
        }

        if (substr($class, 0, 5) == 'XFCP_')
        {
            throw new XenForo_Exception('Cannot load class using XFCP. Load the class using the correct loader first.');
        }

        $base = $this->autoloaderClassToFile($class);
        if (!$base)
        {
            return false;
        }

        $proxy = $this->_getProxyFile($class, $base);
        if (!$proxy)
        {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($proxy);

        if (!class_exists($createClass, false) && !interface_exists($createClass, false))
        {
            return false;
        }

        $type = $this->_getClassType($createClass);
        if ($type == 'final class')
        {
            return parent::autoload($class);
        }

        $extend = self::$_proxyClasses[$class];

        foreach ($extend as $dynamicClass)
        {
            $proxyClass = 'XFCP_' . $dynamicClass;
            eval($type . ' ' . $proxyClass . ' extends ' . $createClass . ' {}');
            $this->autoload($dynamicClass);
            $createClass = $dynamicClass;
        }

        eval($type . ' ' . $class . ' extends ' . $createClass . ' {}');
        return (class_exists($class, false) || interface_exists($class, false));
    }

    public function autoloaderClassToFile($class)
    {
        if (!self::isClass($class))
        {
            return false;
        }

        $file = str_replace(array('_', '\\'), '/', $class) . '.php';
        $includePaths = explode(PATH_SEPARATOR, get_include_path());

        if (!$includePaths)
        {
            $includePaths = array($this->_rootDir);
        }

        foreach ($includePaths as $path)
        {
            $path .= '/' . $file;
            if (file_exists($path))
            {
                return $path;
            }
        }

        return false;
    }

    protected function _getProxyFile($class, $base)
    {
        $proxy = self::$_internalDataPath . '/gfncore/autoloader/' . $class . '.class';

        if (is_file($proxy) && (filemtime($proxy) >= filemtime($base)))
        {
            return $proxy;
        }

        $source = file_get_contents($base);
        $source = preg_replace('#([\s\n](class|interface)[\s\n]+)(' . $class . ')([\s\n\{])#u', '$1GFNProxy_$3$4', $source, 1, $count);

        if ($count)
        {
            file_put_contents($proxy, $source);
            return $proxy;
        }

        return false;
    }

    protected function _getClassType($class, $fromCacheOnly = false)
    {
        $cache = &self::$_classTypes;

        if (isset($cache[$class]))
        {
            return $cache[$class];
        }

        if ($fromCacheOnly)
        {
            return false;
        }

        $ref = new ReflectionClass($class);

        if ($ref->isAbstract())
        {
            $type = 'abstract class';
        }
        elseif ($ref->isInterface())
        {
            $type = 'interface';
        }
        elseif ($ref->isFinal())
        {
            $type = 'final class';
        }
        elseif ($ref->isTrait())
        {
            $type = 'trait';
        }
        else
        {
            $type = 'class';
        }

        if (!$cache || !is_array($cache))
        {
            $cache = array();
        }

        $cache[$class] = $type;
        return $type;
    }

    public static function addProxy($class, $extend)
    {
        if (!is_array($extend))
        {
            $extend = array($extend);
        }

        if (!self::isClass($class))
        {
            return false;
        }

        foreach ($extend as $i => $c)
        {
            if (!self::isClass($c))
            {
                unset($extend[$i]);
            }
        }

        if (!$extend)
        {
            return false;
        }

        $classes = &self::$_proxyClasses;
        $classes[$class] = isset($classes[$class]) ? array_merge($classes[$class], $extend) : $extend;
        return true;
    }

    public static function setInternalDataPath($path)
    {
        self::$_internalDataPath = realpath($path);
    }

    public static function isClass($class)
    {
        return (preg_match('#[a-zA-Z0-9_\\\\]#', $class));
    }

    public function __destruct()
    {
        if (!self::$__setup)
        {
            return;
        }

        file_put_contents(self::$_classTypePath, serialize(self::$_classTypes));
        self::$__setup = false;
    }
} 