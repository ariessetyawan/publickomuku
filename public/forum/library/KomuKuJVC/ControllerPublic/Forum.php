<?php

// overide the default forum when its a directory forum, so the links can point to the directroy / review pages
class KomuKuJVC_ControllerPublic_Forum extends XFCP_KomuKuJVC_ControllerPublic_Forum
{
	
	// redirect the create thread
	public function actionCreateThread()
	{
		$response = parent::actionCreateThread();
		$directoryForums =  XenForo_Application::get('options')->directoryForum;
		
		if ($response instanceof XenForo_ControllerResponse_View && in_array($response->params['forum']['node_id'], $directoryForums))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('full:directory/create-listing'));
		}

		return $response;
	}
	
	
	
	// could be issues if other plug-ins extend this, will make call to parent if not directory forum
	
	/**
	 * Displays the contents of a forum.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIndex()
	{
		
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		if ($forumId || $forumName)
		{
			return $this->actionForum();
		}
		else
		{
			return parent::actionIndex();
		}

	}

	
	
	public function actionForum()
	{
		
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		$responseView = parent::actionForum();

		$options = XenForo_Application::get('options');
		$directoryForums = $options->directoryForum;
		$directoryForum = $directoryForums[0];

		if (isset($responseView->params['forum']['node_id'])) {
			
			
			if($responseView->params['forum']['node_id'] == $directoryForum){
			
				// make sure we have params before trying to use them
				if(isset($responseView->params) && array_key_exists('threads', $responseView->params)){
					
					$params = $responseView->params;
							
					// I need to build the links up for read/unread 
					// to params build the url FOR EACH thread in threads using
					// params['threads']['thread_id'] and params['threads']['title']		
					foreach($params['threads'] AS $thread)
					{
						$thisURL = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'], $thread['title'], true);
						$params['threads'][$thread['thread_id']]['url'] =  $thisURL;
					}
				}			
				
				return $this->responseView('XenForo_ViewPublic_Forum_View', 'sfdir_forum_view', $params);
				}
		}
		
		

		return $responseView;		
		
		
				
	}	
	
	
}