<?php

class phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Member extends XFCP_phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Member
{
    public function actionIndex()
    {
        $GLOBALS['alphaNation'] = array();
        if(XenForo_Application::get('options')->alphaxf_forum_member_abc)
        {
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->alphaNation();

            $GLOBALS['alphaxfData'] = 'xfmember';
            $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
            $GLOBALS['alphaNation'] = $langugageABC;
        }

        $res = parent::actionIndex();

        if(!empty($GLOBALS['alphaxfData']))
        {
            $res->params['alpha'] = array('alpha' => $GLOBALS['alpha']);
        }

        return $res;
    }
}