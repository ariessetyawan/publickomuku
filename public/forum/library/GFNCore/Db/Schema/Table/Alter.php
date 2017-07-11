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
class GFNCore_Db_Schema_Table_Alter extends GFNCore_Db_Schema_Table_Abstract
{
    public $dropPrimary = false;

    public $dropIndex = array();

    public $drop = array();

    public $modify = array();

    public $rename;

    public function rename($name)
    {
        $this->rename = $name;
    }

    public function drop($name)
    {
        $this->drop[$name] = true;
    }

    public function modify(GFNCore_Db_Schema_Column $column, $oldName = null)
    {
        unset($this->columns[$column->name]);
        $this->modify[($oldName ? $oldName : $column->name)] = $column;
        return $column;
    }

    public function dropPrimaryKey()
    {
        $this->dropPrimary = true;
    }

    public function dropIndex($name)
    {
        $this->dropIndex[$name] = true;
    }

    public function parse()
    {
        return new GFNCore_Db_Schema_Grammar_Table_Alter($this);
    }
} 