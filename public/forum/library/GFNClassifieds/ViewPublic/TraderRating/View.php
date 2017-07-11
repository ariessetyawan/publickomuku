<?php /*1e0b44232145511fd71c0f0dc3a01b5da9860495*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_TraderRating_View extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'showSignature' => false,
            'messageKey' => 'message',
            'messageParsedKey' => 'message_parsed',
            'messageCacheVersionKey' => 'message_cache_version'
        );

        $params['messageHtml'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
            $params['rating'], $bbCodeParser, $bbCodeOptions
        );

        if (!empty($params['rating']['criteriaFeedbacks']))
        {
            XenForo_ViewPublic_Helper_Message::bbCodeWrapMessages(
                $params['rating']['criteriaFeedbacks'], $bbCodeParser, $bbCodeOptions
            );
        }
    }
}