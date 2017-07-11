<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_CommentEdit extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_CommentEdit
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        $this->attach('comment.message', $this->_params['comment']['user_id']);
        return parent::renderHtml();
    }
}
