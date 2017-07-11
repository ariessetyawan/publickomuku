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
abstract class GFNCore_Html_Abstract
{
    protected $_attributes = array();

    protected $_contents = '';

    public function attribute($name, $value)
    {
        $this->_attributes[$name] = htmlspecialchars($value);
        return $this;
    }

    public function data($name, $value)
    {
        return $this->attribute('data-' . $name, $value);
    }

    /**
     * @throws GFNCore_Exception
     * @return string;
     */
    public function parse()
    {
        if ($this->_requiredAttributes() && count(XenForo_Application::arrayFilterKeys($this->_attributes, $this->_requiredAttributes())) != count($this->_requiredAttributes()))
        {
            throw new GFNCore_Exception('Some of the required attributes are missing.');
        }

        $attr = '';

        foreach ($this->_attributes as $name => $value)
        {
            $attr .= ' ' . $name . '="' . $value . '"';
        }

        $output = sprintf('<%s%s', $this->_getTagName(), $attr);

        if ($this->_isSelfClosed())
        {
            return $output . ' />';
        }
        else
        {
            return $output . sprintf('>%s</%s>', $this->_contents, $this->_getTagName());
        }
    }

    abstract protected function _isSelfClosed();

    abstract protected function _getTagName();

    /**
     * @return array
     */
    abstract protected function _requiredAttributes();

    public function __toString()
    {
        return $this->parse();
    }
}