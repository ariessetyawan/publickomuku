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
abstract class GFNCore_Deferred_Abstract extends XenForo_Deferred_Abstract
{
    protected $_startTime;

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge($this->_getDefaultData(), $data);
        if (empty($data['action']))
        {
            return false;
        }

        $action = $data['action'];
        $action = str_replace('_', ' ', $action);
        $action = ucwords($action);
        $action = '_action' . str_replace(' ', '', $action);

        if (!is_callable(array($this, $action)))
        {
            return false;
        }

        $this->_startTime = microtime(true);

        if (!call_user_func_array(array($this, $action), array(&$data, $targetRunTime, &$status)))
        {
            return false;
        }

        return $data;
    }

    /**
     * @return array
     */
    abstract protected function _getDefaultData();

    protected function _execute($targetRunTime, callable $action)
    {
        do
        {
            if (call_user_func($action))
            {
                return true;
            }
        }
        while($targetRunTime - (microtime(true) - $this->_startTime) > 1);

        return false;
    }

    protected function _getStatus($actionPhrase, $typePhrase, &$step)
    {
        return sprintf('%s... %s %s', $actionPhrase, $typePhrase, str_repeat(' . ', ++$step));
    }
}