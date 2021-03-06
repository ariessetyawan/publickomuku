<?php
/**
 * Copyright (c) 2016.
 */

class phc_AdvancedRules_Extend_XenGallery_ControllerPublic_Media extends XFCP_phc_AdvancedRules_Extend_XenGallery_ControllerPublic_Media
{
	public function actionIndex()
	{
        $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('view_gallery');

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
        $arIds = phc_AdvancedRules_Helper_ARHelper::StartAR('add_gallery');

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

        return parent::actionAdd();
    }
}