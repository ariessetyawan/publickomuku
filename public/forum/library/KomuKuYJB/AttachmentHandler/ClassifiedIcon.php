<?php /*71f987ea51a2475eaf137d386327250363e6d30b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_AttachmentHandler_ClassifiedIcon extends XenForo_AttachmentHandler_Abstract
{
    protected $_contentIdKey = 'classified_id';

    protected $_contentRoute = 'classifieds/items';

    protected $_contentTypePhraseKey = 'classified_icon';

    protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('KomuKuYJB_Model_Category');
        return $categoryModel->canAddClassified(null, $null, $viewingUser);
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

        return $classifiedModel->canViewClassifiedAndContainer($classified, $category, $null, $viewingUser);
    }

    public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
    {
        $db->update(
            'kmk_classifieds_classified',
            array('featured_image_date' => 0),
            'classified_id = ' . $db->quote($attachment['content_id'])
        );
    }

    public function getAttachmentConstraints()
    {
        return $this->_getClassifiedModel()->getClassifiedIconConstraints();
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