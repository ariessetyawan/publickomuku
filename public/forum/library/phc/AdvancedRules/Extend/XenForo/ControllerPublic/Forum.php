<?php
// Team NullXF

class phc_AdvancedRules_Extend_XenForo_ControllerPublic_Forum extends XFCP_phc_AdvancedRules_Extend_XenForo_ControllerPublic_Forum
{
	public function actionForum()
	{
        $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);

        if($forumId)
        {
            $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('forum_view', $forumId);

            if(is_array($arIds) && !empty($arIds))
            {
                if(empty($_SERVER['REQUEST_URI']))
                {
                    $redirect = 'fid=' . $forumId;
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
        return parent::actionForum();
	}

    public function actionCreateThread()
    {
        $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);

        if($forumId)
        {
            $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('thread_create', $forumId);

            if(is_array($arIds) && !empty($arIds))
            {
                if(empty($_SERVER['REQUEST_URI']))
                {
                    $redirect = 'fid=' . $forumId;
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
        return parent::actionCreateThread();
    }
}