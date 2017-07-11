<?php /*7bbfda67ecf7a2d0f63ce3305f10586f576072aa*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_Activity extends KomuKuYJB_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if (!$this->getModelFromCache('XenForo_Model_User')->canViewMemberList())
        {
            return $this->responseNoPermission();
        }

        $this->_assertNewsFeedEnabled();
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/activity'));

        $newsFeedId = $this->_input->filterSingle('news_feed_id', XenForo_Input::UINT);
        $viewParams = $this->_getNewsFeedModel()->getNewsFeed(array('classifiedOnly' => true), $newsFeedId);
        return $this->responseView('XenForo_ViewPublic_NewsFeed_View', 'classifieds_news_feed_page', $viewParams);
    }

    /**
     * @return XenForo_Model_NewsFeed
     */
    protected function _getNewsFeedModel()
    {
        return $this->getModelFromCache('XenForo_Model_NewsFeed');
    }

    protected function _assertNewsFeedEnabled()
    {
        if (!XenForo_Application::get('options')->enableNewsFeed)
        {
            throw $this->responseException(
                $this->responseError(new XenForo_Phrase('news_feed_disabled'), 503) // 503 Service Unavailable
            );
        }
    }
}