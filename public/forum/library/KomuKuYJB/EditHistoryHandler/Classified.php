<?php /*47e1b86c917cd20a5b20bc6e5fa535474701ae42*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_EditHistoryHandler_Classified extends XenForo_EditHistoryHandler_Abstract
{
    protected $_prefix = 'classifieds';

    protected function _getContent($contentId, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');

        $classified = $classifiedModel->getClassifiedById($contentId, array(
            'join' => $classifiedModel::FETCH_CATEGORY | $classifiedModel::FETCH_USER,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        if ($classified)
        {
            $classified['permissions'] = XenForo_Permission::unserializePermissions($classified['category_permission_cache']);
            $classified = $classifiedModel->prepareClassified($classified);
        }

        return $classified;
    }

    protected function _canViewHistoryAndContent(array $content, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');

        return $classifiedModel->canViewClassifiedAndContainer($content, $content, $null, $viewingUser, $content['permissions'])
            && $classifiedModel->canViewDescriptionEditHistory($content, $content, $null, $viewingUser, $content['permissions']);
    }

    protected function _canRevertContent(array $content, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');
        return $classifiedModel->canEditClassified($content, $content, $null, $viewingUser, $content['permissions']);
    }

    public function getText(array $content)
    {
        return $content['description'];
    }

    public function getTitle(array $content)
    {
        return $content['title'];
    }

    public function getBreadcrumbs(array $content)
    {
        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('KomuKuYJB_Model_Category');
        return $categoryModel->getCategoryBreadcrumb($content);
    }

    public function getNavigationTab()
    {
        return 'classifieds';
    }

    public function formatHistory($string, XenForo_View $view)
    {
        $parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view)));
        return new XenForo_BbCode_TextWrapper($string, $parser);
    }

    public function revertToVersion(array $content, $revertCount, array $history, array $previous = null)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($content);
        $writer->set('description', $history['old_text']);

        if ($previous && $previous['edit_user_id'] == $content['user_id'])
        {
            $writer->set('last_update', $previous['edit_date']);
        }

        return $writer->save();
    }
}