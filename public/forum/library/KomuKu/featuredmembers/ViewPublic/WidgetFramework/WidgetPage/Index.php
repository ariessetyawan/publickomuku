<?php

class KomuKu_featuredmembers_ViewPublic_WidgetFramework_WidgetPage_Index extends XFCP_KomuKu_featuredmembers_ViewPublic_WidgetFramework_WidgetPage_Index
{
	public function renderHtml()
	{
		XenForo_Application::set('view', $this);
		
		parent::renderHtml();
	}
}
