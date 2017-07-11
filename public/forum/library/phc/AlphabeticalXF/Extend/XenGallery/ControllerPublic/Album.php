<?php

// Team NullXF
class phc_AlphabeticalXF_Extend_XenGallery_ControllerPublic_Album extends XFCP_phc_AlphabeticalXF_Extend_XenGallery_ControllerPublic_Album
{
    public function actionIndex()
    {
        $GLOBALS['alphaNation'] = array();
        if(XenForo_Application::get('options')->alphaxf_forum_thread_abc)
        {
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->alphaNation();

            $GLOBALS['alphaxfData'] = 'xfmg';
            $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
            $GLOBALS['alphaNation'] = $langugageABC;
        }

        return parent::actionIndex();
    }
}