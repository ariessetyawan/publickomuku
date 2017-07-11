<?php /*4aa34773dc7288761d92195feebb5cb9074a1787*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_TraderRating_LikeConfirmed extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $message = $this->_params['rating'];

        if (!empty($message['likes']))
        {
            $params = array(
                'message' => $message,
                'likesUrl' => XenForo_Link::buildPublicLink('classifieds/traders/ratings', $message)
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
            $output['term'] = new XenForo_Phrase('unlike');

            $output['cssClasses'] = array(
                'like' => '-',
                'unlike' => '+'
            );
        }
        else
        {
            $output['term'] = new XenForo_Phrase('like');

            $output['cssClasses'] = array(
                'like' => '+',
                'unlike' => '-'
            );
        }

        return $output;
    }
}