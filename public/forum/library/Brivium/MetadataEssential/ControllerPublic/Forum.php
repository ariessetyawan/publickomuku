<?php

/**
 * Controller for handling actions on forums.
 *
 * @package XenForo_Forum
 */
class Brivium_MetadataEssential_ControllerPublic_Forum extends XFCP_Brivium_MetadataEssential_ControllerPublic_Forum
{
	public function actionForum()
	{
		$response = parent::actionForum();
		if(!empty($response->params)){
			$response->params = $this->setMetadataValue($response->params);
		}
		return $response;
	}
	public function actionIndex()
	{
		$response = parent::actionIndex();
		if(!method_exists('XenForo_ControllerPublic_Forum','actionForum')){
			if(!empty($response->params)){
				$response->params = $this->setMetadataValue($response->params);
			}
		}
		return $response;
	}
	
	
	public function setMetadataValue($params)
	{
		if(!empty($params['forum']['node_id'])){
			$forum = $params['forum'];
			$metaModel = $this->_getMetadataModel();
			$options = XenForo_Application::get('options');
			$metaOptions = $options->BRME_forumMetadata;
			
			$strReplace = $params['forum']['title'];

			if(!empty($metaOptions['titleFixed']))
			{
				$metaOptions['titleFixed'] = str_replace('{$forumTitle}', $strReplace, $metaOptions['titleFixed']);
			}
			
			$metaData  = $metaModel->getMetadata('forum',$forum['node_id']);
			if(isset($forum['description']) && (empty($metaData['description']) || (!empty($metaOptions['description']) && $metaOptions['description']=='content'))){
				if (!empty($options->BRME_defaultForumDes)) {
					$defaultDes = $options->BRME_defaultForumDes;
					$defaultDes = str_replace('{$forumTitle}', $strReplace,$defaultDes);
					$metaData['description'] = $defaultDes;
				}else{
					$metaData['description'] = $forum['description'];
				}
			}
			if(isset($forum['title']) && (empty($metaData['title']) || (!empty($metaOptions['title']) && $metaOptions['title']=='content'))){
				if (!empty($options->BRME_defaultForumTitle)){
					$defaultTitle = $options->BRME_defaultForumTitle;
					$defaultTitle = str_replace('{$forumTitle}', $strReplace,$defaultTitle);
					$metaData['title'] = $defaultTitle;
				}else{
					$metaData['title'] = XenForo_Template_Helper_Core::helperSnippet($forum['title'],300);
				}
			}

			$metaData = $metaModel->getRobotMetaData($metaData);
			$params['metaData'] = $GLOBALS['BRME_metadata'] = $metaData;
			$params['brmeOptions'] = $GLOBALS['brmeOptions'] = $metaOptions;
			
		}
		return $params;
	}
	
	
	
	public function actionCreateThread()
	{
		$response = parent::actionCreateThread();
		if(!empty($response->params['forum']['node_id'])){
			$nodeId = $response->params['forum']['node_id'];
			$metadataModel = $this->_getMetadataModel();
			$response->params['canChangeTitleMeta'] = $metadataModel->canChangeTitleMeta($nodeId);
			$response->params['canChangeDescriptionMeta'] = $metadataModel->canChangeDescriptionMeta($nodeId);
			$response->params['canChangeKeywordMeta'] = $metadataModel->canChangeKeywordMeta($nodeId);
			$response->params['canChangeRobotIndex'] = $metadataModel->canChangeRobotIndex($nodeId);
			$response->params['canChangeRobotFollow'] = $metadataModel->canChangeRobotFollow($nodeId);
		}
		return $response;
	}
	
	public function actionAddThread()
	{
		$GLOBALS['BRME_CPF_actionAddThread'] = $this;
		return parent::actionAddThread();
	}
	public function brmeActionSave(XenForo_DataWriter_Discussion_Thread $writer) 
	{
		$metaOptions = XenForo_Application::get('options')->BRME_threadMetadata;
		$metadataModel = $this->_getMetadataModel();
		$data = array();
		$firstMessageWriter = $writer->getFirstMessageDw();
		
		$thread = $writer->getMergedData();
		if(!empty($metaOptions['description']) && $metaOptions['description']=='user'){
			if(!empty($thread['node_id']) && $metadataModel->canChangeDescriptionMeta($thread['node_id'])){
				$data['description'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_description', XenForo_Input::STRING),300);
			}
		}else{
			$data['description'] = '';
		}
		if(!empty($metaOptions['title']) && $metaOptions['title']=='user'){
			if(!empty($thread['node_id']) && $metadataModel->canChangeTitleMeta($thread['node_id'])){
				$data['title'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_title', XenForo_Input::STRING),300);
			}
		}else{
			$data['title'] = '';
		}
		if(!empty($thread['node_id']) && $metadataModel->canChangeKeywordMeta($thread['node_id'])){
			$data['keywords'] = $this->_input->filterSingle('brme_meta_keywords', XenForo_Input::STRING);
		}
		
		if(!empty($thread['node_id']) && $metadataModel->canChangeRobotIndex($thread['node_id'])){
			$data['index'] = $this->_input->filterSingle('brme_index', XenForo_Input::UINT);
		}
		if(!empty($thread['node_id']) && $metadataModel->canChangeRobotFollow($thread['node_id'])){
			$data['follow'] = $this->_input->filterSingle('brme_follow', XenForo_Input::UINT);
		}
		
		$data['author'] = $thread['username']?$thread['username']:'';
		$data['content_type'] = 'thread';
		$data['content_id'] = $thread['thread_id'];
		
		$metadataModel->insertMetadata($data);
	}
	/**
	 * Load table model from cache.
	 *
	 * @return Brivium_MetadataEssential_Model_Metadata
	 */
	protected function _getMetadataModel()
	{
		return $this->getModelFromCache('Brivium_MetadataEssential_Model_Metadata');
	}
}