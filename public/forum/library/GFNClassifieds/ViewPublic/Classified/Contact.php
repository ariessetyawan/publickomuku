<?php /*1469cce96ae5da1bc19d7ed59101d4632490212e*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Classified_Contact extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        $params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'message', !empty($params['draft']) ? $params['draft']['message'] : '',
            array(
                'extraClass' => 'NoAutoComplete',
                'autoSaveUrl' => XenForo_Link::buildPublicLink('classifieds/contact/save-draft', $params['classified']),
                'json' => array(
                    'focus' => true
                )
            )
        );
    }
}