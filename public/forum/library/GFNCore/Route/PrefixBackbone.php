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
abstract class GFNCore_Route_PrefixBackbone implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    protected $_major = '';
    protected $_minor = '';

    protected $_copyrightPhrase = false;
    protected $_copyrightLink = false;
    protected $_routeClasses;

    abstract protected function _getRouteClasses();

    /**
     * @return array
     */
    public function getRouteClasses()
    {
        if ($this->_routeClasses === null)
        {
            $this->_routeClasses = $this->_getRouteClasses();
        }

        return $this->_routeClasses;
    }

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        if ($this->_copyrightPhrase)
        {
            XenForo_Application::set('GFNCCopyrightPhrase', $this->_copyrightPhrase);
        }

        if ($this->_copyrightLink)
        {
            XenForo_Application::set('GFNCCopyrightLink', $this->_copyrightLink);
        }

        $routeClass = $this->fetchClass($routePath);
        if (!$routeClass)
        {
            return false;
        }

        if (is_callable($routeClass))
        {
            $routeClass = call_user_func($routeClass, $routePath, $request, $router);
            if ($routeClass instanceof XenForo_RouteMatch)
            {
                $routeClass->setSections(
                    $routeClass->getMajorSection() ?: $this->_major,
                    $routeClass->getMinorSection() ?: $this->_minor
                );

                return $routeClass;
            }
        }

        if (!is_string($routeClass))
        {
            return false;
        }

        if (substr($routeClass, 0, 11) === 'controller:')
        {
            @list ($controller, $action) = preg_split('/::|->|\./', substr($routeClass, 11), 2, PREG_SPLIT_NO_EMPTY);
            return $router->getRouteMatch($controller, $action ?: $routePath, $this->_major, $this->_minor);
        }

        $route = $this->_getRouteObject($routeClass);
        if (!$route)
        {
            return false;
        }

        // You should follow XenForo's interface...
        if ($route instanceof XenForo_Route_Interface)
        {
            $return = $route->match($routePath, $request, $router);
            if ($return instanceof XenForo_RouteMatch)
            {
                $return->setSections(
                    $return->getMajorSection() ?: $this->_major,
                    $return->getMinorSection() ?: $this->_minor
                );
            }

            return $return;
        }

        return false;
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $routeClass = $this->fetchClass($action, $outputPrefix);
        if (!$routeClass)
        {
            return false;
        }

        if (!is_string($routeClass))
        {
            return false;
        }

        if (substr($routeClass, 0, 11) === 'controller:')
        {
            return false;
        }

        $route = $this->_getRouteObject($routeClass);
        if (!$route)
        {
            return false;
        }

        // You should follow XenForo's interface...
        if ($route instanceof XenForo_Route_BuilderInterface)
        {
            $return = $route->buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);

            if (is_string($return))
            {
                $return = str_replace('//', '/', $return);
            }

            return $return;
        }

        return false;
    }

    /**
     * @param $routePath
     * @param string $outputPrefix
     * @param array $classes
     * @return string|callable|false
     */
    public function fetchClass(&$routePath, &$outputPrefix = '', array $classes = null)
    {
        $pieces = explode('/', $routePath, 2);
        $prefix = $pieces[0] == '' ? 'index' : $pieces[0];

        if ($classes === null)
        {
            $classes = $this->getRouteClasses();
        }
        elseif (empty($classes) || !is_array($classes))
        {
            return false;
        }

        if (array_key_exists($prefix, $classes))
        {
            $outputPrefix .= ($outputPrefix ? '/' : '') . $prefix;
            $routePath = isset($pieces[1]) ? $pieces[1] : '';

            if (is_string($classes[$prefix]) || is_callable($classes[$prefix]))
            {
                return $classes[$prefix];
            }
            elseif (($prefix != 'index') && is_array($classes[$prefix]))
            {
                return $this->fetchClass($routePath, $outputPrefix, $classes[$prefix]);
            }
        }
        elseif (isset($classes['default']) && (is_string($classes['default']) || is_callable($classes['default'])))
        {
            return $classes['default'];
        }

        return false;
    }

    public function resolveActionWithStringParam($routePath, Zend_Controller_Request_Http $request, $paramName)
    {
        $pieces = explode('/', $routePath, 2);

        if (isset($pieces[1]))
        {
            $request->setParam($paramName, $pieces[0]);
            return $pieces[1];
        }

        return $routePath;
    }

    /**
     * @param $routeClass
     * @return XenForo_Route_Interface|XenForo_Route_BuilderInterface
     */
    final protected function _getRouteObject($routeClass)
    {
        if (XenForo_Application::autoload($routeClass))
        {
            $routeClass = XenForo_Application::resolveDynamicClass($routeClass, 'route_prefix');
            return new $routeClass();
        }

        return false;
    }
}