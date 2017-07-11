<?php

class KomuKu_Emoticons_Teams_ViewPublic_Team_Extra extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Team_Extra
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        $this->attach('team.about', $this->_params['team']['user_id']);
        return parent::renderHtml();
    }
}
