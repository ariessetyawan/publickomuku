<?php

//######################## User Ratings By ThemeHouse ###########################

/**
 * Handler for reported posts.
 */
class ThemeHouse_UserRatings_ReportHandler_Ratings extends XenForo_ReportHandler_Abstract
{
    /**
     * Gets report details from raw array of content (eg, a rating record).
     *
     * @see XenForo_ReportHandler_Abstract::getReportDetailsFromContent()
     */
    public function getReportDetailsFromContent(array $content)
    {
        return array(
            $content['rating_id'],
            $content['from_user_id'],
            $content,
        );
    }

    /**
     * Gets the visible reports of this content type for the viewing user.
     *
     * @see XenForo_ReportHandler_Abstract:getVisibleReportsForUser()
     */
    public function getVisibleReportsForUser(array $reports, array $viewingUser)
    {
        return $reports;
    }

    /**
     * Gets the title of the specified content.
     *
     * @see XenForo_ReportHandler_Abstract:getContentTitle()
     */
    public function getContentTitle(array $report, array $contentInfo)
    {
        if (!empty($contentInfo['system_generated'])) {
            return new XenForo_Phrase('th_auto_report_title', array('rating' => $contentInfo['rating'] == 0 ? 'neutral' : 'negative'));
        }

        return new XenForo_Phrase('th_rating_left_for_x_by_y', array('forName' => $contentInfo['to_username'], 'byName' => $contentInfo['from_username']));
    }

    /**
     * Gets the link to the specified content.
     *
     * @see XenForo_ReportHandler_Abstract::getContentLink()
     */
    public function getContentLink(array $report, array $contentInfo)
    {
        return XenForo_Link::buildPublicLink('canonical:ratings', array('rating_id' => $report['content_id']));
    }

    /**
     * A callback that is called when viewing the full report.
     *
     * @see XenForo_ReportHandler_Abstract::viewCallback()
     */
    public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
    {
        return $view->createTemplateObject('th_rating_report_content', array(
            'report' => $report,
            'content' => $contentInfo,
        ));
    }
}
