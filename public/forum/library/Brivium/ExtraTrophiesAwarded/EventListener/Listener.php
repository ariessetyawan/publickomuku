<?php
class Brivium_ExtraTrophiesAwarded_EventListener_Listener extends Brivium_BriviumHelper_EventListeners
{
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
		switch ($hookName) {
			case 'help_sidebar_links':
				$positionAward = XenForo_Application::get('options')->BRETA_positionAward;
				
				if($positionAward['help_page']){
					$newTemplate = $template->create('BRETA_' . $hookName, $template->getParams());
					$contents .= $newTemplate->render();
				}
				break;
			
			case 'BRETA_sidebar_visitor_panel':
				$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;

				if($positionAward['visitor_panel'] && XenForo_Application::get('options')->BRETA_showTrophyIcon){
					$newTemplate = $template->create($hookName,  $template->getParams());
					$contents .= $newTemplate->render();
				}
				break;
				
			case 'BRETA_navigation_visitor_tab':
				$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;

				if($positionAward['account_menu'] && XenForo_Application::get('options')->BRETA_showTrophyIcon){
					$newTemplate = $template->create($hookName,  $template->getParams());
					$contents .= $newTemplate->render();
				}
				break;
				
			case 'member_view_tabs_heading':
			case 'member_view_tabs_content':
				$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
				
				if($positionAward['member_view']){
					$newTemplate = $template->create('BRETA_' . $hookName, $template->getParams());
					$contents .= $newTemplate->render();
				}
				break;

			case 'member_view_sidebar_middle1':
				$newTemplate = $template->create('BRETA_' . $hookName, $template->getParams());
				$contents .= $newTemplate->render();
		}
	}
	
	public static function navigationTabs(&$extraTabs, $selectedTabId)
	{
		$positionAward = XenForo_Application::get('options')->BRETA_positionAward;

		if($positionAward['navigation_tab']){
			$extraTabs['BRETA_help_links'] = array(
					'title' => new XenForo_Phrase('BRETA_help_links'),
					'href' => XenForo_Link::buildPublicLink('help'),
					'position' => 'end',
					'linksTemplate' => 'BRETA_help_links'
			);
		}
	}
	
	public static function visitorSetup(XenForo_Visitor &$visitor)
	{
		try
		{
			if(!empty($visitor['user_id'])){
				$trophyModel = XenForo_Model::create('Brivium_ExtraTrophiesAwarded_Model_exTrophy');

				$visitor['awards'] = $trophyModel->prepareTrophies(
					$trophyModel->getAwardsForUserId($visitor,
					array('limit' => XenForo_Application::get('options')->BRETA_defaultTrophyIcons))
				);
				
			}
		} catch (Exception $e) {
			// do nothing
		}
		
	}
	
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data) 
	{	
		XenForo_Template_Helper_Core::$helperCallbacks['trophyicon'] = array('Brivium_ExtraTrophiesAwarded_EventListener_Helpers', 'helperGetAwardIcon');
	}
}