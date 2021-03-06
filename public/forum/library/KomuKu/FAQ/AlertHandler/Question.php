<?php

class KomuKu_FAQ_AlertHandler_Question extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        // alert_{contentType}_like
        // alert_{contentType}_answered
        return $model->getModelFromCache('KomuKu_FAQ_Model_Question')->getQuestionsByIds($contentIds);
    }
}
