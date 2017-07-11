<?php /*010f05672740095e180e19c7fbb0d60639589612*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_ControllerPublic_Thread extends XFCP_KomuKuYJB_Extend_XenForo_ControllerPublic_Thread
{
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View)
        {
            $params = &$response->params;

            if (isset($params['thread']['discussion_type']) && $params['thread']['discussion_type'] == 'classified')
            {
                /** @var KomuKuYJB_Model_Classified $classifiedModel */ /** @var KomuKuYJB_Model_Category $categoryModel */ /** @var KomuKuYJB_Model_AdvertType $advertTypeModel */ /** @var KomuKuYJB_Model_Comment $commentModel */
                $classifiedModel = $this->getModelFromCache('KomuKuYJB_Model_Classified');
                $categoryModel = $this->getModelFromCache('KomuKuYJB_Model_Category');
                $advertTypeModel = $this->getModelFromCache('KomuKuYJB_Model_AdvertType');
                $commentModel = $this->getModelFromCache('KomuKuYJB_Model_Comment');

                $visitor = XenForo_Visitor::getInstance();
                $thread = $params['thread'];

                $fetchOptions = array(
                    'join' => $classifiedModel::FETCH_CATEGORY
                        | $classifiedModel::FETCH_USER
                        | $classifiedModel::FETCH_LOCATION,
                    'watchUserId' => $visitor['user_id'],
                    'likeUserId' => $visitor['user_id'],
                    'contactUserId' => $visitor['user_id'],
                    'permissionCombinationId' => $visitor['permission_combination_id']
                );

                $classified = $classifiedModel->getClassifiedByDiscussionId($thread['thread_id'], $fetchOptions);
                if ($classified)
                {
                    $categoryModel->setCategoryPermCache($visitor['permission_combination_id'], $classified['category_id'], $classified['category_permission_cache']);
                    $classifiedModel->getDeletionLog($classified);

                    if ($classifiedModel->canViewClassifiedAndContainer($classified, $classified))
                    {
                        $classified = $categoryModel->prepareCategory($classified);
                        $classified = $classifiedModel->prepareClassified($classified, $classified);
                        $category = $classified;
                        $classified = $classifiedModel->prepareClassifiedCustomFields($classified, $category);
                        $classified['fieldCache'] = $category['fieldCache'];

                        $advertType = $advertTypeModel->getAdvertTypeById($classified['advert_type_id']);
                        $advertTypeModel->prepareAdvertType($advertType);

                        if ($classified['gallery_count'] && $classifiedModel->canViewGalleryImage($classified, $classified))
                        {
                            $galleryImages = $this->_getAttachmentModel()->getAttachmentsByContentId('classified_gallery', $classified['classified_id']);
                            $galleryImages = $this->_getAttachmentModel()->prepareAttachments($galleryImages);
                            $params['galleryImages'] = $galleryImages;
                        }

                        $criteria = $commentModel->getPermissionBasedFetchConditions($classified);
                        $criteria['classified_id'] = $classified['classified_id'];
                        $params['totalComments'] = $commentModel->countComments($criteria);

                        $params['classifiedViewPage'] = 'thread_view';
                        $params['classified'] = $classified;
                        $params['advertType'] = $advertType;
                        $params['socialLinks'] = KomuKuYJB_Helper_Misc::getSocialShareLinks(
                            $classified['title'], XenForo_Link::buildPublicLink('canonical:classifieds', $classified)
                        );
                    }
                }
            }
        }

        return $response;
    }
}