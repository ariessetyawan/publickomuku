<?php

class AnonymousPosting_XenForo_ControllerPublic_Forum extends XFCP_AnonymousPosting_XenForo_ControllerPublic_Forum
{
    public function actionCreateThread()
    {
        return AnonymousPosting_Engine::prepareResponse($this, $this->_input, parent::actionCreateThread());
    }

    public function actionAddThread()
    {
        $GLOBALS['AnonymousPosting_XenForo_ControllerPublic_Forum::actionAddThread'] = $this;
        return parent::actionAddThread();
    }

    public function AnonymousPosting_actionAddThread(XenForo_DataWriter_DiscussionMessage $postDw)
    {
        $forum = $postDw->getExtraData(XenForo_DataWriter_DiscussionMessage_Post::DATA_FORUM);
        if (empty($forum)) {
            return;
        }

        AnonymousPosting_Engine::processAnonymousPosting($forum['node_id'], 0, $this, $postDw);
    }

}
