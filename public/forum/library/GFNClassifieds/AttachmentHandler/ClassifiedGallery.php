<?php /*c6a065a9917b5ce11e153bf12b38921bbd1297d9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_AttachmentHandler_ClassifiedGallery extends XenForo_AttachmentHandler_Abstract
{
    protected $_contentIdKey = 'classified_id';

    protected $_contentRoute = 'classifieds/items';

    protected $_contentTypePhraseKey = 'classified_gallery_image';

    protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canUploadAndManageGalleryImage($null, $viewingUser);
    }

    /**
     * Determines if the specified user can view the given attachment.
     *
     * @param array $attachment Attachment to view
     * @param array $viewingUser Viewing user array
     *
     * @return boolean
     */
    protected function _canViewAttachment(array $attachment, array $viewingUser)
    {
        $classifiedModel = $this->_getClassifiedModel();

        $classified = $classifiedModel->getClassifiedById($attachment['content_id']);
        if (!$classified)
        {
            return false;
        }

        /** @var GFNClassifieds_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('GFNClassifieds_Model_Category');
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

        return $classifiedModel->canViewClassifiedAndContainer($classified, $category, $null, $viewingUser);
    }

    /**
     * Behavior to carry out after deleting an attachment (such as reducing an
     * attachment count on the content). This is only called when the attachment
     * has been associated with particular content (not just uploaded unassociated).
     *
     * @param array $attachment Attachment that has been deleted
     * @param Zend_Db_Adapter_Abstract $db DB object
     */
    public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
    {
        $db->query(
            'UPDATE kmk_classifieds_classified
            SET gallery_count = IF(gallery_count > 0, gallery_count - 1, 0)
            WHERE classified_id = ?', $attachment['content_id']
        );
    }

    public function getAttachmentConstraints()
    {
        return $this->_getClassifiedModel()->getGalleryImageConstraints();
    }

    /**
     * @return GFNClassifieds_Model_Classified
     * @throws XenForo_Exception
     */
    protected function _getClassifiedModel()
    {
        return XenForo_Model::create('GFNClassifieds_Model_Classified');
    }
}