<?php

/**
 * Controller for resource categories
 */
class Brivium_MetadataEssential_ControllerAdmin_Category extends XFCP_Brivium_MetadataEssential_ControllerAdmin_Category
{
	/**
	 * Gets the category add/edit form response.
	 *
	 * @param array $category
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	protected function _getCategoryAddEditResponse(array $category)
	{
		if(!empty($category['resource_category_id'])){
			$category['metaData'] = $this->getModelFromCache('Brivium_MetadataEssential_Model_Metadata')->getMetadata('resource_category',$category['resource_category_id']);
		}
		return parent::_getCategoryAddEditResponse($category);
	}
	public function actionSave()
	{		
		$GLOBALS['BRME_CARC_actionSave'] = $this;
		return parent::actionSave();
	}
	public function brmeActionSave(XenResource_DataWriter_Category $writer)
	{		
		$category = $writer->getMergedData();
		if(!empty($category['resource_category_id'])){
			$metaOptions = XenForo_Application::get('options')->BRME_resourceCategory;
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
			
			$category = $writer->getMergedData();
			
			//$data['author'] = $category['username']?$category['username']:'';
			$data['content_type'] = 'resource_category';
			$data['content_id'] = $category['resource_category_id'];
			$metadataModel->updateMetadata($data, $data['content_type'], $data['content_id']);
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