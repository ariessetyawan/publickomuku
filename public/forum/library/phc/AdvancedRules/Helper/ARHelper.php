<?php
// Team NullXF

class phc_AdvancedRules_Helper_ARHelper
{
    public static function StartAR($action, $id = NULL)
    {
        $phcARCache = XenForo_Application::getSimpleCacheData('phcARCache');

        if(empty($phcARCache))
            return;

        $visitor = XenForo_Visitor::getInstance();
        $userModel = self::_getUserModel();
        $ARModel = self::_getARModel();

        $rules = array();

        if($visitor['user_id'] <= 0)
            return;


        foreach($phcARCache as $data)
        {
            if(!isset($data['actions'][$action]) || isset($data['actions'][$action]) && $data['actions'][$action] == 0)
                continue;

            if($userModel->isMemberOfUserGroup($visitor->toArray(), $data['group_ids']))
                continue;

            if($id)
            {
                if(in_array($id, $data['node_ids']))
                    continue;
            }

            $rules[$data['ar_id']] = $data;
        }

        if($rules)
        {
            // Check if user accept Rule
            $accepts = $ARModel->checkIfRuleAccept($visitor['user_id'], $rules);

            if($accepts)
            {
                foreach($accepts as $key)
                    unset($rules[$key]);
            }

            return array_keys($rules);
        }
    }

    protected static function _getARModel()
    {
        return XenForo_Model::create('phc_AdvancedRules_Model_ARModel');
    }

    protected static function _getUserModel()
    {
        return XenForo_Model::create('XenForo_Model_User');
    }
}