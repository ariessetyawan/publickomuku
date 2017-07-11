<?php
/**
 * Copyright (c) komuku (komuku.com) 2015 to present.
 * All rights reserved.
 * @license All use is subject to the komuku License Agreement (https://www.komuku.com/community/products/license-agreement)
 * @author: komuku Team <support@komuku.com>
 */
class komuku_ProfilePostLimit_DataWriter_DiscussionMessage_ProfilePost extends XFCP_komuku_ProfilePostLimit_DataWriter_DiscussionMessage_ProfilePost
{
    protected function _getDefaultOptions()
    {
        $options = parent::_getDefaultOptions();
        $xfOptions = XenForo_Application::getOptions();
        $options[self::OPTION_MAX_MESSAGE_LENGTH] = $xfOptions->applMessageLimit;
        $options[self::OPTION_MAX_TAGGED_USERS] = $xfOptions->applTaggedUserLimit;

        return $options;
    }

    protected function _messagePreSave()
    {
        if ($this->get('user_id') == $this->get('profile_user_id') && $this->isChanged('message'))
        {
            // statuses are more limited than other posts
            $message = $this->get('message');
            $maxLength = XenForo_Application::getOptions()->applCharacterLimit;

            $message = preg_replace('/\r?\n/', ' ', $message);

            if (utf8_strlen($message) > $maxLength)
            {
                $this->error(new XenForo_Phrase('please_enter_message_with_no_more_than_x_characters', array('count' => $maxLength)), 'message');
            }

            $this->set('message', $message);
        }

        // do this auto linking after length counting
        /** @var $taggingModel XenForo_Model_UserTagging */
        $taggingModel = $this->getModelFromCache('XenForo_Model_UserTagging');

        $this->_taggedUsers = $taggingModel->getTaggedUsersInMessage(
            $this->get('message'), $newMessage, 'text'
        );
        $this->set('message', $newMessage);

        try {
            parent::_messagePreSave();
            if(!empty($this->_errors) && isset($this->_errors['message']) && $this->_errors['message'] == new XenForo_Phrase('please_enter_message_with_no_more_than_x_characters', array('count' => 140)))
            {
                unset($this->_errors['message']);
            }
        } catch (XenForo_Exception $e) {}
    }
}