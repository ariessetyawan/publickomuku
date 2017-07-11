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
class GFNCore_Helper_Options
{
    public $prefix;

    public function __construct($prefix)
    {
        $this->prefix = rtrim($prefix, '_') . '_';
    }

    public function get($optionName, $subOption = null)
    {
        return XenForo_Application::getOptions()->get($this->prefix . $optionName, $subOption);
    }

    public function __get($optionName)
    {
        return $this->get($optionName);
    }

    public function __isset($optionName)
    {
        return ($this->get($optionName) !== null);
    }

    public function set($optionName, $subOption, $value = null)
    {
        XenForo_Application::getOptions()->set($this->prefix . $optionName, $subOption, $value);
    }

    public function __set($optionName, $value)
    {
        $this->set($optionName, $value);
    }
} 