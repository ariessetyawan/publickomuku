<?php /*78a69ad615ffdd2f238c0eb7fb7847a86c474787*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ControllerPublic_Conversation extends XFCP_GFNClassifieds_Extend_XenForo_ControllerPublic_Conversation
{
    public function actionView()
    {
        $controllerResponse = parent::actionView();

        if ($controllerResponse instanceof XenForo_ControllerResponse_View
            && !empty($controllerResponse->params['conversation']['classified_id'])
        )
        {
            $params = &$controllerResponse->params;
            $conversation = $params['conversation'];

            if (XenForo_Application::isRegistered('GFNClassifiedsContactPage'))
            {
                list ($classified, $category) = XenForo_Application::get('GFNClassifiedsContactPage');
            }
            else
            {
                /** @var GFNClassifieds_ControllerHelper_Content $contentHelper */
                $contentHelper = $this->getHelper('GFNClassifieds_ControllerHelper_Content');

                list ($classified, $category) = $contentHelper->assertClassifiedValidAndViewable($conversation['classified_id'], array(
                    'join' => GFNClassifieds_Model_Classified::FETCH_LOCATION
                ));

                if ($conversation['user_id'] == XenForo_Visitor::getUserId())
                {
                    return $this->responseRedirect(
                        XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
                        $this->_buildLink('classifieds/contact', $classified, array(
                            'page' => $params['page']
                        ))
                    );
                }
            }

            if ($classified['discussion_thread_id'])
            {
                /** @var XenForo_Model_Thread $threadModel */
                $threadModel = $this->getModelFromCache('XenForo_Model_Thread');
                $params['thread'] = $threadModel->getThreadById($classified['discussion_thread_id']);
            }

            $advertType = $this->_getAdvertTypeModel()->getAdvertTypeById($classified['advert_type_id']);
            $this->_getAdvertTypeModel()->prepareAdvertType($advertType);

            if ($conversation['user_id'] != XenForo_Visitor::getUserId())
            {
                $params['showStarter'] = true;

                $params['starter'] = $this->_getUserModel()->getUserById($conversation['user_id'], array(
                    'join' => XenForo_Model_User::FETCH_USER_PROFILE
                ));

                $params['canViewOnlineStatus'] = $this->_getUserModel()->canViewUserOnlineStatus($params['starter']);
            }
            else
            {
                $params['showStarter'] = false;
                $params['starter'] = null;
                $params['canViewOnlineStatus'] = $this->_getUserModel()->canViewUserOnlineStatus($classified);
            }

            $params['canPublishLocation'] = false;

            if ($classified['location_private'] && $classified['user_id'] == XenForo_Visitor::getUserId() && !$conversation['show_location'])
            {
                $params['canPublishLocation'] = true;
            }

            $params['classified'] = $classified;
            $params['category'] = $category;
            $params['advertType'] = $advertType;
            $params['categoryBreadcrumbs'] = $this->_getCategoryModel()->getCategoryBreadcrumb($category);
            $params['selectedTab'] = 'conversation';

            $this->_routeMatch->setSections('classifieds');
            $controllerResponse->templateName = 'classifieds_item_conversation';
        }

        return $controllerResponse;
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }

    /**
     * @return GFNClassifieds_Model_AdvertType
     */
    protected function _getAdvertTypeModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_AdvertType');
    }

    /**
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }
}