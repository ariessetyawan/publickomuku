<?php
// Team NullXF

class phc_AdvancedRules_Extend_XenResource_ControllerPublic_Resource extends XFCP_phc_AdvancedRules_Extend_XenResource_ControllerPublic_Resource
{
	public function actionIndex()
	{
        $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('view_ressource');

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

        return parent::actionIndex();
	}

    public function actionAdd()
    {
        $categoryId = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);

        if($categoryId)
        {
            $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('add_ressource');

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

        return parent::actionAdd();
    }

    public function actionAddVersion()
    {
        $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('update_ressource');

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

        return parent::actionAddVersion();
    }
}