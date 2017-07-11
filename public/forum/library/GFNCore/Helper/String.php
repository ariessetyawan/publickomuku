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
class GFNCore_Helper_String
{
    public static function replaceFirst($search, $replace, $string)
    {
        $pos = strpos($string, $search);
        if ($pos === false)
        {
            return $string;
        }

        return substr_replace($string, $replace, $pos, strlen($search));
    }

    public static function detectEndOfLine($string)
    {
        if (strpos($string, "\r\n") !== false)
        {
            return "\r\n";
        }
        elseif (strpos($string, "\r") !== false)
        {
            return "\r";
        }
        elseif (strpos($string, "\n") !== false)
        {
            return "\n";
        }
        else
        {
            // either does not exist or the string is fucked up...
            return null;
        }
    }
} 