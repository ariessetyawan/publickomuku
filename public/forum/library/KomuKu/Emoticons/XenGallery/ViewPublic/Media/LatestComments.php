<?php

class KomuKu_Emoticons_XenGallery_ViewPublic_Media_LatestComments extends XFCP_KomuKu_Emoticons_XenGallery_ViewPublic_Media_LatestComments
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderJson()
    {
        foreach($this->_params['comments'] as &$comment)
        {
            $comment['message'] = $this->attachArray($comment);
        }

        return parent::renderJson();
    }
}
