<?php

class Brivium_MetadataEssential_EventListeners_Listener extends Brivium_BriviumHelper_EventListeners
{
	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($template instanceof XenForo_Template_Admin)
		{
		}else{
			switch ($templateName) {
				case 'thread_view':
					$template->preloadTemplate('BRME_page_container_head');
					break;
				case 'thread_edit':
				case 'thread_create':
					
				break;
			}
		}
	}
	
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
		switch ($hookName) {
			case 'forum_edit_basic_information': //admin hook
			case 'thread_create': 
				
				break;
			case 'page_container_head':
				$metaData = array(
					'title'	=>	'',
					'description'	=>	'',
					'keywords'	=>	'',
					'author'	=>	'',
				);
				if (isset($GLOBALS['BRME_metadata'])&& is_array($GLOBALS['BRME_metadata'])) {
					$metaData = array_merge($metaData,$GLOBALS['BRME_metadata']);
				}
				if (isset($GLOBALS['brmeOptions'])&& is_array($GLOBALS['brmeOptions'])) {
					$brmeOptions = $GLOBALS['brmeOptions'];
				}
				$newTemplate = $template->create('BRME_' . $hookName, $template->getParams());
				$newTemplate->setParams(array('metaData'=>$metaData,'brmeOptions'=>$brmeOptions));
				$contents .= $newTemplate->render();
				break;
		}

    }
	
	
}