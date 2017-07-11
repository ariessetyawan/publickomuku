<?php

class KomuKu_FAQ_NewsFeedHandler_Question extends XenForo_NewsFeedHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, array $viewingUser)
    {
        return $model->getModelFromCache('KomuKu_FAQ_Model_Question')->getQuestionsByIds($contentIds);
    }
}
