<?php /*41cfd2d4595debc1a569474620c61d2f33923a3a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_NewsFeedHandler_Classified extends XenForo_NewsFeedHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, array $viewingUser)
    {
        $model = $this->_getClassifiedModel();

        $classifieds = $model->getClassifiedsByIds($contentIds, array(
            'join' => $model::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        foreach ($classifieds as &$classified)
        {
            $classified['title'] = XenForo_Helper_String::censorString($classified['title']);
        }

        return $classifieds;
    }

    public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
    {
        $model = $this->_getClassifiedModel();
        $categoryPermissions = XenForo_Permission::unserializePermissions($content['category_permission_cache']);
        return $model->canViewClassifiedAndContainer($content, $content, $null, $viewingUser, $categoryPermissions);
    }

    /**
     * @return GFNClassifieds_Model_Classified
     * @throws XenForo_Exception
     */
    protected function _getClassifiedModel()
    {
        static $model = null;

        if ($model === null)
        {
            $model = XenForo_Model::create('GFNClassifieds_Model_Classified');
        }

        return $model;
    }
}