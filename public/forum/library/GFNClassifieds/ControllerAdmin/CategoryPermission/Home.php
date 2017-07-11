<?php /*b8c66bd806aa869a72e48c41f673745346412c7b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_CategoryPermission_Home extends GFNClassifieds_ControllerAdmin_CategoryPermission_Abstract
{
    public function actionIndex()
    {
        $category = $this->_category;
        $categoryId = $category['category_id'];

        $permissionSets = $this->_getPermissionModel()->getUserCombinationsWithContentPermissions('classified_category');

        $groupsWithPerms = array();
        foreach ($permissionSets AS $set)
        {
            if ($set['user_group_id'] && $set['content_id'] == $categoryId)
            {
                $groupsWithPerms[$set['user_group_id']] = true;
            }
        }

        $viewParams = array(
            'category' => $category,
            'userGroups' => $this->_getUserGroupModel()->getAllUserGroups(),
            'groupsWithPerms' => $groupsWithPerms,
            'users' => $this->_getPermissionModel()->getUsersWithContentUserPermissions('classified_category', $categoryId),
            'revoked' => $this->_permissionsAreRevoked($categoryId, 0, 0),
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_CategoryPermission_List', 'permission_classifieds_category', $viewParams);
    }

    public function actionCategoryWideRevoke()
    {
        $this->_assertPostOnly();

        $category = $this->_category;
        $revoke = $this->_input->filterSingle('revoke', XenForo_Input::BOOLEAN);

        $this->_setPermissionRevokeStatus($category['category_id'], 0, 0, $revoke);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/categories/permissions', $category)
        );
    }
}