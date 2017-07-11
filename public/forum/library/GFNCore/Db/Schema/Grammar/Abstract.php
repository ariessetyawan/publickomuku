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
abstract class GFNCore_Db_Schema_Grammar_Abstract extends GFNCore_Db_Schema_Abstract
{
    public function quote($input)
    {
        if (is_array($input))
        {
            foreach ($input as &$val)
            {
                $val = $this->quote($val);
            }

            return implode(', ', $input);
        }

        if (is_int($input))
        {
            return $input;
        }

        if (is_float($input))
        {
            return sprintf('%F', $input);
        }

        return "'" . addcslashes($input, "\000\n\r\\'\"\032") . "'";
    }
} 