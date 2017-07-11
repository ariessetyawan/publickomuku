<?php /*ba750dc9316970fa604604d46b9c2fce1170de28*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Comment_Insert extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $params = &$this->_params;
        $output = array();
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

            $output[] = $this->createTemplateObject('classifieds_item_comment_single', array(
                'comment' => $comment
            ));
        }

        return array(
            'comments' => $output,
            'lastCommentDate' => $params['lastCommentDate'],
            'message' => new XenForo_Phrase('your_message_has_been_posted')
        ) + $this->_renderer->getDefaultOutputArray(
            get_class($this), $params, $this->_templateName
        );
    }
}