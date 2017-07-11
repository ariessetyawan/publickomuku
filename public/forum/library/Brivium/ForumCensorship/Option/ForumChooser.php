<?php

abstract class Brivium_ForumCensorship_Option_ForumChooser
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$value = $preparedOption['option_value'];

		static $forums = false;
		if ($forums === false) {
			$nodeModel = XenForo_Model::create('XenForo_Model_Node');
			$nodes = $nodeModel->getAllNodes();
			$forums = array();
			$forums[] = '(None)';
			
			foreach ($nodes as $node) {
				if ($node['node_type_id'] == 'Forum') {
					$forums[] = array(
						'value' => $node['node_id'],
						'label' => $node['title'],
						'depth' => $node['depth'],
					);
				}
			}
		}

		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));
		
		return $view->createTemplateObject('BriviumForumCensorship_option_forumChooser', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $preparedOption['formatParams'],
			'editLink' => $editLink,

			'forums' => $forums,
		));
	}
}