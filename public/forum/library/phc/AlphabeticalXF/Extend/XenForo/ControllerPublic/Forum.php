<?php

class phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Forum extends XFCP_phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Forum
{
    public function actionForum()
    {
        $GLOBALS['alphaNation'] = array();
        $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
        $GLOBALS['alpha_order'] = $this->_input->filterSingle('order', XenForo_Input::STRING);
        $GLOBALS['alpha_direction'] = $this->_input->filterSingle('direction', XenForo_Input::STRING);

        $res = parent::actionForum();

        if(empty($res->params['forum']))
            return $res;

        $options = XenForo_Application::get('options');

        if($options->alphaxf_forum_thread_abc)
        {
            $go = false;
            $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
            $onlyNodes = XenForo_Application::get('options')->alphaxf_forum_thread_abc_only_nodes;

            if(isset($onlyNodes[0]) && $onlyNodes[0] == 0)
                $go = true;

            if(!$go && in_array($forumId, $onlyNodes))
                $go = true;


            if($go)
            {
                $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
                $langugageABC = $alphaHelper->alphaNation();

                $GLOBALS['alphaxfData'] = 'xfforum';
                $GLOBALS['alphaxf_float'] = false;
                $GLOBALS['alphaNation'] = $langugageABC;
            }
        }

        if($GLOBALS['alpha'])
        {
            $defaultOrder = $res->params['forum']['default_sort_order'];

            $res->params['forum']['default_sort_order'] = 'title';
            $res->params['forum']['default_sort_direction'] = 'desc';

            if(isset($res->params['orderParams'][$defaultOrder]))
            {
                unset($res->params['orderParams'][$defaultOrder]['direction']);
                $res->params['orderParams'][$defaultOrder]['order'] = $defaultOrder;
            }

            if(empty($GLOBALS['alpha_order']) && empty($GLOBALS['alpha_direction']) && $options->alphaxf_sort_results)
            {
                $res->params['order'] = 'title';
                $res->params['orderDirection'] = 'asc';
                $res->params['orderParams']['title']['direction'] = 'desc';
            }

            if(isset($res->params['orderParams']))
            {
                foreach($res->params['orderParams'] as $key => &$order)
                {
                    $order['alpha'] = $GLOBALS['alpha'];
                }
            }

        }

        if(!empty($GLOBALS['alphaxfData']))
        {
            $res->params['pageNavParams'][] = array('alpha' => $GLOBALS['alpha']);
        }

        return $res;
    }
}