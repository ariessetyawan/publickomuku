<?php

class phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Conversation extends XFCP_phc_AlphabeticalXF_Extend_XenForo_ControllerPublic_Conversation
{
    protected function _getConversationListData(array $extraConditions = array())
    {
        $GLOBALS['alphaNation'] = array();
        if(XenForo_Application::get('options')->alphaxf_forum_conversation_abc)
        {
            $alphaHelper = new phc_AlphabeticalXF_Helper_Helper();
            $langugageABC = $alphaHelper->alphaNation();

            $GLOBALS['alphaxfData'] = 'xfconversation';
            $GLOBALS['alpha'] = $this->_input->filterSingle('alpha', XenForo_Input::STRING);
            $GLOBALS['alphaNation'] = $langugageABC;
        }
        
        $res = parent::_getConversationListData($extraConditions);

        if(!empty($GLOBALS['alphaxfData']))
        {
            $res['pageNavParams'][] = array('alpha' => $GLOBALS['alpha']);
        }

        return $res;
    }
}