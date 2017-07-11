<?php

/**
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Extends_ControllerAdmin_User extends XFCP_KomuKu_ThreadCount_Extends_ControllerAdmin_User
{

	public function actionSave()
	{
        
 	    $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
 	    
	    if ($userId)
	    {
	        $threadCount = $this->_input->filterSingle('thread_count', XenForo_Input::UINT);
	        
	        $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
	        
	        $dw->setExistingData($userId);

	        $dw->setOption(XenForo_DataWriter_User::OPTION_ADMIN_EDIT, true);
	        
	        $dw->set('thread_count',$threadCount);
	        $dw->save();
	        
	        
	    }
	    
	    return parent::actionSave();
	       
	}
	
	
}
?>