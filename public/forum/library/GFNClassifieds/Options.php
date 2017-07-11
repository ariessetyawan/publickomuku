<?php /*bbfae2d65ea1d6916974d508554daa1d98eb72f9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Options
{
    protected static $_instance;

    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new GFNCore_Helper_Options('gfnclassifieds');
        }

        return self::$_instance;
    }
}