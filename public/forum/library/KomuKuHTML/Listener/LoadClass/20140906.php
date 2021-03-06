<?php

class KomuKuHTML_Listener_LoadClass
{

    protected $_class = null;

    protected $_extend = null;

    protected $_type = '';

    protected static $_runOnce = array();

    /**
     *
     * @param string $class
     * @param array $extend
     */
    public function __construct(&$class = '', array &$extend = array(), $type = '')
    {
        $this->_class = $class;
        $this->_extend = $extend;
        $this->_type = $type;
    } /* END __construct */

    /**
     * Called when instantiating any class.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClass($class, array &$extend, $type = '')
    {
        if (function_exists('get_called_class')) {
            $className = get_called_class();
        } else {
            $className = get_class();
        }

        $extend = self::createAndRun($className, $class, $extend, $type);
    } /* END loadClass */

    /**
     * Called when instantiating an authentication module.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassAuthentication($class, array &$extend)
    {
        self::loadClass($class, $extend, 'authentication');
    } /* END loadClassAuthentication */

    /**
     * Called when instantiating a BB code formatter.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassBBCode($class, array &$extend)
    {
        self::loadClass($class, $extend, 'bb_code');
    } /* END loadClassBBCode */

    /**
     * Called when instantiating a calendar handler.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassCalendarHandler($class, array &$extend)
    {
        self::loadClass($class, $extend, 'calendar_handler');
    } /* END loadClassCalendarHandler */

    /**
     * Called when instantiating a content permission handler.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassContentPermission($class, array &$extend)
    {
        self::loadClass($class, $extend, 'contentpermission');
    } /* END loadClassContentPermission */

    /**
     * Called when instantiating a controller.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassController($class, array &$extend)
    {
        self::loadClass($class, $extend, 'controller');
    } /* END loadClassController */

    /**
     * Called when instantiating a data writer.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassDataWriter($class, array &$extend)
    {
        self::loadClass($class, $extend, 'datawriter');
    } /* END loadClassDataWriter */

    /**
     * Called when instantiating a deferred process.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassDeferred($class, array &$extend)
    {
        self::loadClass($class, $extend, 'deferred');
    } /* END loadClassDeferred */

    /**
     * Called when instantiating a helper.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassHelper($class, array &$extend)
    {
        self::loadClass($class, $extend, 'helper');
    } /* END loadClassHelper */

    /**
     * Called when instantiating an image processor.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassImage($class, array &$extend)
    {
        self::loadClass($class, $extend, 'image');
    } /* END loadClassImage */

    /**
     * Called when instantiating an importer.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassImporter($class, array &$extend)
    {
        self::loadClass($class, $extend, 'importer');
    } /* END loadClassImporter */

    /**
     * Called when instantiating an installer.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassInstallerKomuKuHTML($class, array &$extend)
    {
        self::loadClass($class, $extend, 'installer_KomuKuHTML');
    } /* END loadClassInstallerKomuKuHTML */

    /**
     * Called when instantiating a listener.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassListenerKomuKuHTML($class, array &$extend)
    {
        self::loadClass($class, $extend, 'listener_KomuKuHTML');
    } /* END loadClassListenerKomuKuHTML */

    /**
     * Called when instantiating a mail object.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassMail($class, array &$extend)
    {
        self::loadClass($class, $extend, 'mail');
    } /* END loadClassMail */

    /**
     * Called when instantiating a model.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassModel($class, array &$extend)
    {
        self::loadClass($class, $extend, 'model');
    } /* END loadClassModel */

    /**
     * Called when instantiating a PDF.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassPdfKomuKuHTML($class, array &$extend)
    {
        self::loadClass($class, $extend, 'pdf_KomuKuHTML');
    } /* END loadClassPdfKomuKuHTML */

    /**
     * Called when instantiating a PHP file.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassPhpFileKomuKuHTML($class, array &$extend)
    {
        self::loadClass($class, $extend, 'php_file_KomuKuHTML');
    } /* END loadClassPhpFileKomuKuHTML */

    /**
     * Called when instantiating the proxy outputter.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassProxyOutput($class, array &$extend)
    {
        self::loadClass($class, $extend, 'proxyoutput');
    } /* END loadClassProxyOutput */

    /**
     * Called when instantiating a reward handler.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassRewardHandler($class, array &$extend)
    {
        self::loadClass($class, $extend, 'reward_handler');
    } /* END loadClassRewardHandler */

    /**
     * Called when instantiating a specific route prefix class.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassRoutePrefix($class, array &$extend)
    {
        self::loadClass($class, $extend, 'route_prefix');
    } /* END loadClassRoutePrefix */

    /**
     * Called when instantiating a search data handler.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassSearchData($class, array &$extend)
    {
        self::loadClass($class, $extend, 'search_data');
    } /* END loadClassSearchData */

    /**
     * Called when instantiating a search source.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassSearchSource($class, array &$extend)
    {
        self::loadClass($class, $extend, 'search_source');
    } /* END loadClassSearchSource */

    /**
     * Called when instantiating a template.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassTemplateKomuKuHTML($class, array &$extend)
    {
        self::loadClass($class, $extend, 'template_KomuKuHTML');
    } /* END loadClassTemplateKomuKuHTML */

    /**
     * Called when instantiating a view.
     * This event can be used to extend the class that will be instantiated
     * dynamically.
     *
     * @param string $class - the name of the class to be created
     * @param array &$extend - a modifiable list of classes that wish to extend
     * the class.
     */
    public static function loadClassView($class, array &$extend)
    {
        self::loadClass($class, $extend, 'view');
    } /* END loadClassView */

    /**
     *
     * @return array $extend
     */
    public function run()
    {
        $extends = $this->_getExtends();

        if ($this->_type) {
            $allExtendedClasses = $this->_getExtendedClasses();
            foreach ($allExtendedClasses as $addOnId => $extendedClasses) {
                if (isset($extendedClasses[$this->_type])) {
                    foreach ($extendedClasses[$this->_type] as $class) {
                        if ($class == $this->_class) {
                            $this->_extend[] = $addOnId . '_Extend_' . $class;
                        }
                    }
                }
            }
        }

        if (!empty($extends)) {
            foreach ($extends as $class => $extend) {
                if ($class == $this->_class && !empty($extend)) {
                    if (is_array($extend)) {
                        foreach ($extend as $extendClass) {
                            if (!in_array($extendClass, $this->_extend)) {
                                $this->_extend[] = $extendClass;
                            }
                        }
                    } else {
                        if (!in_array($extend, $this->_extend)) {
                            $this->_extend[] = $extend;
                        }
                    }
                }
            }
        }

        foreach ($this->_extend as $key => $extend) {
            if (class_exists('XFCP_' . $extend, false)) {
                unset($this->_extend[$key]);
            }
        }

        if (!in_array($this->_class, self::$_runOnce)) {
            $this->_runOnce();
        }

        return $this->_extend;
    } /* END run */

    /**
     *
     * @return array
     */
    protected function _run()
    {
        if (XenForo_Application::debugMode()) {
            return $this->run();
        }
        try {
            return $this->run();
        } catch (Exception $e) {
            return $this->_params;
        }
    } /* END _run */

    protected function _runOnce()
    {
        if ($this->_type == 'controller' && !empty($this->_extend)) {
            $hints = XenForo_Application::get('options')->KomuKuHTML_loadClassHints;

            foreach ($this->_extend as $extend) {
                $hints[$this->_class][] = $extend;
            }
            array_unique($hints[$this->_class]);

            XenForo_Application::get('options')->set('KomuKuHTML_loadClassHints', $hints);
        }

        self::$_runOnce[] = $this->_class;
    } /* END _runOnce */

    /**
     *
     * @return array $extend
     */
    protected function _getExtends()
    {
        return array();
    } /* END _getExtends */

    /**
     *
     * @return array ([type] => array ([addon id] => array))
     */
    protected function _getExtendedClasses()
    {
        return array();
    } /* END _getExtendedClasses */

    /**
     *
     * @return array
     */
    public function getExtendedClasses($classesOnly = true, $addOnId = '', $type = '')
    {
        if ($classesOnly || ($addOnId && $type)) {
            $extends = array_filter($this->_getExtends());

            foreach ($extends as $class => $extend) {
                if (!is_array($extend)) {
                    $extends[$class] = array(
                        $extend
                    );
                }
            }
        }

        if ($classesOnly) {
            $allExtendedClasses = $this->_getExtendedClasses();
            foreach ($allExtendedClasses as $addOnId => $extendedClasses) {
                foreach ($extendedClasses as $loadClassType => $typeExtendedClasses) {
                    if ($this->_type && $this->_type != $loadClassType) {
                        continue;
                    }
                    foreach ($typeExtendedClasses as $class) {
                        if (!isset($extends[$class]) || !array_search($addOnId . '_Extend_' . $class, $extends[$class])) {
                            $extends[$class][] = $addOnId . '_Extend_' . $class;
                        }
                    }
                }
            }

            return $extends;
        }

        $extendedClasses = $this->_getExtendedClasses();

        if ($addOnId && $type) {
            foreach ($extends as $class => $extend) {
                if (in_array($addOnId . '_Extend_' . $class, $extend)) {
                    if (!isset($extendedClasses[$addOnId][$type]) || !in_array($class,
                        $extendedClasses[$addOnId][$type])) {
                        $extendedClasses[$addOnId][$type][] = $class;
                    }
                }
            }
        }

        return $extendedClasses;
    } /* END getExtendedClasses */

    /**
     * Gets the specified model object from the cache.
     * If it does not exist,
     * it will be instantiated.
     *
     * @param string $class Name of the class to load
     *
     * @return XenForo_Model
     */
    public function getModelFromCache($class)
    {
        if (!isset($this->_modelCache[$class])) {
            $this->_modelCache[$class] = XenForo_Model::create($class);
        }

        return $this->_modelCache[$class];
    } /* END getModelFromCache */

    /**
     * Factory method to get the named load class listener.
     * The class must exist or be autoloadable or an exception will be thrown.
     *
     * @param string $className Class to load
     * @param string $class
     * @param array $extend
     * @param string $type
     *
     * @return KomuKuHTML_Listener_LoadClass
     */
    public static function create($className, $class, array &$extend, $type = '')
    {
        $createClass = XenForo_Application::resolveDynamicClass($className, 'listener_KomuKuHTML');
        if (!$createClass) {
            throw new XenForo_Exception("Invalid listener '$className' specified");
        }

        return new $createClass($class, $extend, $type);
    } /* END create */

    /**
     *
     * @return array
     */
    public static function createAndRun($className, $class, array &$extend, $type = '')
    {
        $createClass = self::create($className, $class, $extend, $type);

        return $createClass->_run();
    } /* END createAndRun */

    /**
     *
     * @deprecated Deprecated.
     *
     * @param $class
     * @param $extend
     */
    protected static function _extend($class, array &$extend)
    {
        if (!in_array($class, $extend)) {
            $extend[] = $class;
        }
    } /* END _extend */
}