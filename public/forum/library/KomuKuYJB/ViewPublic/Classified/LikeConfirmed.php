<?php /*e70737a4e596d1625ddd94e6116cb0ba21d13beb*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Classified_LikeConfirmed extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $message = $this->_params['classified'];

        if (!empty($message['likes']))
        {
            $params = array(
                'message' => $message,
                'likesUrl' => XenForo_Link::buildPublicLink('classifieds/likes', $message)
            );

            $output = $this->_renderer->getDefaultOutputArray(get_class($this), $params, 'likes_summary');
        }
        else
        {
            $output = array('templateHtml' => '', 'js' => '', 'css' => '');
        }

        $output += self::getLikeViewParams($this->_params['liked']);

        return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
    }

    public static function getLikeViewParams($liked)
    {
        $output = array();

        if ($liked)
        {
            $output['term'] = new XenForo_Phrase('unlike_this_classified');

            $output['cssClasses'] = array(
                'like' => '-',
                'unlike' => '+'
            );
        }
        else
        {
            $output['term'] = new XenForo_Phrase('like_this_classified');

            $output['cssClasses'] = array(
                'like' => '+',
                'unlike' => '-'
            );
        }

        return $output;
    }
}