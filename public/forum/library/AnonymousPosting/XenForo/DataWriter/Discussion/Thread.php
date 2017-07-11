<?php

class AnonymousPosting_XenForo_DataWriter_Discussion_Thread extends XFCP_AnonymousPosting_XenForo_DataWriter_Discussion_Thread
{
    protected function _preSaveFirstMessageDw()
    {
        if (!empty($this->_firstMessageDw)
            && !empty($GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Forum::actionAddThread'])
        ) {
            /** @var AnonymousPosting_XenForo_ControllerPublic_Forum $controller */
            $controller = $GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Forum::actionAddThread'];
            $controller->AnonymousPosting_actionAddThread($this->_firstMessageDw);

            if ($this->_firstMessageDw->getOption(AnonymousPosting_XenForo_DataWriter_DiscussionMessage_Post::OPTION_IS_ANONYMOUS)) {
                $this->bulkSet(array(
                    'user_id' => $this->_firstMessageDw->get('user_id'),
                    'username' => $this->_firstMessageDw->get('username'),
                ));

                if ($this->isInsert()) {
                    $this->bulkSet(array(
                        'last_post_user_id' => $this->_firstMessageDw->get('user_id'),
                        'last_post_username' => $this->_firstMessageDw->get('username'),
                    ));
                }
            }
        }

        parent::_preSaveFirstMessageDw();
    }

}
