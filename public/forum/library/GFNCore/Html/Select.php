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
class GFNCore_Html_Select extends GFNCore_Html_Abstract
{
    protected $_value;

    protected function _getTagName()
    {
        return 'select';
    }

    protected function _isSelfClosed()
    {
        return false;
    }

    protected function _requiredAttributes()
    {
        return array(
            'name', 'id'
        );
    }

    public function value($value)
    {
        $this->_value = $value;
    }

    public function option($label, $value, $disabled = false)
    {
        $this->_contents .= sprintf(
            '<option value="%s"%s%s>%s</option>', $value,
            $value == $this->_value ? ' selected="selected"' : '',
            $disabled ? ' disabled="disabled"' : '', $label
        );
    }
}