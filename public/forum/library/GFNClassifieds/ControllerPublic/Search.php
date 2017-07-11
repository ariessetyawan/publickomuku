<?php /*028844ce14860dfa6d43d60cf6e6eddb0e9c4b8e*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_Search extends GFNClassifieds_ControllerPublic_Abstract
{
    protected function _assertViewingPermissions($action)
    {
        parent::_assertViewingPermissions($action);

        if (!XenForo_Visitor::getInstance()->hasPermission('classifieds', 'search'))
        {
            throw $this->getNoPermissionResponseException();
        }
    }

    public function actionIndex()
    {
        $this->_request->setParam('type', 'classified');
        XenForo_Application::set('showClassifiedSearchForm', true);
        return $this->responseReroute('XenForo_ControllerPublic_Search', 'index');
    }
}