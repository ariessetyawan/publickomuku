<?php

class phc_AlphabeticalXF_Extend_XenForo_Model_User extends XFCP_phc_AlphabeticalXF_Extend_XenForo_Model_User
{
    public function prepareUserConditions(array $conditions, array &$fetchOptions)
    {
        $res = parent::prepareUserConditions($conditions, $fetchOptions);

        $res = $this->_getAlphaModel()->getAlphaStatement('user.username', $res);

        return $res;
    }

    public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        if(!empty($GLOBALS['alpha']) && XenForo_Application::get('options')->alphaxf_sort_results)
        {
            $fetchOptions['order'] = 'username';
            $fetchOptions['orderDirection'] = 'asc';
            $fetchOptions['direction'] = 'asc';

            $choices = array(
                'username' => 'LOWER(user.username)',
                'register_date' => 'user.register_date',
                'message_count' => 'user.message_count',
                'trophy_points' => 'user.trophy_points',
                'like_count' => 'user.like_count',
                'last_activity' => 'user.last_activity'
            );

            return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
        }

        return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
    }
    
    protected function _getAlphaModel()
    {
        return $this->getModelFromCache('phc_AlphabeticalXF_Model_Alpha');
    }
}