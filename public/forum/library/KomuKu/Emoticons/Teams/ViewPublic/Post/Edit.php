<?php

class KomuKu_Emoticons_Teams_ViewPublic_Post_Edit extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Post_Edit
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        $this->attach('post.message', $this->_params['post']['user_id']);
        return parent::renderHtml();
    }
}
