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
class GFNCore_ViewAdmin_SystemOption_View extends XenForo_ViewAdmin_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;
        $options = array();

        foreach ($params['preparedOptions'] as $optionId => $option)
        {
            $x = floor($option['display_order'] / 100);
            $options[$x][$optionId] = $option;
        }

        $renderedOptions = array();

        foreach ($options as $x => $optionGroup)
        {
            $renderedOptions[$x] = XenForo_ViewAdmin_Helper_Option::renderPreparedOptionsHtml(
                $this, $optionGroup, $params['canEditOptionDefinition']
            );
        }

        $params['renderedOptions'] = $renderedOptions;
    }
}