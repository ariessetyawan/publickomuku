<?php /*0c258ba6df2c96b293c8a957f67357887f0777ad*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Classified_PageWrapper extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
        $params = &$this->_params;

        if (!empty($params['classified']['canAddComment']))
        {
            $params['qrEditor'] = XenForo_ViewPublic_Helper_Editor::getQuickReplyEditor($this, 'message', '', array(
                'extraClass' => 'NoAttachment',
                'json' => array('placeholder' => new XenForo_Phrase('comment_placeholder'))
            ));
        }

        if (!empty($params['comments']))
        {
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
        }
    }
}