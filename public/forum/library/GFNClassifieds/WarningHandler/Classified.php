<?php /*0067e5c6788c4b0276e9ad291d2a5073b72adc90*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_WarningHandler_Classified extends XenForo_WarningHandler_Abstract
{
    protected function _canView(array $content, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canViewClassifiedAndContainer($content, $content, $null, $viewingUser, $content['permissions']);
    }

    protected function _canWarn($userId, array $content, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canWarnClassified($content, $content, $null, $viewingUser, $content['permissions']);
    }

    protected function _canDeleteContent(array $content, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canDeleteClassified($content, $content, 'soft', $null, $viewingUser, $content['permissions']);
    }

    protected function _getContent(array $contentIds, array $viewingUser)
    {
        $model = $this->_getClassifiedModel();

        $classifieds = $model->getClassifiedsByIds($contentIds, array(
            'join' => $model::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        return $model->unserializePermissionsInList($classifieds, 'category_permission_cache');
    }

    public function getContentTitle(array $content)
    {
        return $content['title'];
    }

    public function getContentDetails(array $content)
    {
        return $content['description'];
    }

    public function getContentUrl(array $content, $canonical = false)
    {
        return XenForo_Link::buildPublicLink(($canonical ? 'canonical:' : '') . 'classifieds', $content);
    }

    public function getContentTitleForDisplay($title)
    {
        return new XenForo_Phrase('classified_x', array('title' => $title));
    }

    protected function _warn(array $warning, array $content, $publicMessage, array $viewingUser)
    {
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
        if ($writer->setExistingData($content))
        {
            $writer->set('warning_id', $warning['warning_id']);
            $writer->set('warning_message', $publicMessage);
            $writer->save();
        }
    }

    protected function _reverseWarning(array $warning, array $content)
    {
        if ($content)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            if ($writer->setExistingData($content))
            {
                $writer->set('warning_id', 0);
                $writer->set('warning_message', '');
                $writer->save();
            }
        }
    }

    protected function _deleteContent(array $content, $reason, array $viewingUser)
    {
        $this->_getClassifiedModel()->deleteClassified($content['classified_id'], 'soft', array('reason' => $reason));
        XenForo_Model_Log::logModeratorAction('classified', $content, 'delete_soft', array('reason' => $reason));
        XenForo_Helper_Cookie::clearIdFromCookie($content['classified_id'], 'inlinemod_classifieds');
    }

    public function canPubliclyDisplayWarning()
    {
        return true;
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return XenForo_Model::create('GFNClassifieds_Model_Classified');
    }
}