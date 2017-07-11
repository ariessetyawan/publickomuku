<?php /*18f9e971488164cb4e6de22cf901ecb40eebfe62*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Comment_Replies extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $params = &$this->_params;
        $comments = array();

        if ($params['comment']['first_reply_date'] < $params['firstReplyShown']['post_date'])
        {
            $comments[] = $this->createTemplateObject(
                'classifieds_comment_replies_before',
                array(
                    'comment' => $params['comment'],
                    'firstReplyShown' => $params['firstReplyShown']
                )
            );
        }

        foreach ($params['replies'] as $comment)
        {
            $comments[] = $this->createTemplateObject(
                'classifieds_item_comment_reply',
                array(
                    'comment' => $comment
                )
            );
        }

        return array('comments' => $comments);
    }
}