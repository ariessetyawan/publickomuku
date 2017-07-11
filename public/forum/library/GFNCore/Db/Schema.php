<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 * @include    ./
 */
class GFNCore_Db_Schema
{
    protected $_table;

    public function table()
    {
        if (!$this->_table)
        {
            $this->_table = new GFNCore_Db_Schema_Table();
        }

        return $this->_table;
    }

    protected $_db;

    public function db()
    {
        if (!$this->_db)
        {
            $this->_db = XenForo_Application::getDb();
        }

        return $this->_db;
    }
} 