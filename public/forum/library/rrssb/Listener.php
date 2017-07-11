<?php
class rrssb_Listener
{
	public static function cssCacheTemplateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		$xenoptions = XenForo_Application::get('options');
		$optionHookThreadsTop = $xenoptions->rrssb_turnOn_top;
		$optionHookThreadsBottom = $xenoptions->rrssb_turnOn_bottomHook['hookname'];
		$optionHookPagesTop = $xenoptions->rrssb_turnOn_pagesTop;
		$optionHookPagesBottom = $xenoptions->rrssb_turnOn_pagesBottom;

		if ($templateName == 'thread_view')
		{
			if (!empty($optionHookThreadsTop) OR !empty($optionHookThreadsBottom))
			{
				$template->addRequiredExternal('css', 'SV_rrssbDefault');
				$template->preloadTemplate('SV_rrssbSharesThreads');
			}
		}
		if ($templateName == 'pagenode_container')
		{
			if (!empty($optionHookPagesTop) OR !empty($optionHookPagesBottom))
			{
				$template->addRequiredExternal('css', 'SV_rrssbDefault');
				$template->preloadTemplate('SV_rrssbSharesPages');
			}
		}
	}

	public static function addButtonsTemplateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		$xenoptions = XenForo_Application::get('options');
		$optionHookThreadsTop = $xenoptions->rrssb_turnOn_top;
		$optionHookThreadsBottom = $xenoptions->rrssb_turnOn_bottomHook['hookname'];
		$optionHookPagesTop = $xenoptions->rrssb_turnOn_pagesTop;
		$optionHookPagesBottom = $xenoptions->rrssb_turnOn_pagesBottom;
		$thread = $template->getParam('thread');
		$page = $template->getParam('page');
		$rrssbExcludedNodes = $xenoptions->rrssb_excludeForums;

		if (!empty($thread))
		{
			if (!in_array($thread['node_id'], $rrssbExcludedNodes))
			{
				if (!empty($optionHookThreadsTop))
				{
					if ($hookName == 'thread_view_pagenav_before')
					{
						$contents .= $template->create('SV_rrssbSharesThreads', array_merge($hookParams, $template->getParams()));
					}
				}
				if (!empty($optionHookThreadsBottom))
				{
					switch ($hookName) 
					{
						case $optionHookThreadsBottom :
						{
							$contents .= $template->create('SV_rrssbSharesThreads', array_merge($hookParams, $template->getParams()));
							break;
						}
					}
				}
			}
		}
		if (!empty($page))
		{
			if (!in_array($page['node_id'], $rrssbExcludedNodes))
			{
				if (!empty($optionHookPagesTop))
				{
					if ($hookName == 'pagenode_container_rrssb_top')
					{
						$contents = $template->create('SV_rrssbSharesPages', array_merge($hookParams, $template->getParams())) . $contents;
					}
				}
				if (!empty($optionHookPagesBottom))
				{
					if ($hookName == 'pagenode_container_article')
					{
						$contents .= $template->create('SV_rrssbSharesPages', array_merge($hookParams, $template->getParams()));
					}
				}
			}
		}
		if ($hookName == 'footer_after_copyright')
		{
			$contents .= '<div style="clear:both;" id="copyright"><a class="concealed" title="Responsive Social Sharing Buttons" href="https://xenforo.com/community/resources/3960/">Responsive Social Sharing Buttons</a> by <a class="concealed" title="CertForums.com" href="http://www.certforums.com/">CertForums.com</a></div>';
		}
	}
} 