<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 *
 * @method $this length(int $length)
 * @method $this default($value)
 * @method $this nullable(boolean $nullable)
 * @method $this comments(string $comments)
 * @method $this allowed(array $allowed)
 * @method $this places(int $places)
 * @method $this unsigned(boolean $unsigned)
 * @method $this autoIncrement(boolean $autoIncrement)
 * @method $this after(string $column)
 */
class GFNCore_Db_Schema_Column extends GFNCore_Db_Schema_Abstract
{
    public $name;
    public $dataType;
    public $length;
    public $default = null;
    public $nullable = false;
    public $comments = '';

    public $allowed;
    public $places;
    public $unsigned = false;
    public $autoIncrement = false;
    public $after;

    public function parse()
    {
        return new GFNCore_Db_Schema_Grammar_Column($this);
    }

    public function __call($method, array $parameters)
    {
        if (property_exists($this, $method))
        {
            $this->{$method} = reset($parameters);
        }

        return $this;
    }
} 