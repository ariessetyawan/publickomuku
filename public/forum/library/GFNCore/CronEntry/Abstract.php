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
abstract class GFNCore_CronEntry_Abstract
{
    protected $_entryId;
    protected $_nextRun;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected static $_db;

    protected static $_modelCache = array();

    final public static function run(array $entry)
    {
        $obj = new static($entry['entry_id'], $entry['next_run']);
        $obj->_run();
    }

    final protected function __construct($entryId, $nextRun)
    {
        $this->_entryId = $entryId;
        $this->_nextRun = $nextRun;
    }

    abstract protected function _run();

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDb()
    {
        if (!static::$_db)
        {
            static::$_db = XenForo_Application::getDb();
        }

        return static::$_db;
    }

    /**
     * @param $class
     * @return XenForo_Model
     */
    protected function _getModelFromCache($class)
    {
        if (!isset(static::$_modelCache[$class]))
        {
            static::$_modelCache[$class] = XenForo_Model::create($class);
        }

        return static::$_modelCache[$class];
    }
}