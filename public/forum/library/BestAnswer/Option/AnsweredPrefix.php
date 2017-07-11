<?php

class BestAnswer_Option_AnsweredPrefix
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$prefixModel = self::_getPrefixModel();
		
		$preparedOption['formatParams'] = $prefixModel->getPrefixOptions();

		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
			'option_template_best_answer_answered_prefix', $view, $fieldPrefix, $preparedOption, $canEdit,
			array('class' => 'checkboxColumns')
		);
	}

	public static function verifyOption(array &$choices, XenForo_DataWriter $dw, $fieldName)
	{
		if ($dw->isInsert())
		{
			// insert - just trust the default value
			return true;
		}

		if (!isset($choices['type']))
		{
			return false;
		}
		
		if ($choices['type'] == 'custom')
		{
			$prefixModel = self::_getPrefixModel();
			
			if (!$prefixModel->getPrefixById($choices['custom']))
			{
				return false;
			}
			
			return true;
		}
		
		if (!in_array($choices['type'], array('none', 'additional')))
		{
			return false;
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