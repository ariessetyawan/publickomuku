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
abstract class GFNCore_Rebuilder
{
    /**
     * Get everything constructed here as some resources might
     * not be available when the destruct is called.
     */
    final public function __construct()
    {
        $this->_construct();
    }

    protected $_executed = false;

    protected function _executed()
    {
        return $this->_executed;
    }

    /**
     * Get everything constructed here as some resources might
     * not be available when the destruct is called.
     */
    abstract protected function _construct();

    /**
     * The main rebuilding occurs here.
     */
    final public function __destruct()
    {
        if ($this->_executed())
        {
            return;
        }

        try
        {
            $this->_destruct();
            $this->_executed = true;
        }
        catch (Exception $e)
        {
            // To make sure that the PHP closing sequence is not disrupted.
            XenForo_Error::logException($e, true, 'Unable to rebuild \'' . get_called_class() . '\': ');
        }
    }

    /**
     * The main rebuilding occurs here.
     */
    abstract protected function _destruct();

    /**
     * @param $class
     * @return GFNCore_Rebuilder
     * @throws Zend_Exception
     */
    final public static function start($class)
    {
        if (!XenForo_Application::isRegistered($class))
        {
            XenForo_Application::set($class, new $class());
        }

        return XenForo_Application::get($class);
    }

    /**
     * @return static
     * @throws GFNCore_Exception
     */
    public static function init()
    {
        return static::get();
    }

    /**
     * @return static
     * @throws GFNCore_Exception
     */
    public static function get()
    {
        $class = get_called_class();

        if ($class === __CLASS__)
        {
            throw new GFNCore_Exception('This method needs to be called from a child class.');
        }

        return static::start($class);
    }
}