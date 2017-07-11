<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_View extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_View
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        if (!empty($this->_params['canAddComment']) && !empty($this->_params['draft']))
		{
            $this->attach('draft.message');
        }

        // Support all custom fields which use BBCode
        if(!empty($this->_params['media']['customFields']))
        {
            foreach($this->_params['media']['customFields'] as $fieldId => &$fieldValue)
            {
                if(!isset($this->_params['fieldsCache'][$fieldId]))
                {
                    continue;
                }

                $field = $this->_params['fieldsCache'][$fieldId];
                if($field['field_type'] !== 'bbcode')
                {
                    continue;
                }

                $fieldValue = KomuKu_Emoticons_String::attach($this->_params['media']['user_id'], $fieldValue);
            }
        }

        foreach($this->_params['comments'] as &$comment)
        {
            $comment['message'] = $this->attachArray($comment);
        }

        return parent::renderHtml();
    }
}
