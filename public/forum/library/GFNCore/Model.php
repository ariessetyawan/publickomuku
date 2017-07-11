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
abstract class GFNCore_Model extends XenForo_Model
{
    /**
     * @param string $class
     * @return static|XenForo_Model
     */
    public static function create($class = null)
    {
        if (!$class)
        {
            $class = get_called_class();
        }

        return parent::create($class);
    }
}