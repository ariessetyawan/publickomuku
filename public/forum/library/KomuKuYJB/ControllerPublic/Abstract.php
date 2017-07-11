<?php /*106ebe04eed98d1642fc775751d25237e74f292f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class KomuKuYJB_ControllerPublic_Abstract extends XenForo_ControllerPublic_Abstract
{
    protected function _assertViewingPermissions($action)
    {
        parent::_assertViewingPermissions($action);
        $visitor = XenForo_Visitor::getInstance();
        $action = strtolower($action);

        if (!$visitor->hasPermission('classifieds', 'view'))
        {
            throw $this->getNoPermissionResponseException();
        }

        if ($action == 'ip')
        {
            if (!$visitor->getUserId() || !$visitor->hasPermission('general', 'viewIps'))
            {
                throw $this->getNoPermissionResponseException();
            }
        }
    }

    protected function _assertCorrectVersion($action)
    {
        parent::_assertCorrectVersion($action);

        if (XenForo_Application::debugMode())
        {
            return;
        }

        if (!XenForo_Application::get('config')->checkVersion)
        {
            return;
        }

        if (KomuKuYJB_Application::$versionId != GFNCore_Application::getInstalledVersion('KomuKuYJB'))
        {
            $response = $this->responseMessage(new XenForo_Phrase('classified_section_currently_being_upgraded'));
            throw $this->responseException($response, 503);
        }
    }

    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('viewing_classifieds_section');
    }

    /**
     * @return KomuKuYJB_ControllerHelper_Content
     */
    public function getContentHelper()
    {
        return $this->getHelper('KomuKuYJB_ControllerHelper_Content');
    }

    /**
     * @return KomuKuYJB_ControllerHelper_Model
     */
    public function models()
    {
        return $this->getHelper('KomuKuYJB_ControllerHelper_Model');
    }

    protected function _getLogChanges(XenForo_DataWriter $writer)
    {
        $newData = $writer->getMergedNewData();
        $oldData = $writer->getMergedExistingData();
        $changes = array();

        foreach ($newData as $key => $value)
        {
            if (isset($oldData[$key]))
            {
                $changes[$key] = $oldData[$key];
            }
        }

        return $changes;
    }

    protected function _getClassifiedListFetchOptions()
    {
        return array(
            'join' => KomuKuYJB_Model_Classified::FETCH_CATEGORY
                | KomuKuYJB_Model_Classified::FETCH_LOCATION
                | KomuKuYJB_Model_Classified::FETCH_USER
        );
    }

    protected function _displayFilterOptions(array $viewableCategories, array $searchCategoryIds)
    {
        /*foreach ($searchCategoryIds AS $searchCategoryId)
        {
            if (!isset($viewableCategories[$searchCategoryId]))
            {
                continue;
            }

            if ($viewableCategories[$searchCategoryId]['prefix_cache']
                && (is_array($viewableCategories[$searchCategoryId]['prefix_cache'])
                    ? count($viewableCategories[$searchCategoryId]['prefix_cache']) > 0
                    : strlen($viewableCategories[$searchCategoryId]['prefix_cache']) > 5
                ))
            {
                return true;
            }

            if ($viewableCategories[$searchCategoryId]['advert_type_cache']
                && (is_array($viewableCategories[$searchCategoryId]['advert_type_cache'])
                    ? count($viewableCategories[$searchCategoryId]['advert_type_cache']) > 0
                    : strlen($viewableCategories[$searchCategoryId]['advert_type_cache']) > 5
                ))
            {
                return true;
            }
        }*/

        return true;
    }

    /**
     * @return XenForo_Model_Like
     */
    protected function _getLikeModel()
    {
        return $this->getModelFromCache('XenForo_Model_Like');
    }
}