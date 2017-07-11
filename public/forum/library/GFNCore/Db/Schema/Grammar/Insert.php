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
class GFNCore_Db_Schema_Grammar_Insert extends GFNCore_Db_Schema_Grammar_Abstract
{
    protected $_insert;

    protected $_maxLength = 1048576;

    public function __construct(GFNCore_Db_Schema_Insert $insert)
    {
        $this->_insert = $insert;

        $stmt = $this->db()->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
        if ($stmt instanceof Zend_Db_Statement_Interface && $stmt->columnCount())
        {
            $this->_maxLength = $stmt->fetchColumn(1);
        }
    }

    protected function _prepareRows(array $rows)
    {
        $return = array();
        $columns = array();

        foreach ($rows as $row)
        {
            if (count($row) > count($columns))
            {
                $columns = array_keys($row);
            }
        }

        foreach ($rows as $i => $row)
        {
            foreach ($columns as $column)
            {
                $return[$i][$column] = array_key_exists($column, $row) ? $row[$column] : '';
            }
        }

        return array($columns, $return);
    }

    public function parse()
    {
        list ($columns, $rows) = $this->_prepareRows($this->_insert->rows);
        $table = $this->_insert->table;
        $sql = array();

        if (empty($rows))
        {
            return array();
        }

        $header = 'INSERT ' . ($this->_insert->ignore ? 'IGNORE ' : '') . "INTO `{$table}`\n\t(`" . implode('`, `', $columns) . "`)\nVALUES\n";
        $maxLength = $this->_maxLength - (strlen($header) + 200);
        $length = 0;
        $i = 0;

        foreach ($rows as $row)
        {
            $output = $this->_parseValues($row);
            $length += strlen($output);

            if ($length > $maxLength)
            {
                $i++;
                $length = 0;
            }

            $sql[$i][] = $output;
        }

        $return = array();
        foreach ($sql as $queries)
        {
            $return[] = $header . implode(",\n", $queries);
        }

        return $return;
    }

    public function __toString()
    {
        return implode(";\n", $this->parse());
    }

    protected function _parseValues(array $row)
    {
        $return = array();

        foreach ($row as $value)
        {
            if (is_null($value))
            {
                $return[] = 'NULL';
            }
            else
            {
                $return[] = $this->quote($value);
            }
        }

        return "\t(" . implode(', ', $return) . ')';
    }
}