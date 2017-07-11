<?php

class KomuKu_featuredmembers_Listener
{
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
        XenForo_Template_Helper_Core::$helperCallbacks = array_merge(
            XenForo_Template_Helper_Core::$helperCallbacks, array
            (
                'richusername'  => array('KomuKu_featuredmembers_XenForo_Template_Helper_Core', 'helperRichUserName')
            )
        );
	}


	public static function extendXenForoDataWriterUser($class, array &$extend)
	{
		$extend[] = 'KomuKu_featuredmembers_XenForo_DataWriter_User';
	}

	public static function extendXenForoControllerAdminUser($class, array &$extend)
	{
		$extend[] = 'KomuKu_featuredmembers_XenForo_ControllerAdmin_User';
	}


    public static function extendXenForoControllerModelUser($class, array &$extend)
    {
        $extend[] = 'KomuKu_featuredmembers_XenForo_Model_User';
    }

    public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        if ($hookName == 'user_criteria_profile')
        {
            $userCriteria = $template->getParam('userCriteria');

            $verifychecked = $userCriteria['dad_fm_is_verified'] ? 'checked' : '';
            $contents .= '<li><label><input type="checkbox" name="user_criteria[dad_fm_is_verified][rule]" value="dad_fm_is_verified"' . $verifychecked .' /> '. new XenForo_Phrase('dad_fm_miv') .'</label></li>';

            $featurechecked = $userCriteria['dad_fm_is_featured'] ? 'checked' : '';
            $contents .= '<li><label><input type="checkbox" name="user_criteria[dad_fm_is_featured][rule]" value="dad_fm_is_featured"' . $featurechecked .' /> '. new XenForo_Phrase('dad_fm_mif') .'</label></li>';

            $noverifychecked = $userCriteria['no_dad_fm_is_verified'] ? 'checked' : '';
            $contents .= '<li><label><input type="checkbox" name="user_criteria[no_dad_fm_is_verified][rule]" value="no_dad_fm_is_verified"' . $noverifychecked .' /> '. new XenForo_Phrase('dad_fm_minv') .'</label></li>';

            $nofeaturechecked = $userCriteria['no_dad_fm_is_featured'] ? 'checked' : '';
            $contents .= '<li><label><input type="checkbox" name="user_criteria[no_dad_fm_is_featured][rule]" value="no_dad_fm_is_featured"' . $nofeaturechecked .' /> '. new XenForo_Phrase('dad_fm_minf') .'</label></li>';
        }
    }

    public static function criteriaUser($rule, array $data, array $user, &$returnValue)
    {
        switch ($rule)
        {
            case 'dad_fm_is_verified':
                if (isset($user['dad_fm_is_verified']) && $user['dad_fm_is_verified'] == 1)
                {
                    $returnValue = true;
                }
                break;
            case 'dad_fm_is_featured':
                if (isset($user['dad_fm_is_featured']) && $user['dad_fm_is_featured'] == 1)
                {
                    $returnValue = true;
                }
                break;
            case 'no_dad_fm_is_verified':
                if (isset($user['dad_fm_is_verified']) && $user['dad_fm_is_verified'] == 0)
                {
                    $returnValue = true;
                }
                break;
            case 'no_dad_fm_is_featured':
                if (isset($user['dad_fm_is_featured']) && $user['dad_fm_is_featured'] == 0)
                {
                    $returnValue = true;
                }
                break;
        }
    }
}