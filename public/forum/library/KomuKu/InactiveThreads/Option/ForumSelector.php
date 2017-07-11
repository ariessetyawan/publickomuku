<?php

abstract class KomuKu_InactiveThreads_Option_ForumSelector
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$value = $preparedOption['option_value'];

		static $forums = false;
		
		if ($forums === false) 
		{
			$nodeModel = XenForo_Model::create('XenForo_Model_Node');
			$nodes = $nodeModel->getAllNodes();
			$forums = array();
			$forums[] = '(None)';
			
			foreach ($nodes as $node) 
			{
				if ($node['node_type_id'] == 'Forum') 
				{
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
		
		return $view->createTemplateObject('KomuKu_option_inactive_move', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $preparedOption['formatParams'],
			'formatParams' => $forums,
			'editLink' => $editLink,

			'forums' => $forums,
		));
	}
}