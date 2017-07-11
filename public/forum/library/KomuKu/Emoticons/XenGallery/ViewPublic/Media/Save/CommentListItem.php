<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_Save_CommentListItem extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_Save_CommentListItem
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderJson()
    {
        $this->attach('comment.message', $this->_params['comment']['user_id']);
        return parent::renderJson();
    }
}
