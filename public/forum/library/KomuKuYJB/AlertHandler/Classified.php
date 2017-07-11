<?php /*41462a6496dcb00317a627f0590fb105155c68bd*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_AlertHandler_Classified extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
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

    public function canViewAlert(array $alert, $content, array $viewingUser)
    {
        $model = $this->_getClassifiedModel();
        $categoryPermissions = XenForo_Permission::unserializePermissions($content['category_permission_cache']);
        return $model->canViewClassifiedAndContainer($content, $content, $null, $viewingUser, $categoryPermissions);
    }

    /**
     * @return KomuKuYJB_Model_Classified
     * @throws XenForo_Exception
     */
    protected function _getClassifiedModel()
    {
        static $model = null;

        if ($model === null)
        {
            $model = XenForo_Model::create('KomuKuYJB_Model_Classified');
        }

        return $model;
    }
}