<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_CommentEditInline extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_CommentEditInline
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        $this->attach('comment.message', $this->_params['comment']['user_id']);
        return parent::renderHtml();
    }
}
