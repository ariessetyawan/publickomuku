<?php /*aa133aba6ffa06d6a25e2da111421010064ea1c5*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Comment_LikeConfirmed extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $message = $this->_params['comment'];

        if (!empty($message['likes']))
        {
            $params = array(
                'message' => $message,
                'likesUrl' => XenForo_Link::buildPublicLink('classifieds/comments/likes', $message)
            );

            $output = $this->_renderer->getDefaultOutputArray(get_class($this), $params, 'likes_summary');
        }
        else
        {
            $output = array('templateHtml' => '', 'js' => '', 'css' => '');
        }

        $output += XenForo_ViewPublic_Helper_Like::getLikeViewParams($this->_params['liked']);

        return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
    }
}