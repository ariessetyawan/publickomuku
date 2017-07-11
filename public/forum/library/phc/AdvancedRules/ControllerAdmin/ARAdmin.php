<?php
// Team NullXF

class phc_AdvancedRules_ControllerAdmin_ARAdmin extends XenForo_ControllerAdmin_Abstract
{
    public function actionIndex()
    {
        $AR = $this->_getARModel()->fetchAllAR();

        $viewParams = array(
            'AR' => $AR
        );

        return $this->responseView('phc_AdvancedRules_ViewAdmin_ARAdmin', 'ar_list', $viewParams);
    }

    public function actionEdit()
    {
        $ar_id = $this->_input->filterSingle('ar_id', XenForo_Input::UINT);

        $AR = $this->_getARModel()->fetchARById($ar_id);

        if($ar_id && empty($AR))
        {
            return $this->responseError(new XenForo_Phrase('ar_rule_not_found'));
        }

        if($ar_id && !empty($AR))
        {
            $AR['node_ids'] = unserialize($AR['node_ids']);
            $AR['group_ids'] = unserialize($AR['group_ids']);
            $AR['actions'] = unserialize($AR['actions']);
        }
        else
        {
            $AR = array();
            $AR['node_ids'] = array();
            $AR['group_ids'] = array();
            $AR['actions'] = array();
        }

        // Nodes
        $nodeModel = XenForo_Model::create('XenForo_Model_Node');
        $nodes = $nodeModel->getNodeOptionsArray($nodeModel->getAllNodes());

        // UGroups
        $userGroupModel = XenForo_Model::create('XenForo_Model_UserGroup');
        $groups = $userGroupModel->getUserGroupOptions($AR['group_ids']);

        foreach($groups as $key => &$group)
        {
            if($group['value'] == 1)
                unset($groups[$key]);
        }

        // Check if Addon Installed
        $AddOnModel = XenForo_Model::create('XenForo_Model_AddOn');
        $ressource = ($AddOnModel->getAddOnById('XenResource') ? 0 : 1);
        $gallery = ($AddOnModel->getAddOnById('XenGallery') ? 0 : 1);

        $viewParams = array(
            'AR' => $AR,
            'nodes' => $nodes,
            'groups' => $groups,
            'ressource' => $ressource,
            'gallery' => $gallery,

        );

        return $this->responseView('phc_AdvancedRules_ViewAdmin_ARAdminEdit', 'ar_edit', $viewParams);
    }

    public function actionSave()
    {
        $ar_id = $this->_input->filterSingle('ar_id', XenForo_Input::UINT);

        $AR = $this->_getARModel()->fetchARById($ar_id);

        if($ar_id && empty($AR))
        {
            return $this->responseError(new XenForo_Phrase('ar_rule_not_found'));
        }

        $title = $this->_input->filterSingle('title', XenForo_Input::STRING);
        $text = $this->_input->filterSingle('text', XenForo_Input::STRING);
        $actions = $this->_input->filterSingle('actions', XenForo_Input::ARRAY_SIMPLE);
        $time = $this->_input->filterSingle('time', XenForo_Input::UINT);
        $groups = $this->_input->filterSingle('groups', XenForo_Input::ARRAY_SIMPLE);
        $nodes = $this->_input->filterSingle('nodes', XenForo_Input::ARRAY_SIMPLE);

        $dw = XenForo_DataWriter::create('phc_AdvancedRules_DataWriter_AR');

        if($ar_id)
        {
            $dw->setExistingData($ar_id);
        }

        $dw->set('title', $title);
        $dw->set('text', $text);
        $dw->set('time', $time);
        $dw->set('actions', serialize($actions));
        $dw->set('group_ids', serialize($groups));
        $dw->set('node_ids', serialize($nodes));

        $dw->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ar-list')
        );
    }

    public function actionDelete()
    {
        $ar_id = $this->_input->filterSingle('ar_id', XenForo_Input::UINT);

        $AR = $this->_getARModel()->fetchARById($ar_id);

        if($ar_id && empty($AR))
        {
            return $this->responseError(new XenForo_Phrase('ar_rule_not_found'));
        }

        $dw = XenForo_DataWriter::create('phc_AdvancedRules_DataWriter_AR');
        $dw->setExistingData($ar_id);

        if($this->isConfirmedPost())
        {
            $dw->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ar-list')
            );
        }
        else
        {
            $dw->preDelete();

            if($errors = $dw->getErrors())
            {
                return $this->responseError($errors);
            }

            $viewParams = array(
                'AR' => $AR
            );

            return $this->responseView('phc_AdvancedRules_ViewAdmin_ARAdminDelete', 'ar_delete', $viewParams);
        }
    }

    public function actionReset()
    {
        $ar_id = $this->_input->filterSingle('ar_id', XenForo_Input::UINT);

        if($this->isConfirmedPost())
        {
            $this->_getARModel()->resetData($ar_id);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ar-list')
            );
        }
        else
        {
            $AR = $this->_getARModel()->fetchARById($ar_id);

            if($ar_id && empty($AR))
            {
                return $this->responseError(new XenForo_Phrase('ar_rule_not_found'));
            }

            return $this->responseView('phc_AdvancedRules_ViewAdmin_ARAdminDelete', 'ar_reset', array('AR' => $AR));
        }
    }

    public function actionResetUser()
    {
        $userId = $this->_input->filterSingle('userId', XenForo_Input::UINT);

        $user = $this->_getUserModel()->getUserById($userId);

        if($user)
        {
            if($this->isConfirmedPost())
            {
                $this->_getARModel()->resetUserData($user['user_id']);

                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('members', $user)
                );
            }
            else
            {
                return $this->responseView('phc_AdvancedRules_ViewAdmin_ARAdminDelete', 'ar_reset_user', array('userId' => $userId));
            }
        }
        else
        {
            return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
        }
    }

    public function actionToggle()
    {
        return $this->_getToggleResponse($this->_getARModel()->fetchAllAR(), 'phc_AdvancedRules_DataWriter_AR', 'ar-list');
    }

    protected function _getARModel()
    {
        return $this->getModelFromCache('phc_AdvancedRules_Model_ARModel');
    }

    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }
}