<?php

class Brivium_MetadataEssential_ViewPublic_Thread_View extends XFCP_Brivium_MetadataEssential_ViewPublic_Thread_View
{
	public function renderHtml()
	{
		$GLOBALS['Brivium_MetadataEssential_ViewPublic_Thread_View'] = true;
		return parent::renderHtml();
	}
}