<?php

class KomuKu_SimpleForms_DestinationOption_ThreadPrefix
{
	public static function renderOption(XenForo_View $view, array $option)
	{
		$threadPrefixModel = self::_getThreadPrefixModel();
		$threadPrefixes = $threadPrefixModel->preparePrefixes($threadPrefixModel->getAllPrefixes());

		$options = array();
		foreach ($threadPrefixes as $threadPrefixId => $threadPrefix)
		{
			$options[$threadPrefixId] = $threadPrefix['title'];
		}
		$option['field_choices'] = $options;
		
		return $view->createTemplateObject('kmkform__destination_option_thread_prefix', array(
			'option' => $option
		));
	}
	
	/**
	 * @return XenForo_Model_ThreadPrefix
	 */
	protected static function _getThreadPrefixModel()
	{
		return XenForo_Model::create('XenForo_Model_ThreadPrefix');
	}
}