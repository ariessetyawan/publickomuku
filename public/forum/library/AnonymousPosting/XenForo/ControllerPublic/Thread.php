<?php

class AnonymousPosting_XenForo_ControllerPublic_Thread extends XFCP_AnonymousPosting_XenForo_ControllerPublic_Thread
{
    public function actionIndex()
    {
        return AnonymousPosting_Engine::prepareResponse($this, $this->_input, parent::actionIndex());
    }

    public function actionReply()
    {
        return AnonymousPosting_Engine::prepareResponse($this, $this->_input, parent::actionReply());
    }

    public function actionAddReply()
    {
        $GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Thread::actionAddReply'] = $this;
        return parent::actionAddReply();
    }

    public function AnonymousPosting_actionAddReply(XenForo_DataWriter_DiscussionMessage_Post $dw)
    {
        $forum = $dw->getExtraData(XenForo_DataWriter_DiscussionMessage_Post::DATA_FORUM);
        if (empty($forum['node_id'])) {
            return;
        }

        AnonymousPosting_Engine::processAnonymousPosting($forum['node_id'], $dw->get('thread_id'), $this, $dw);
    }

    protected function _getDefaultViewParams(
        array $forum,
        array $thread,
        array $posts,
        $page = 1,
        array $viewParams = array()
    ) {
        $viewParams = parent::_getDefaultViewParams($forum, $thread, $posts, $page, $viewParams);

        $viewParams['AnonymousPosting_canReveal'] =
            XenForo_Visitor::getInstance()->hasPermission('general', 'anonymous_posting_reveal');

        return $viewParams;
    }

}
