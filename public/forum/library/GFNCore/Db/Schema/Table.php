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
class GFNCore_Db_Schema_Table
{
    public function create($name, $callback)
    {
        $table = new GFNCore_Db_Schema_Table_Create();
        $table->name = $name;
        call_user_func($callback, $table);
        $table->execute();
    }

    public function alter($name, $callback)
    {
        $table = new GFNCore_Db_Schema_Table_Alter();
        $table->name = $name;
        call_user_func($callback, $table);
        $table->execute();
    }

    public function drop($name)
    {
        $table = new GFNCore_Db_Schema_Table_Drop();
        $table->name = $name;
        $table->execute();
    }

    public function truncate($name)
    {
        $table = new GFNCore_Db_Schema_Table_Truncate();
        $table->name = $name;
        $table->execute();
    }

    public function rename($from, $to)
    {
        $table = new GFNCore_Db_Schema_Table_Rename();
        $table->name = $from;
        $table->newName = $to;
        $table->execute();
    }
} 