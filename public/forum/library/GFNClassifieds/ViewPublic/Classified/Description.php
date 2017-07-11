<?php /*e74e7a13300b139c8ef03804906de94318b030bf*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Classified_Description extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
        $params = &$this->_params;

        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'states' => array(
                'viewAttachments' => $params['classified']['canViewAttachments']
            ),
            'showSignature' => false,
            'messageKey' => 'description',
            'messageParsedKey' => 'description_parsed',
            'messageCacheVersionKey' => 'description_cache_version'
        );

        $params['descriptionHtml'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
            $params['classified'], $bbCodeParser, $bbCodeOptions
        );
    }
}