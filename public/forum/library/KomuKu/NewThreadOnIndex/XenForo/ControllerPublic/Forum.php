<?php
 /*************************************************************************
 * XenForo New Thread On Index - Xen Factory (c) 2015
 * All Rights Reserved.
 * Created by Clement Letonnelier aka. MtoR
 **************************************************************************
 * This file is subject to the terms and conditions defined in the Licence
 * Agreement available at http://xen-factory.com/pages/license-agreement/.
  *************************************************************************/

class KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Forum extends XFCP_KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Forum
{
	public function actionIndex()
	{
    	$response = parent::actionIndex();
    	
    	if (isset($response->params['nodeList']))
    	{
            $forums = $this->_getForumModel()->getForums();

        	foreach($forums AS $idx => $forum)
        	{
            	if (!$this->_getForumModel()->canPostThreadInForum($forum))
            	{
                    unset($forums[$idx]);	
                }            	
        	}
        	
        	if (count($forums))
        	{
            	$response->params['KomuKu_ntoi_canPostThreads'] = true;
        	}
        	else
        	{
            	$response->params['KomuKu_ntoi_canPostThreads'] = false;
        	}
    	}
    	    	
    	return $response;
    }
}