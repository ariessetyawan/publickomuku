<?php /*ce7c70f70e7939f5a002e2c8160891f048ab8c83*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_ControllerPublic_Attachment extends XFCP_KomuKuYJB_Extend_XenForo_ControllerPublic_Attachment
{
    public function actionDoUpload()
    {
        XenForo_Application::set(
            'KomuKuYJBAttachmentContentType',
            $this->_input->filterSingle('content_type', XenForo_Input::STRING)
        );

        return parent::actionDoUpload();
    }
}