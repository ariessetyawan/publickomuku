<?php

trait KomuKu_Emoticons_Traits_Message
{
    public function attach($messageKey, $userId = null)
    {
        $userId = $userId ?: XenForo_Visitor::getUserId();
        $messageKeys = $this->_exploreMessageKeys($messageKey);

        if(empty($messageKeys))
        {
            throw new InvalidArgumentException('MessageKey could not be empty.');
        }

        if(count($messageKeys) == 2)
        {
            return $this->_renderTwoLevel($messageKeys[0], $messageKeys[1], $userId);
        }

        return $this->_renderOneLevel($messageKeys[0], $userId);
    }

    public function attachArray(array $array, $userKey = 'user_id', $messageKey = 'message')
    {
        return KomuKu_Emoticons_String::attach($array[$userKey], $array[$messageKey]);
    }

    protected function _renderOneLevel($one, $userId)
    {
        $this->_params[$one] = KomuKu_Emoticons_String::attach($userId, $this->_params[$one]);
    }

    protected function _renderTwoLevel($one, $two, $userId)
    {
        $this->_params[$one][$two] = KomuKu_Emoticons_String::attach($userId, $this->_params[$one][$two]);
    }

    protected function _exploreMessageKeys($messageKey)
    {
        return array_map('trim', explode('.', $messageKey));
    }
}
