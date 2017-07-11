<?php
class KomuKu_FollowingAlerts_Template_Helper_Core
{
	public static function helperFollowHtml(array $user, array $attributes, $wrapTag = '')
	{
		if (XenForo_Application::getOptions()->followingAlerts_autoConfirm)
		{
			$model = XenForo_Model::create('KomuKu_FollowingAlerts_Model_Follow');
		
			$attributes['data-thread'] = $model->canFollowThread() ? "1" : "0";
			$attributes['data-post'] = $model->canFollowPost() ? "1" : "0";
			$attributes['data-status'] = $model->canFollowStatus() ? "1" : "0";
			$attributes['data-resource'] = $model->canFollowResource() ? "1" : "0";

			$link = XenForo_Template_Helper_Core::helperFollowHtml($user, $attributes, $wrapTag);
			$link = str_replace('FollowLink' , 'FollowLink_Advanced', $link);
		}
		else
		{
			if (isset($attributes['class']))
			{
				$attributes['class'] .= ' OverlayTrigger ';
			}
			else
			{
				$attributes['class'] = 'OverlayTrigger';
			}
			
			$attributes['data-cacheOverlay'] = 'false';

			$link = XenForo_Template_Helper_Core::helperFollowHtml($user, $attributes, $wrapTag);
			$link = str_replace('FollowLink' , '', $link);
		}

		return $link;
	}
}