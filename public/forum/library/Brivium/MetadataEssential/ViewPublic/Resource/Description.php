<?php

class Brivium_MetadataEssential_ViewPublic_Resource_Description extends XFCP_Brivium_MetadataEssential_ViewPublic_Resource_Description
{
	public function renderHtml()
	{
		$GLOBALS['Brivium_MetadataEssential_ViewPublic_Resource_Description'] = true;
		return parent::renderHtml();
	}
}