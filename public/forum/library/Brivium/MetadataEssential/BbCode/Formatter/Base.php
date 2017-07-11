<?php
class Brivium_MetadataEssential_BbCode_Formatter_Base extends XFCP_Brivium_MetadataEssential_BbCode_Formatter_Base
{
	public function renderTagAttach(array $tag, array $rendererStates)
	{
		$response = parent::renderTagAttach($tag, $rendererStates);

		$options = XenForo_Application::get('options');
		if (!empty($GLOBALS['Brivium_MetadataEssential_ViewPublic_Resource_Description']) && !empty($options->BRME_defaultImageResource))
		{
			$response = preg_replace('/alt="(.+?)"/', 'alt="'.$options->BRME_defaultImageResource.'"', $response);
		}
		if (!empty($GLOBALS['Brivium_MetadataEssential_ViewPublic_Thread_View']) && !empty($options->BRME_defaultPostImageAltTag))
		{
			$response = preg_replace('/alt="(.+?)"/', 'alt="'.$options->BRME_defaultPostImageAltTag.'"', $response);
		}

		return $response;
	}
}