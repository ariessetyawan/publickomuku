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
class GFNCore_Template_Helper_Core
{
    public static function helperColorIsBright($color)
    {
        $obj = new GFNCore_Helper_Color($color);
        return $obj->isBright();
    }

    public static function helperBrightnessAdjustment($color, $adjust)
    {
        $adjust = max(-255, min(255, $adjust));
        $obj = new GFNCore_Helper_Color($color);

        $colors = array();
        foreach ($obj->getColors() as $color)
        {
            $colors[] = max(0, min(255, $color + ($color * $adjust / 255)));
        }

        $obj = new GFNCore_Helper_Color($colors);
        return $obj->hex();
    }
}