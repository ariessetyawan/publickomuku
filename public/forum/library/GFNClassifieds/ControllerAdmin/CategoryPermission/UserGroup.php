<?php /*b470fb527ecbc2e69908237fe363519e1bbdd0b3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_CategoryPermission_UserGroup extends GFNClassifieds_ControllerAdmin_CategoryPermission_Abstract
{
    public function actionIndex()
    {
        $category = $this->_category;
        $categoryId = $category['category_id'];

        $userGroupId = $this->_input->filterSingle('user_group_id', XenForo_Input::UINT);
        $userGroup = $this->_getValidUserGroupOrError($userGroupId);

        $permissionModel = $this->_getPermissionModel();

        $permissions = $permissionModel->getUserCollectionContentPermissionsForGroupedInterface(
            'classified_category', $categoryId, 'classifieds', $userGroup['user_group_id'], 0
        );

        $viewParams = array(
            'category' => $category,
            'userGroup' => $userGroup,
            'permissions' => $permissions,
            'permissionChoices' => $permissionModel->getPermissionChoices('userGroup', true)
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_CategoryPermission_UserGroup', 'permission_classifieds_category_user_group', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();
        $category = $this->_category;
        $categoryId = $category['category_id'];

        $userGroupId = $this->_input->filterSingle('user_group_id', XenForo_Input::UINT);
        $userGroup = $this->_getValidUserGroupOrError($userGroupId);
        $permissions = $this->_input->filterSingle('permissions', XenForo_Input::ARRAY_SIMPLE);

        $this->_getPermissionModel()->updateContentPermissionsForUserCollection(
            $permissions, 'classified_category', $categoryId, $userGroup['user_group_id'], 0
        );

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/categories/permissions', $category) . $this->getLastHash("user_group_{$userGroupId}")
        );
    }
}