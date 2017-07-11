<?php /*f02548a7cf8441200e3a4f1169528b1bcc362077*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Comment_Show extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;
        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

        $params['comment']['message'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
            $params['comment'], $bbCodeParser
        );

        if (!empty($params['comment']['replies']))
        {
            foreach ($params['comment']['replies'] as &$reply)
            {
                $reply['message'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
                    $reply, $bbCodeParser
                );
            }
        }
    }
}