<?php

class KomuKu_Emoticons_Teams_ViewPublic_Post_Show extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Post_Show
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        $this->attach('post.message', $this->_params['post']['user_id']);
        return parent::renderHtml();
    }

    public function renderJson()
    {
        $this->attach('post.message', $this->_params['post']['user_id']);
        return parent::renderJson();
    }
}
