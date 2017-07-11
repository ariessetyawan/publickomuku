<?php /*86694fd87f2289712ae586c93af9c202f6adefc3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Comment_Edit extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;
        $message = $params['comment']['message'];

        $params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'message', $message, array(
                'extraClass' => 'NoAttachment',
                'editorId' => 'message' . $params['comment']['comment_id']
            )
        );
    }
}