<?php /*5b3c622243e9fe2dea127fbb49f09032fa3e3d2c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewAdmin_Field_Edit extends XenForo_ViewAdmin_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        $params['hintTextEditorHtml'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'hint_text', $params['field']['hint_text'], array('height' => '100px')
        );
    }
}