<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_Edit extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_Edit
{
    public function renderHtml()
    {
        foreach ($this->_params['customFields'] AS $fieldId => &$fields)
		{
			foreach ($fields AS &$field)
			{
				if ($field['field_type'] == 'bbcode')
				{
					if(!isset($field['field_value']))
                    {
                        continue;
                    }

                    $field['field_value'] = KomuKu_Emoticons_String::attach(
                        $this->_params['media']['user_id'], $field['field_value']
                    );
				}
			}
		}

        return parent::renderHtml();
    }
}
