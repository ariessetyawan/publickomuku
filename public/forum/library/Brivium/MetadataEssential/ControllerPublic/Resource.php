<?php

class Brivium_MetadataEssential_ControllerPublic_Resource extends XFCP_Brivium_MetadataEssential_ControllerPublic_Resource
{
	public function actionCategory()
	{
		$response = parent::actionCategory();
		if(!empty($response->params)){
			$response->params = $this->setMetadataValue($response->params);
		}
		return $response;
	}
	
	protected function _getResourceAddOrEditResponse(array $resource, array $category, array $attachments = array())
	{
		$response = parent::_getResourceAddOrEditResponse($resource, $category, $attachments);
		if(!empty($response->params)){
			$metadataModel = $this->_getMetadataModel();
			if(!empty($resource['resource_id'])){
				$response->params['resource']['metaData'] = $metadataModel->getMetadata('resource',$resource['resource_id']);
			}
			$response->params['canChangeTitleMeta'] = $metadataModel->canChangeResourceTitleMeta($category['resource_category_id']);
			$response->params['canChangeDescriptionMeta'] = $metadataModel->canChangeResourceDescriptionMeta($category['resource_category_id']);
			$response->params['canChangeKeywordMeta'] = $metadataModel->canChangeResourceKeywordMeta($category['resource_category_id']);
			$response->params['canChangeRobotIndex'] = $metadataModel->canChangeResourceRobotIndex($category['resource_category_id']);
			$response->params['canChangeRobotFollow'] = $metadataModel->canChangeResourceRobotFollow($category['resource_category_id']);
		}
		return $response;
	}
	
	protected function _getResourceViewWrapper($selectedTab, array $resource, array $category,
		XenForo_ControllerResponse_View $subView
	){
		$response = parent::_getResourceViewWrapper($selectedTab, $resource, $category, $subView);
		if(!empty($resource['resource_id']) && !empty($response->params)){
			$response->params = $this->setResourceMetadataValue($response->params, $resource);
		}
		return $response;
	}
	
	public function setResourceMetadataValue($viewParams, $resource)
	{
		if(!empty($resource['resource_id']) && !empty($resource['resource_category_id']) ){
			$metaModel = $this->_getMetadataModel();
			$options = XenForo_Application::get('options');
			$metaOptions = $options->BRME_resourceMetadata;

			$strReplace = array(
				XenForo_Template_Helper_Core::helperSnippet($resource['title'],300),
				$viewParams['category']['category_title'],
				$resource['username']
			);
			
			if(!empty($metaOptions['titleFixed']))
			{
				$metaOptions['titleFixed'] = str_replace(array('{$title}', '{$categoryTitle}', '{$author}'), $strReplace, $metaOptions['titleFixed']);
			}

			$metaData  = $metaModel->getMetadata('resource',$resource['resource_id']);
			
			if(isset($resource['tag_line']) && (empty($metaData['description']) || $metaOptions['description']=='content')){
				if (!empty($options->BRME_defaultResourceDes)) {
					$defaultDes = $options->BRME_defaultResourceDes;
					$defaultDes = str_replace(array('{$title}', '{$categoryTitle}', '{$author}'),$strReplace,$defaultDes);
					$metaData['description'] = $defaultDes;
				}else{
					$metaData['description'] = XenForo_Template_Helper_Core::helperSnippet($resource['tag_line'],300);
				}
			}
			if(isset($resource['title']) && (empty($metaData['title']) || $metaOptions['title']=='content')){
				if (!empty($options->BRME_defaultResourceTitle)){
					$defaultTitle = $options->BRME_defaultResourceTitle;
					$defaultTitle = str_replace(array('{$title}', '{$categoryTitle}', '{$author}'),$strReplace,$defaultTitle);
					$metaData['title'] = $defaultTitle;
				}else{
					$metaData['title'] = XenForo_Template_Helper_Core::helperSnippet($resource['title'],300);
				}
			}
			$xenAddons = XenForo_Application::get('addOns');
			if(!empty($metaOptions['keywords'])&& $metaOptions['keywords']=='xen_tag' &&!empty($xenAddons['Tinhte_XenTag'])){
				$metaData['keywords'] = Tinhte_XenTag_Helper::getImplodedTagsFromResource($resource);
			}
			if (!empty($options->BRME_defaultResourceKeywords) && empty($metaData['keywords'])) {
					$defaultThreadKeywords = $options->BRME_defaultResourceKeywords;
					$defaultThreadKeywords = str_replace(array('{$title}', '{$categoryTitle}', '{$author}'),$strReplace,$defaultThreadKeywords);
					$metaData['keywords'] = $defaultThreadKeywords;
			}

			$categoryMetaData  = $metaModel->getMetadata('resource_category',$resource['resource_category_id']);
			if(!empty($categoryMetaData['use_child'])){
				$robotMetaData = $metaModel->getRobotMetaData($categoryMetaData);
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

			$viewParams['metaData'] = $GLOBALS['BRME_metadata'] = $metaData;
			$viewParams['brmeOptions'] = $GLOBALS['brmeOptions'] = $metaOptions;
			
		}
		return $viewParams;
	}
	public function setMetadataValue($params)
	{
		if(!empty($params['category']['resource_category_id'])){
			$category = $params['category'];
			$metaModel = $this->_getMetadataModel();
			$options = XenForo_Application::get('options');
			$metaOptions = $options->BRME_resourceCategory;

			$strReplace = $category['category_title'];
			
			$metaData  = $metaModel->getMetadata('resource_category',$category['resource_category_id']);
			if(isset($category['category_description']) && (empty($metaData['description']) || (!empty($metaOptions['description']) && $metaOptions['description']=='content'))){
				if (!empty($options->BRME_defaultCategoryDes)) {
					$defaultDes = $options->BRME_defaultCategoryDes;
					$defaultDes = str_replace('{$categoryTitle}', $strReplace,$defaultDes);
					$metaData['description'] = $defaultDes;
				}else{
					$metaData['description'] = $category['description'];
				}
			}
			if(isset($category['category_title']) && (empty($metaData['title']) || (!empty($metaOptions['title']) && $metaOptions['title']=='content'))){
				if (!empty($options->BRME_defaultCategoryTitle)){
					$defaultTitle = $options->BRME_defaultCategoryTitle;
					$defaultTitle = str_replace('{$categoryTitle}', $strReplace, $defaultTitle);
					$metaData['title'] = $defaultTitle;
				}else{
					$metaData['title'] = XenForo_Template_Helper_Core::helperSnippet($category['title'],300);
				}
			}

			$metaData = $metaModel->getRobotMetaData($metaData);
			$params['metaData'] = $GLOBALS['BRME_metadata'] = $metaData;
			$params['brmeOptions'] = $GLOBALS['brmeOptions'] = $metaOptions;
			
		}
		return $params;
	}
	
	/**
	 * Updates an existing resource.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSave()
	{
		$GLOBALS['BRME_CPR_actionSave'] = $this;
		return parent::actionSave();
	}
	public function brmeActionSave(XenResource_DataWriter_Resource $writer) {
		$metaOptions = XenForo_Application::get('options')->BRME_resourceMetadata;
		$metadataModel = $this->_getMetadataModel();
		$data = array();
		
		$resource = $writer->getMergedData();
		if(!empty($metaOptions['description']) && $metaOptions['description']=='user'){
			if(!empty($resource['resource_category_id']) && $metadataModel->canChangeResourceDescriptionMeta($resource['resource_category_id'])){
				$data['description'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_description', XenForo_Input::STRING),300);
			}
		}else{
			$data['description'] = '';
		}
		if(!empty($metaOptions['title']) && $metaOptions['title']=='user'){
			if(!empty($resource['resource_category_id']) && $metadataModel->canChangeResourceTitleMeta($resource['resource_category_id'])){
				$data['title'] = XenForo_Template_Helper_Core::helperSnippet($this->_input->filterSingle('brme_meta_title', XenForo_Input::STRING),300);
			}
		}else{
			$data['title'] = '';
		}
		
		if(!empty($resource['resource_category_id']) && $metadataModel->canChangeResourceKeywordMeta($resource['resource_category_id'])){
			$data['keywords'] = $this->_input->filterSingle('brme_meta_keywords', XenForo_Input::STRING);
		}
		
		if(!empty($resource['resource_category_id']) && $metadataModel->canChangeResourceRobotIndex($resource['resource_category_id'])){
			$data['index'] = $this->_input->filterSingle('brme_index', XenForo_Input::UINT);
		}
		if(!empty($resource['resource_category_id']) && $metadataModel->canChangeResourceRobotFollow($resource['resource_category_id'])){
			$data['follow'] = $this->_input->filterSingle('brme_follow', XenForo_Input::UINT);
		}
		
		$data['author'] = $resource['username']?$resource['username']:'';
		$data['content_type'] = 'resource';
		$data['content_id'] = $resource['resource_id'];
		$metadataModel->updateMetadata($data,$data['content_type'],$data['content_id']);
	}
	
	protected function _getMetadataModel()
	{
		return $this->getModelFromCache('Brivium_MetadataEssential_Model_Metadata');
	}
}