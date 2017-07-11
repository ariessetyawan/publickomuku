<?php /*b1d35b7684366b8738412c4a351f28e0276d5e3b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Comment_Reply extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $params = &$this->_params;
        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

        $params['reply']['message'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
            $params['reply'], $bbCodeParser
        );

        return array(
            'comment' => $this->createTemplateObject('classifieds_item_comment_reply', array('comment' => $params['reply']))
        );
    }
}