<?php /*50a1514ea4fa48c539eb9bc92a118605b45cbaee*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_AttachmentHandler_Classified extends XenForo_AttachmentHandler_Abstract
{
    protected $_contentIdKey = 'classified_id';

    protected $_contentRoute = 'classifieds/items';

    protected $_contentTypePhraseKey = 'classified';

    protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canUploadAndManageAttachment($null, $viewingUser);
    }

    protected function _canViewAttachment(array $attachment, array $viewingUser)
    {
        $classifiedModel = $this->_getClassifiedModel();

        $classified = $classifiedModel->getClassifiedById($attachment['content_id']);
        if (!$classified)
        {
            return false;
        }

        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('KomuKuYJB_Model_Category');
        $category = $categoryModel->getCategoryById($classified['category_id'], array(
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        if (!$category)
        {
            return false;
        }

        $categoryModel->setCategoryPermCache(
            $viewingUser['permission_combination_id'],
            $classified['category_id'],
            $category['category_permission_cache']
        );

        if (!$classifiedModel->canViewClassifiedAndContainer($classified, $category, $null, $viewingUser))
        {
            return false;
        }

        return $categoryModel->hasPermission('viewAttach', $category, $viewingUser);
    }

    public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
    {
        $db->query(
            'UPDATE kmk_classifieds_classified
            SET attach_count = IF(attach_count > 0, attach_count - 1, 0)
            WHERE classified_id = ?', $attachment['content_id']
        );
    }

    public function getAttachmentConstraints()
    {
        return $this->_getClassifiedModel()->getAttachmentConstraints();
    }

    /**
     * @return KomuKuYJB_Model_Classified
     * @throws XenForo_Exception
     */
    protected function _getClassifiedModel()
    {
        return XenForo_Model::create('KomuKuYJB_Model_Classified');
    }
}