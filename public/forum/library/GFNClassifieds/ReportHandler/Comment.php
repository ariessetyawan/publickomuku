<?php /*8d1f7db8db887dfcf1f0e970408a30213af49994*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ReportHandler_Comment extends XenForo_ReportHandler_Abstract
{
    public function getReportDetailsFromContent(array $content)
    {
        /** @var GFNClassifieds_Model_Comment $commentModel */
        $commentModel = XenForo_Model::create('GFNClassifieds_Model_Comment');

        $comment = $commentModel->getCommentById($content['comment_id'], array(
            'join' => $commentModel::FETCH_CLASSIFIED
        ));

        if (!$comment)
        {
            return array(false, false, false);
        }

        return array(
            $comment['comment_id'],
            $comment['user_id'],
            array(
                'comment_id' => $comment['comment_id'],

                'classified_id' => $comment['classified_id'],
                'classified_title' => $comment['classified_title'],

                'category_id' => $comment['category_id'],
                'category_title' => $comment['category_title'],

                'username' => $comment['username'],
                'message' => $comment['message']
            )
        );
    }

    public function getVisibleReportsForUser(array $reports, array $viewingUser)
    {
        $reportsByCategory = array();

        foreach ($reports as $reportId => $report)
        {
            $info = XenForo_Helper_Php::safeUnserialize($report['content_info']);
            $reportsByCategory[$info['category_id']][] = $reportId;
        }

        /** @var GFNClassifieds_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('GFNClassifieds_Model_Category');

        $categories = $categoryModel->getCategoriesByIds(array_keys($reportsByCategory), array(
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        $categories = $categoryModel->unserializePermissionsInList($categories, 'category_permission_cache');

        foreach ($reportsByCategory as $categoryId => $categoryReports)
        {
            $remove = false;

            if (!isset($categories[$categoryId]))
            {
                $remove = true;
            }
            else
            {
                $category = $categories[$categoryId];
                if (!XenForo_Permission::hasContentPermission($category['permissions'], 'editCommentAny')
                    || !XenForo_Permission::hasContentPermission($category['permissions'], 'deleteCommentAny')
                )
                {
                    $remove = true;
                }
            }

            if ($remove)
            {
                foreach ($categoryReports as $reportId)
                {
                    unset ($reports[$reportId]);
                }
            }
        }

        return $reports;
    }

    public function getContentLink(array $report, array $contentInfo)
    {
        return XenForo_Link::buildPublicLink('classifieds/comments', $contentInfo);
    }

    public function getContentTitle(array $report, array $contentInfo)
    {
        return new XenForo_Phrase('comment_by_x', array('user' => $contentInfo['username']));
    }

    public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
    {
        $parser = XenForo_BbCode_Parser::create(
            XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view))
        );

        return $view->createTemplateObject('report_classified_comment_content', array(
            'report' => $report,
            'content' => $contentInfo,
            'bbCodeParser' => $parser
        ));
    }

    public function prepareExtraContent(array $contentInfo)
    {
        $contentInfo['classified_title'] = XenForo_Helper_String::censorString($contentInfo['classified_title']);
        return $contentInfo;
    }
}