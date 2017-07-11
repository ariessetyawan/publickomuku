<?php

class KomuKu_Emoticons_Messenger_Output extends XFCP_KomuKu_Emoticons_Messenger_Output
{
    use KomuKu_Emoticons_Traits_Message;

    public function renderBbCode(array &$messages, XenForo_View $view)
	{
        $index = reset($messages);
        if(is_array($index)) {
            foreach($messages as &$message) {
                $message['message'] = KomuKu_Emoticons_String::attach($message['user_id'], $message['message']);
            }
        } else {
            $messages['message'] = KomuKu_Emoticons_String::attach($messages['user_id'], $messages['message']);
        }

        return parent::renderBbCode($messages, $view);
    }
}
