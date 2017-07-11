<?php

class KomuKu_Emoticons_Teams_ViewPublic_Ajax_WallPost extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Ajax_WallPost
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderHtml()
    {
        foreach($this->_params['posts'] as &$post)
        {
            $post['message'] = $this->attachArray($post);
        }
        // release some variables
        unset($post);

        return parent::renderHtml();
    }
}
