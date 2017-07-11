<?php /*ff8ba541e71571d1e05c917f2c59cb2e82d99b2b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_ViewPublic_Attachment_DoUpload extends XFCP_KomuKuYJB_Extend_XenForo_ViewPublic_Attachment_DoUpload
{
    protected function _prepareAttachmentForJson(array $attachment)
    {
        return parent::_prepareAttachmentForJson($attachment) + array('slideUrl' => @$attachment['slideUrl'] ?: null);
    }
}