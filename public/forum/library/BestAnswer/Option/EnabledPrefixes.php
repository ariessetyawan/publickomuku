<?php

class BestAnswer_Option_EnabledPrefixes
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$prefixModel = self::_getPrefixModel();
		
		$preparedOption['formatParams'] = array(array(
			'name' => "{$fieldPrefix}[{$preparedOption['option_id']}][0]",
			'label' => new XenForo_Phrase("no_prefix"),
			'selected' => empty($preparedOption['option_value']) || !empty($preparedOption['option_value'][0])
		));
		
		foreach ($prefixModel->getAllPrefixes() AS $prefix)
		{
			$preparedOption['formatParams'][] = array(
				'name' => "{$fieldPrefix}[{$preparedOption['option_id']}][{$prefix['prefix_id']}]",
				'label' => new XenForo_Phrase("thread_prefix_" . $prefix['prefix_id']),
				'selected' => empty($preparedOption['option_value']) || !empty($preparedOption['option_value'][$prefix['prefix_id']])
			);
		}

		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
			'option_list_option_checkbox', $view, $fieldPrefix, $preparedOption, $canEdit,
			array('class' => 'checkboxColumns enabledPrefixes')
		);
	}

	public static function verifyOption(array &$choices, XenForo_DataWriter $dw, $fieldName)
	{
		if ($dw->isInsert())
		{
			// insert - just trust the default value
			return true;
		}
		
		$prefixModel = self::_getPrefixModel();
		
		$prefixes = array_merge(array(0), array_keys($prefixModel->getAllPrefixes()));
		
		if (count(array_intersect(array_keys($choices), $prefixes)) == count($prefixes))
		{
			$choices = array();
		}
		else
		{
			$exclusions = array();
			
			foreach ($prefixes AS $prefix)
			{
				if (!empty($choices[$prefix]))
				{
					$exclusions[$prefix] = true;
				}
			}

			$choices = $exclusions;
		}

		return true;
	}
	
	/**
	 * @return XenForo_Model_ThreadPrefix
	 */
	protected static function _getPrefixModel()
	{
		return XenForo_Model::create('XenForo_Model_ThreadPrefix');
	}
}