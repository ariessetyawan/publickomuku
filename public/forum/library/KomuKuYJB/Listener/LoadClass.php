<?php /*48159076764b57e3d038283fdd6f12bba6bce165*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 6
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Listener_LoadClass
{
    public static function extend($class, array &$extend)
    {
        $extend[] = 'KomuKuYJB_Extend_' . $class;
    }
}