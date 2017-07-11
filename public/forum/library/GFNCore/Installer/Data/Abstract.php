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
abstract class GFNCore_Installer_Data_Abstract
{
    protected $_schema;

    protected $_db;

    public function __construct()
    {
        $this->_schema = new GFNCore_Db_Schema();
        $this->_db = XenForo_Application::getDb();
    }

    /**
     * @return GFNCore_Db_Schema_Table
     */
    public function table()
    {
        return $this->_schema->table();
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function db()
    {
        return $this->_db;
    }

    abstract public function install($isUpgrade = false);

    abstract public function uninstall();

    public function rebuild() { }
} 