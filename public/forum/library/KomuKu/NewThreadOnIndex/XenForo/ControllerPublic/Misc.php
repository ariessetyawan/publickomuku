<?php
 /*************************************************************************
 * XenForo New Thread On Index - Xen Factory (c) 2015
 * All Rights Reserved.
 * Created by Clement Letonnelier aka. MtoR
 **************************************************************************
 * This file is subject to the terms and conditions defined in the Licence
 * Agreement available at http://xen-factory.com/pages/license-agreement/.
  *************************************************************************/

class KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Misc extends XFCP_KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Misc
{
	public function actionKomuKuCreateThread()
	{
		if ($this->isConfirmedPost())
		{
		    $forumId = $this->_input->filterSingle('forum_id', XenForo_Input::UINT);
		    
            $forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($forumId);
		    
		    if (!$forum)
		    {
    			return $this->responseNoPermission();
		    }
    		
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('forums/create-thread', $forum)
            );
        }
        else
        {
    		$nodeModel = $this->getModelFromCache('XenForo_Model_Node');
    
    		$nodes = $nodeModel->getViewableNodeList(null, true);
    		$nodeTypes = $nodeModel->getAllNodeTypes();
    
    		$nodes = $nodeModel->filterOrphanNodes($nodes);
                        
        	if (!count($nodes))
        	{
    			return $this->responseNoPermission();
        	}
        		
    		$viewParams = array(
    			'forums' => $nodes
    		);
    
    		return $this->responseView('KomuKu_NewThreadOnIndex_ViewPublic_NewThreadList', 'KomuKu_ntoi_list', $viewParams);
        }
    }
}