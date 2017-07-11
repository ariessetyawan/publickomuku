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
class GFNCore_Helper_Color
{
    protected $_red = null;

    protected $_green = null;

    protected $_blue = null;

    protected $_alpha = 1;

    public function __construct($color)
    {
        if (is_array($color))
        {
            if (count($color) < 3)
            {
                throw new GFNCore_Exception('Invalid parameters specified.');
            }

            if (isset($color['red'])) { $this->_red = intval($color['red']); } else
            if (isset($color['r'])) { $this->_red = intval($color['r']); } else
            if (isset($color[0])) { $this->_red = intval($color[0]); }

            if (isset($color['green'])) { $this->_green = intval($color['green']); } else
            if (isset($color['g'])) { $this->_green = intval($color['g']); } else
            if (isset($color[1])) { $this->_green = intval($color[1]); }

            if (isset($color['blue'])) { $this->_blue = intval($color['blue']); } else
            if (isset($color['b'])) { $this->_blue = intval($color['b']); } else
            if (isset($color[2])) { $this->_blue = intval($color[2]); }

            if (isset($color['alpha'])) { $this->_alpha = intval($color['alpha']); } else
            if (isset($color['a'])) { $this->_alpha = intval($color['a']); } else
            if (isset($color[3])) { $this->_alpha = intval($color[3]); }
        }
        elseif (is_string($color))
        {
            if ($color{0} == '#')
            {
                $color = substr($color, 1);

                if (strlen($color) == 3)
                {
                    $color = str_repeat(substr($color, 0, 1), 2) . str_repeat(substr($color, 1, 1), 2) . str_repeat(substr($color, 2, 1), 2);
                }

                $color = str_split($color, 2);
                if (count($color) != 3)
                {
                    throw new GFNCore_Exception('Color is not a valid hexadecimal string.');
                }

                $this->_red = hexdec($color[0]);
                $this->_green = hexdec($color[1]);
                $this->_blue = hexdec($color[2]);
            }
            elseif (substr($color, 0, 3) == 'rgb')
            {
                preg_match_all('/([0-9.]+)/', $color, $matches);

                if (empty($matches[0]))
                {
                    throw new GFNCore_Exception('Color is not a valid rgb(a) string.');
                }

                $color = $matches[0];
                if (count($color) < 3)
                {
                    throw new GFNCore_Exception('Color is not a valid rgb(a) string.');
                }

                $this->_red = intval($color[0]);
                $this->_green = intval($color[1]);
                $this->_blue = intval($color[2]);

                if (isset($color[3]))
                {
                    $this->_alpha = intval($color[3]);
                }
            }
            else
            {
                throw new GFNCore_Exception('Invalid parameters specified.');
            }
        }
        else
        {
            throw new GFNCore_Exception('Invalid parameters specified.');
        }

        if ($this->_red === null || $this->_green === null || $this->_blue === null)
        {
            throw new GFNCore_Exception('Invalid parameters specified.');
        }
    }

    public function luminance()
    {
        return sqrt(0.299 * pow($this->_red, 2) + 0.587 * pow($this->_green, 2) + 0.114 * pow($this->_blue, 2));
    }

    public function isBright()
    {
        return (1 - ($this->luminance() / 255) < 0.5);
    }

    public function __toString()
    {
        if ($this->_alpha == 1)
        {
            return $this->hex();
        }

        return $this->rgb();
    }

    public function hex()
    {
        return sprintf('#%02s%02s%02s', dechex($this->_red), dechex($this->_green), dechex($this->_blue));
    }

    public function rgb()
    {
        if ($this->_alpha < 1)
        {
            return sprintf('rgba(%u, %u, %u, %.2f)', $this->_red, $this->_green, $this->_blue, $this->_alpha);
        }

        return sprintf('rgb(%u, %u, %u)', $this->_red, $this->_green, $this->_blue);
    }

    public function getColors()
    {
        return array($this->_red, $this->_green, $this->_blue);
    }

    public function red()
    {
        return $this->_red;
    }

    public function green()
    {
        return $this->_green;
    }

    public function blue()
    {
        return $this->_blue;
    }

    public function alpha()
    {
        return $this->_alpha;
    }
}