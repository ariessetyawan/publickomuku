<?php

class KomuKuJVC_Listeners_Nav
{
	public static function listen(array &$extraTabs, $selectedTabId)
	{

		$options = XenForo_Application::get('options');
		$hideNav = $options->hideNav;
		$perms = XenForo_Model::create('KomuKuJVC_Model_Perms')->getPermissions();
		
		if(!$hideNav && $perms['canViewDirectory']){
		
			$extraTabs['directory'] = array(
				'perms' => $perms,
				'title' => new XenForo_Phrase('directory'),
				'href' => XenForo_Link::buildPublicLink('full:directory'),
				'position' => 'middle',
				'linksTemplate' => 'sfdirectory_nav',
				'hideNav' => $hideNav
			);
		
		}
	}
	
	

	
}