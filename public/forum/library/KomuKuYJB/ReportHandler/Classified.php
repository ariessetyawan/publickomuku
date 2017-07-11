<?php /*c4016617e0a6118fe5db39bec1888ff419c1cabf*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ReportHandler_Classified extends XenForo_ReportHandler_Abstract
{
    public function getReportDetailsFromContent(array $content)
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');

        $classified = $classifiedModel->getClassifiedById($content['classified_id'], array(
            'join' => $classifiedModel::FETCH_CATEGORY
        ));

        if (!$classified)
        {
            return array(false, false, false);
        }

        return array(
            $content['classified_id'],
            $content['user_id'],
            array(
                'classified_id' => $classified['classified_id'],
                'classified_title' => $classified['title'],

                'category_id' => $classified['category_id'],
                'category_title' => $classified['category_title'],

                'username' => $classified['username'],
                'description' => $classified['description']
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

        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('KomuKuYJB_Model_Category');

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
                if (!XenForo_Permission::hasContentPermission($category['permissions'], 'editAny')
                    || !XenForo_Permission::hasContentPermission($category['permissions'], 'deleteAny')
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
        return XenForo_Link::buildPublicLink('classifieds', $contentInfo);
    }

    public function getContentTitle(array $report, array $contentInfo)
    {
        return new XenForo_Phrase('classified_x', array('title' => $contentInfo['classified_title']));
    }

    public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
    {
        $parser = XenForo_BbCode_Parser::create(
            XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view))
        );

        return $view->createTemplateObject('report_classified_content', array(
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