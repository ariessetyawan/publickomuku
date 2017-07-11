<?php /*8fb6d4f79a9db380946195afb1f7345bb4e89d36*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ReportHandler_TraderRating extends XenForo_ReportHandler_Abstract
{
    public function getReportDetailsFromContent(array $content)
    {
        /** @var KomuKuYJB_Model_TraderRating $model */
        $model = XenForo_Model::create('KomuKuYJB_Model_TraderRating');

        $rating = $model->getTraderRatingById($content['feedback_id'], array(
            'join' => $model::FETCH_USER
        ));

        if (!$rating)
        {
            return array(false, false, false);
        }

        return array(
            $rating['feedback_id'],
            $rating['user_id'],
            array(
                'feedback_id' => $rating['feedback_id'],

                'user_id' => $rating['user_id'],
                'username' => $rating['username'],

                'for_user_id' => $rating['for_user_id'],
                'for_username' => $rating['for_username'],

                'message' => $rating['message'],
                'rating' => $rating['rating']
            )
        );
    }

    public function getVisibleReportsForUser(array $reports, array $viewingUser)
    {
        if (!XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifiedTraderRating', 'editAny')
            || !XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifiedTraderRating', 'deleteAny'))
        {
            return array();
        }

        return $reports;
    }

    public function getContentLink(array $report, array $contentInfo)
    {
        return XenForo_Link::buildPublicLink('classifieds/traders/ratings', $contentInfo);
    }

    public function getContentTitle(array $report, array $contentInfo)
    {
        return new XenForo_Phrase('trader_rating_by_x_for_y', array('by' => $contentInfo['username'], 'for' => $contentInfo['for_username']));
    }

    public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
    {
        $parser = XenForo_BbCode_Parser::create(
            XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view))
        );

        return $view->createTemplateObject('report_classified_trader_rating_content', array(
            'report' => $report,
            'content' => $contentInfo,
            'bbCodeParser' => $parser
        ));
    }
}