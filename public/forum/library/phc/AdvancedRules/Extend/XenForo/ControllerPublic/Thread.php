<?php
// Team NullXF

class phc_AdvancedRules_Extend_XenForo_ControllerPublic_Thread extends XFCP_phc_AdvancedRules_Extend_XenForo_ControllerPublic_Thread
{
	public function actionIndex()
	{
        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $thread = $this->_getThreadModel()->getThreadById($threadId);

        if($thread)
        {
            $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('thread_view', $thread['node_id']);

            if(is_array($arIds) && !empty($arIds))
            {
                if(empty($_SERVER['REQUEST_URI']))
                {
                    $redirect = 'fid=' . $threadId;
                }
                else
                {
                    $redirect = $_SERVER['REQUEST_URI'];
                }

                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                    XenForo_Link::buildPublicLink(
                        'rule', '',
                        array('ruleIds' => implode(',', $arIds), 'redirect' => $redirect))
                );
            }
        }

        return parent::actionIndex();
	}

    protected function _getThreadModel()
    {
        return $this->getModelFromCache('XenForo_Model_Thread');
    }
}