<?php /*3536aa236912e575a854613e623e73316be27de5*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Comment_ReplyForm extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        $params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'message', '', array(
                'extraClass' => 'NoAttachment',
                'editorId' => 'replyMessage' . $params['comment']['comment_id']
            )
        );
    }
}