<?php /*b6c87d881badb89d714f16e495cc6c0199d9604b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Classified_Comment extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
        $params = &$this->_params;

        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        foreach ($params['comments'] as &$comment)
        {
            $comment['message'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
                $comment, $bbCodeParser
            );

            if (!empty($comment['replies']))
            {
                foreach ($comment['replies'] as &$reply)
                {
                    $reply['message'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
                        $reply, $bbCodeParser
                    );
                }
            }
        }

        if (!empty($params['classified']['canAddComment']))
        {
            $params['qrEditor'] = XenForo_ViewPublic_Helper_Editor::getQuickReplyEditor($this, 'message', '', array(
                'extraClass' => 'NoAttachment',
                'json' => array('placeholder' => new XenForo_Phrase('comment_placeholder'))
            ));
        }
    }
}