<?php

/**
 * Controller for handling actions on threads.
 *
 * @package XenForo_Thread
 */
class Brivium_MetadataEssential_ControllerPublic_Thread extends XFCP_Brivium_MetadataEssential_ControllerPublic_Thread
{
	/**
	 * Displays a form to edit a thread.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEdit()
	{
		$response = parent::actionEdit();
		if(!empty($response->params['thread']['thread_id'])){
			$metadataModel = $this->_getMetadataModel();
			$response->params['thread']['metaData'] = $metadataModel->getMetadata('thread',$response->params['thread']['thread_id']);
			
			$response->params['canChangeTitleMeta'] = $metadataModel->canChangeTitleMeta($response->params['thread']['node_id']);
			$response->params['canChangeDescriptionMeta'] = $metadataModel->canChangeDescriptionMeta($response->params['thread']['node_id']);
			$response->params['canChangeKeywordMeta'] = $metadataModel->canChangeKeywordMeta($response->params['thread']['node_id']);
			$response->params['canChangeRobotIndex'] = $metadataModel->canChangeRobotIndex($response->params['thread']['node_id']);
			$response->params['canChangeRobotFollow'] = $metadataModel->canChangeRobotFollow($response->params['thread']['node_id']);
		}
		return $response;
	}
	/**
	 * Updates an existing thread.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSave()
	{
		$GLOBALS['BRME_CPF_actionAddThread'] = $this;
		return parent::actionSave();
	}
	public function brmeActionSave(XenForo_DataWriter_Discussion_Thread $writer) {
		$metaOptions = $this->_getMetadataOptions();
		$metadataModel = $this->_getMetadataModel();
		$data = array();
		$thread = $writer->getMergedData();
		$firstMessageWriter = $writer->getFirstMessageDw();
		
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
		$metadataModel->updateMetadata($data,$data['content_type'],$data['content_id']);
	}
	
	protected function _getDefaultViewParams(array $forum, array $thread, array $posts, $page = 1, array $viewParams = array())
	{
		$viewParams = parent::_getDefaultViewParams($forum, $thread, $posts, $page, $viewParams);
		if(!empty($thread['thread_id'])){
			$viewParams = $this->setMetadataValue($viewParams, $thread);
		}
		return $viewParams;
	}
	
	public function setMetadataValue($viewParams, $thread)
	{
		if(!empty($thread['thread_id']) && !empty($thread['node_id']) ){
			$metaModel = $this->_getMetadataModel();
			$options = XenForo_Application::get('options');
			$metaOptions = $options->BRME_threadMetadata;

			$node = $this->getModelFromCache('XenForo_Model_Node')->getNodeById($thread['node_id']);
			$strReplace = array(
				XenForo_Template_Helper_Core::helperSnippet($thread['title'],300),
				$node['title'],
				$thread['username']
			);
			
			if(!empty($metaOptions['titleFixed']))
			{
				$metaOptions['titleFixed'] = str_replace(array('{$title}', '{$forumTitle}', '{$author}'), $strReplace, $metaOptions['titleFixed']);
			}
			
			$metaData  = $metaModel->getMetadata('thread',$thread['thread_id']);
			if(isset($viewParams['firstPost']['message']) && (empty($metaData['description']) || (!empty($metaOptions['title']) && $metaOptions['description']=='content'))){
				if (!empty($options->BRME_defaultThreadDes)) {
					$defaultDes = $options->BRME_defaultThreadDes;
					$defaultDes = str_replace(array('{$title}', '{$forumTitle}', '{$author}'),$strReplace,$defaultDes);
					$metaData['description'] = $defaultDes;
				}else{
					$metaData['description'] = XenForo_Template_Helper_Core::helperSnippet($viewParams['firstPost']['message'],300);
				}
			}
			if(isset($thread['title']) && (empty($metaData['title']) || (!empty($metaOptions['title']) && $metaOptions['title']=='content'))){
				if (!empty($options->BRME_defaultThreadTitle)){
					$defaultTitle = $options->BRME_defaultThreadTitle;
					$defaultTitle = str_replace(array('{$title}', '{$forumTitle}', '{$author}'),$strReplace,$defaultTitle);
					$metaData['title'] = $defaultTitle;
				}else{
					$metaData['title'] = XenForo_Template_Helper_Core::helperSnippet($thread['title'],300);
				}
			}
			$xenAddons = XenForo_Application::get('addOns');
			if(!empty($metaOptions['keywords'])&& $metaOptions['keywords']=='xen_tag' &&!empty($xenAddons['Tinhte_XenTag'])){
				$metaData['keywords'] = Tinhte_XenTag_Helper::getImplodedTagsFromThread($thread);
			}
			if (!empty($options->BRME_defaultThreadKeywords) && empty($metaData['keywords']) && !empty($metaOptions['keywords'])) {
					$defaultThreadKeywords = $options->BRME_defaultThreadKeywords;
					$defaultThreadKeywords = str_replace(array('{$title}', '{$forumTitle}', '{$author}'),$strReplace,$defaultThreadKeywords);
					$metaData['keywords'] = $defaultThreadKeywords;
			}

			$forumMetaData  = $metaModel->getMetadata('forum',$thread['node_id']);
			if(!empty($forumMetaData['use_child'])){
				$robotMetaData = $metaModel->getRobotMetaData($forumMetaData);
			}else{
				$robotMetaData = $metaModel->getRobotMetaData($metaData);
			}
			if(!empty($robotMetaData['index'])){
				$metaData['index'] = $robotMetaData['index'];
			}
			if(!empty($robotMetaData['follow'])){
				$metaData['follow'] = $robotMetaData['follow'];
			}
			if(!empty($robotMetaData['robot'])){
				$metaData['robot'] = $robotMetaData['robot'];
			}

			if (empty($metaData['image'])) {
				$metaData['image'] = $options->BRME_defaultPostImageAltTag;
			}

			$viewParams['metaData'] = $GLOBALS['BRME_metadata'] = $metaData;
			$viewParams['brmeOptions'] = $GLOBALS['brmeOptions'] = $metaOptions;
			
		}
		return $viewParams;
	}
	
	protected function _getMetadataOptions()
	{
		return XenForo_Application::get('options')->BRME_threadMetadata;
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