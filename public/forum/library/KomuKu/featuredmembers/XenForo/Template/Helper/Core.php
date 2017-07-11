<?php

class KomuKu_featuredmembers_XenForo_Template_Helper_Core extends XenForo_Template_Helper_Core
{
	public static function helperRichUserName(array $user, $usernameHtml = '')
	{
		$parent = parent::helperRichUserName($user, $usernameHtml);

		if (isset($user['dad_fm_is_verified']) && $user['dad_fm_is_verified'] && is_string($parent))
		{

			/*$parent = '<i class="fa fa-check-circle-o Tooltip" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '"></i>&nbsp;'.$parent;*/
            $options = XenForo_Application::get('options');

            if ($options->dad_fm_badge_type == "image"){
                if (isset($options->dad_fm_verifiedbadge) && !empty($options->dad_fm_verifiedbadge)) {
                    if ($options->dad_fm_verifiedbadge_opposite){
                        $parent = $parent . '<img class="VerifiedBadge Tooltip" src="' . $options->dad_fm_verifiedbadge . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '" alt="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '" />';
                    } else {
                        $parent = '<img class="VerifiedBadge Tooltip" src="' . $options->dad_fm_verifiedbadge . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '" alt="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '" />'.$parent;
                    }

                }
            } elseif ($options->dad_fm_badge_type == "icon"){
                if (isset($options->dad_fm_verifiedbadge_icon) && !empty($options->dad_fm_verifiedbadge_icon)) {
                    if ($options->dad_fm_verifiedbadge_opposite){
                        $parent = $parent . ' <i class="VerifiedBadgeIcon Tooltip fa ' . $options->dad_fm_verifiedbadge_icon . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '"></i>';
                    } else {
                        $parent = '<i class="VerifiedBadgeIcon Tooltip fa ' . $options->dad_fm_verifiedbadge_icon . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taiv', ['username' => $user['username']]) . '"></i> '.$parent;
                    }

                }
            }


		}

        if (isset($user['dad_fm_is_featured']) && $user['dad_fm_is_featured'] && is_string($parent))
        {

            $options = XenForo_Application::get('options');
            if ($options->dad_fm_badge_type == "image"){
                if (isset($options->dad_fm_featuredbadge) && !empty($options->dad_fm_featuredbadge)){
                    if ($options->dad_fm_featuredbadge_opposite){
                        $parent = $parent . '<img class="FeaturedBadge Tooltip" src="' . $options->dad_fm_featuredbadge . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '" alt="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '" />';
                    } else{
                        $parent = '<img class="FeaturedBadge Tooltip" src="' . $options->dad_fm_featuredbadge . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '" alt="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '" />'.$parent;
                    }

                }
            } elseif ($options->dad_fm_badge_type == "icon"){
                if (isset($options->dad_fm_featuredbadge_icon) && !empty($options->dad_fm_featuredbadge_icon)){
                    if ($options->dad_fm_featuredbadge_opposite){
                        $parent = $parent . ' <i class="FeaturedBadgeIcon Tooltip fa ' . $options->dad_fm_featuredbadge_icon . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '"></i>';
                    } else{
                        $parent = '<i class="FeaturedBadgeIcon Tooltip fa ' . $options->dad_fm_featuredbadge_icon . '" data-offsetx="-8" title="' . new XenForo_Phrase('dad_fm_taif', ['username' => $user['username']]) . '"></i> '.$parent;
                    }

                }
            }

        }

		return $parent;
	}
}