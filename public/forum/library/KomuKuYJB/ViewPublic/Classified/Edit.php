<?php /*fceb4ceda186358358adf015c18f78cdee310bf3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Classified_Edit extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;
        $message = isset($params['classified']['description']) ? $params['classified']['description'] : '';

        $params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'description', $message, array(
                'extraClass' => 'NoAutoComplete',
                'autoSaveUrl' => empty($params['classified']['classified_id'])
                    ? XenForo_Link::buildPublicLink('classifieds/categories/save-draft', $params['category'])
                    : ''
            )
        );

        $bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

        if (!empty($params['customFields']))
        {
            foreach ($params['customFields'] as &$fields)
            {
                foreach ($fields as &$field)
                {
                    if ($field['field_type'] == 'bbcode')
                    {
                        $field['editorTemplateHtml'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
                            $this, 'custom_fields[' . $field['field_id'] . ']',
                            isset($field['field_value']) ? $field['field_value'] : '',
                            array(
                                'height' => '100px',
                                'extraClass' => 'NoAttachment NoAutoComplete'
                            )
                        );
                    }
                    elseif ($field['field_type'] == 'hint_text')
                    {
                        $field['hintTextHtml'] = new XenForo_BbCode_TextWrapper($field['hint_text'], $bbCodeParser);
                    }
                }
            }
        }
    }
}