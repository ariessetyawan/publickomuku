<?php

class AnonymousPosting_XenForo_Model_Session extends XFCP_AnonymousPosting_XenForo_Model_Session
{
    public function addSessionActivityDetailsToList(array $activities)
    {
        /** @var AnonymousPosting_XenForo_Model_Forum $forumModel */
        $forumModel = $this->getModelFromCache('XenForo_Model_Forum');

        $forumModel->AnonymousPosting_checkSeeUserPermission(true);
        $response = parent::addSessionActivityDetailsToList($activities);
        $forumModel->AnonymousPosting_checkSeeUserPermission(false);

        return $response;
    }
}
