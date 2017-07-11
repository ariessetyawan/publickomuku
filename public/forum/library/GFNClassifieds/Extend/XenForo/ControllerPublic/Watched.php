<?php /*acb10f127a2b8bcede723f7d102eac0f6a90bbd1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ControllerPublic_Watched extends XFCP_GFNClassifieds_Extend_XenForo_ControllerPublic_Watched
{
    public function actionClassifieds()
    {
        $this->getRouteMatch()->setSections('classifieds');
        return $this->responseReroute('GFNClassifieds_ControllerPublic_ClassifiedWatch', 'index');
    }

    public function actionClassifiedCategories()
    {
        $this->getRouteMatch()->setSections('classifieds');
        return $this->responseReroute('GFNClassifieds_ControllerPublic_CategoryWatch', 'index');
    }

    protected function _takeEmailAction(array $user, $action, $type, $id)
    {
        parent::_takeEmailAction($user, $action, $type, $id);

        if ($type == '' || $type == 'classified')
        {
            if ($id)
            {
                $this->_getClassifiedWatchModel()->setClassifiedWatchState($user['user_id'], $id, $action);
            }
            else
            {
                $this->_getClassifiedWatchModel()->setClassifiedWatchSateForAll($user['user_id'], $action);
            }
        }

        if ($type == '' || $type == 'classified_category')
        {
            if ($id)
            {
                $this->_getClassifiedCategoryWatchModel()->setCategoryWatchState(
                    $user['user_id'], $id,
                    $action == '' ? 'delete' : null, null,
                    $action == 'watch_email' ? true : false
                );
            }
            else
            {
                $this->_getClassifiedCategoryWatchModel()->setCategoryWatchStateForAll($user['user_id'], $action);
            }
        }
    }

    protected function _getEmailActionConfirmPhrase(array $user, $action, $type, $id)
    {
        if ($type == 'classified')
        {
            if ($id)
            {
                return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_one_classified');
            }
            else
            {
                return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_all_classifieds');
            }
        }

        if ($type == 'classified_category')
        {
            if ($id)
            {
                return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_one_category');
            }
            else
            {
                return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_all_categories');
            }
        }

        return parent::_getEmailActionConfirmPhrase($user, $action, $type, $id);
    }

    /**
     * @return GFNClassifieds_Model_ClassifiedWatch
     */
    protected function _getClassifiedWatchModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_ClassifiedWatch');
    }

    /**
     * @return GFNClassifieds_Model_CategoryWatch
     */
    protected function _getClassifiedCategoryWatchModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryWatch');
    }
}