<?php /*ede29cf8c5a17f8f649b858023f7383742436555*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Helper_Misc
{
    public static function getSocialShareLinks($title, $link)
    {
        $title = urlencode($title);
        $link = urlencode($link);

        return array(
            'email' => array(
                'title' => new XenForo_Phrase('email'),
                'class' => 'email',
                'href' => sprintf('mailto:?subject=%s&body=%s', $title, $link),
                'styleProperty' => 'classifiedsShareIconEmail'
            ),
            'twitter' => array(
                'title' => 'Twitter',
                'class' => 'twitter',
                'href' => sprintf(
                        'https://twitter.com/intent/tweet?original_referer=%s&url=%s&text=%s',
                        $link, $link, $title
                    ) . (XenForo_Application::getOptions()->get('twitter', 'via') ? '&via=' . XenForo_Application::getOptions()->get('twitter', 'via') : ''),
                'styleProperty' => 'classifiedsShareIconTwitter'
            ),
            'facebook' => array(
                'title' => 'Facebook',
                'class' => 'facebook SocialPopup',
                'href' => sprintf('https://facebook.com/share.php?u=%s', $link),
                'styleProperty' => 'classifiedsShareIconFacebook'
            ),
            'google' => array(
                'title' => 'Google+',
                'class' => 'google SocialPopup',
                'href' => sprintf('https://plus.google.com/u/0/share?url=%s', $link),
                'styleProperty' => 'classifiedsShareIconGoogle'
            ),
            'tumblr' => array(
                'title' => 'Tumblr',
                'class' => 'tumblr SocialPopup',
                'href' => sprintf('https://www.tumblr.com/share/link?url=%s', $link),
                'styleProperty' => 'classifiedsShareIconTumblr'
            ),
            'reddit' => array(
                'title' => 'reddit',
                'class' => 'reddit SocialPopup',
                'href' => sprintf('http://reddit.com/submit?url=%s', $link),
                'styleProperty' => 'classifiedsShareIconReddit'
            ),
            'digg' => array(
                'title' => 'digg',
                'class' => 'digg SocialPopup',
                'href' => sprintf('http://digg.com/submit?&url=%s', $link),
                'styleProperty' => 'classifiedsShareIconDigg'
            ),
            'vk' => array(
                'title' => 'vk',
                'class' => 'vk SocialPopup',
                'href' => sprintf('http://vk.com/share.php?url=%s', $link),
                'styleProperty' => 'classifiedsShareIconVk'
            )
        );
    }
}