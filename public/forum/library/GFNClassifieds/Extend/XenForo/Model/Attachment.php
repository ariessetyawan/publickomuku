<?php /*eb1e0114a8b6feed0e638c6d3c24999627eb8652*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 10
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_Model_Attachment extends XFCP_GFNClassifieds_Extend_XenForo_Model_Attachment
{
    public function insertUploadedAttachmentData(XenForo_Upload $file, $userId, array $extra = array())
    {
        if (!XenForo_Application::isRegistered('GFNClassifiedsAttachmentContentType'))
        {
            XenForo_Application::set('GFNClassifiedsAttachmentContentType', false);
        }

        /** @var GFNClassifieds_Model_Classified $classifiedModel */
        $classifiedModel = $this->getModelFromCache('GFNClassifieds_Model_Classified');

        switch (XenForo_Application::get('GFNClassifiedsAttachmentContentType'))
        {
            case 'classified_icon':
                return $classifiedModel->insertUploadedClassifiedIconData($file, $userId, $extra);

            case 'classified_gallery':
                return $classifiedModel->insertUploadedGalleryImageData($file, $userId, $extra);

            default:
                return parent::insertUploadedAttachmentData($file, $userId, $extra);
        }
    }

    public function prepareAttachment(array $attachment, $fetchContentLink = false)
    {
        $return = parent::prepareAttachment($attachment, $fetchContentLink);

        if (!empty($attachment['slide_width']))
        {
            $return['slideUrl'] = $this->getModelFromCache('GFNClassifieds_Model_Classified')->getGallerySlideImageUrl($attachment);
        }

        return $return;
    }
}