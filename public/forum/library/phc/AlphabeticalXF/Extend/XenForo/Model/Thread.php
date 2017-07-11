<?php

class phc_AlphabeticalXF_Extend_XenForo_Model_Thread extends XFCP_phc_AlphabeticalXF_Extend_XenForo_Model_Thread
{
    public function prepareThreadConditions(array $conditions, array &$fetchOptions)
    {
        $res = parent::prepareThreadConditions($conditions, $fetchOptions);

        $res = $this->_getAlphaModel()->getAlphaStatement('thread.title', $res);

        return $res;
    }

    public function prepareThreadFetchOptions(array $fetchOptions)
    {
        if(!empty($GLOBALS['alpha']) && XenForo_Application::get('options')->alphaxf_sort_results)
        {
            if(empty($GLOBALS['alpha_order']) && empty($GLOBALS['alpha_direction']))
            {
                $fetchOptions['order'] = 'title';
                $fetchOptions['orderDirection'] = 'asc';
            }
        }

        return parent::prepareThreadFetchOptions($fetchOptions);
    }

    protected function _getAlphaModel()
    {
        return $this->getModelFromCache('phc_AlphabeticalXF_Model_Alpha');
    }
}
