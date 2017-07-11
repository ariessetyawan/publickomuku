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
class GFNCore_Db_Schema_Insert extends GFNCore_Db_Schema_Abstract
{
    public $table;
    public $ignore;

    public $rows = array();

    public function __construct($table, $ignore = false)
    {
        $this->table = $table;
        $this->ignore = $ignore;
    }

    public function row(array $row)
    {
        $this->rows[] = $row;
    }

    public function parse()
    {
        return new GFNCore_Db_Schema_Grammar_Insert($this);
    }

    public function execute()
    {
        foreach ($this->parse()->parse() as $sql)
        {
            $this->db()->query((string) $sql);
        }
    }
}