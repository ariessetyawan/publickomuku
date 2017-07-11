<?php

class phc_AlphabeticalXF_Extend_XenResource_ControllerPublic_Resource extends XFCP_phc_AlphabeticalXF_Extend_XenResource_ControllerPublic_Resource
{
    public function actionIndex()
    {
        $GLOBALS['alphaNation'] = array();
        if(XenForo_Application::get('options')->alphaxf_rm_index_abc)
        {
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->alphaNation();

            $GLOBALS['alphaxfData'] = 'xfresource';
            $GLOBALS['alphaxf_float'] = true;
            $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
            $GLOBALS['alphaNation'] = $langugageABC;
        }

        $res = parent::actionIndex();

        if(!empty($GLOBALS['alphaxfData']))
        {
            $res->params['pageNavParams'][] = array('alpha' => $GLOBALS['alpha']);
        }

        return $res;
    }

    public function actionCategory()
    {
        $GLOBALS['alphaNation'] = array();
        if(XenForo_Application::get('options')->alphaxf_rm_category_abc)
        {
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->alphaNation();

            $GLOBALS['alphaxfData'] = 'xfresource_category';
            $GLOBALS['alphaxf_float'] = true;
            $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
            $GLOBALS['alphaNation'] = $langugageABC;
        }

        $res = parent::actionCategory();

        if(!empty($GLOBALS['alphaxfData']))
        {
            $res->params['pageNavParams'][] = array('alpha' => $GLOBALS['alpha']);
        }

        return $res;
    }
}