<?php /*b6749b1caa3f2a1c0894c13ce88a9a35797e3dc8*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_CategoryPermission_User extends GFNClassifieds_ControllerAdmin_CategoryPermission_Abstract
{
    public function actionIndex()
    {
        $category = $this->_category;
        $categoryId = $category['category_id'];

        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $user = $this->_getValidUserOrError($userId);

        $permissionModel = $this->_getPermissionModel();

        $permissions = $permissionModel->getUserCollectionContentPermissionsForGroupedInterface(
            'classified_category', $categoryId, 'classifieds', 0, $user['user_id']
        );

        $viewParams = array(
            'category' => $category,
            'user' => $user,
            'permissions' => $permissions,
            'permissionChoices' => $permissionModel->getPermissionChoices('user', true)
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_CategoryPermission_User', 'permission_classifieds_category_user', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();
        $category = $this->_category;
        $categoryId = $category['category_id'];

        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $user = $this->_getValidUserOrError($userId);
        $permissions = $this->_input->filterSingle('permissions', XenForo_Input::ARRAY_SIMPLE);

        $this->_getPermissionModel()->updateContentPermissionsForUserCollection(
            $permissions, 'classified_category', $categoryId, 0, $user['user_id']
        );

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/categories/permissions', $category) . $this->getLastHash("user_{$userId}")
        );
    }

    public function actionAdd()
    {
        $category = $this->_category;
        $userName = $this->_input->filterSingle('username', XenForo_Input::STRING);

        $user = $this->_getUserModel()->getUserByName($userName);
        if (!$user)
        {
            return $this->responseError(new XenForo_Phrase('requested_user_not_found'), 404);
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
            $this->_buildLink('classifieds/categories/permissions/users', $category, array(
                'user_id' => $user['user_id'],
                'username' => $user['username']
            ))
        );
    }
}