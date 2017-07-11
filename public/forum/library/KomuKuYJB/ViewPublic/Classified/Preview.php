<?php /*02c8e64996cb6ed603c86f0625e91ae903569fa5*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Classified_Preview extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        if (isset($params['classified']))
        {
            $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Text'));
            $params['descriptionParsed'] = $bbCodeParser->render($params['classified']['description']);
        }
        elseif (isset($params['description']))
        {
            $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
            $params['descriptionParsed'] = new XenForo_BbCode_TextWrapper($params['description'], $bbCodeParser);
        }
    }
}