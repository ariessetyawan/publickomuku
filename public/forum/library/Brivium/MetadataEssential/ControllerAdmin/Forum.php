<?php

class Brivium_MetadataEssential_ControllerAdmin_Forum extends XFCP_Brivium_MetadataEssential_ControllerAdmin_Forum
{
	public function actionEdit()
	{
		$response = parent::actionEdit();
		if(!empty($response->params['forum']['node_id'])){
			$response->params['forum']['metaData'] = $this->getModelFromCache('Brivium_MetadataEssential_Model_Metadata')->getMetadata('forum',$response->params['forum']['node_id']);
		}
		return $response;
	}

	public function actionSave()
	{		
		$GLOBALS['BRME_CAF_actionSave'] = $this;
		return parent::actionSave();
	}
	public function brmeActionSave(XenForo_DataWriter_Forum $writer)
	{		
		$forum = $writer->getMergedData();
		if(!empty($forum['node_id'])){
			$metaOptions = XenForo_Application::get('options')->BRME_forumMetadata;
			$metadataModel = $this->_getMetadataModel();
			$data = array();
			
			if(!empty($metaOptions['description']) && $metaOptions['description']=='user'){
				$data['description'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_description', XenForo_Input::STRING),300);
			}else{
				$data['description'] = XenForo_Template_Helper_Core::helperSnippet($writer->get('description'),300);
			}
			if(!empty($metaOptions['title']) && $metaOptions['title']=='user'){
				$data['title'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_title', XenForo_Input::STRING),300);
			}else{
				$data['title'] = XenForo_Template_Helper_Core::helperSnippet($writer->get('title'),300);
			}
			$data['keywords'] = $this->_input->filterSingle('brme_meta_keywords', XenForo_Input::STRING);
			$data['index'] = $this->_input->filterSingle('brme_index', XenForo_Input::UINT);
			$data['follow'] = $this->_input->filterSingle('brme_follow', XenForo_Input::UINT);
			$data['use_child'] = $this->_input->filterSingle('brme_use_child', XenForo_Input::UINT);
			
			$forum = $writer->getMergedData();
			
			//$data['author'] = $forum['username']?$forum['username']:'';
			$data['content_type'] = 'forum';
			$data['content_id'] = $forum['node_id'];
			$metadataModel->updateMetadata($data,'forum',$forum['node_id']);
		}
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
	