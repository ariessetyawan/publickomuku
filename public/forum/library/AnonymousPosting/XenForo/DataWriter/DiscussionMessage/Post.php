<?php

class AnonymousPosting_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_AnonymousPosting_XenForo_DataWriter_DiscussionMessage_Post
{
    const OPTION_IS_ANONYMOUS = '_AnonymousPosting_isAnonymous';

    protected function _getDefaultOptions()
    {
        $options = parent::_getDefaultOptions();

        $options[self::OPTION_IS_ANONYMOUS] = false;

        return $options;
    }

    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['kmk_post']['anonymous_posting_real_user_id'] = array('type' => self::TYPE_UINT);
        $fields['kmk_post']['anonymous_posting_real_username'] = array('type' => self::TYPE_STRING);

        return $fields;
    }

    protected function _messagePreSave()
    {
        parent::_messagePreSave();

        if (!empty($GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Thread::actionAddReply'])) {
            /** @var AnonymousPosting_XenForo_ControllerPublic_Thread $controller */
            $controller = $GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Thread::actionAddReply'];
            $controller->AnonymousPosting_actionAddReply($this);
        }
    }

}
