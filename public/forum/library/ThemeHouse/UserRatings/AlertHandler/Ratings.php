<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_AlertHandler_Ratings extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        $ratingModel = $model->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');
        $rating = $ratingModel->getRatingByIds($contentIds);
        $rating = $ratingModel->prepareRating($rating);

        return $rating;
    }

    protected function _getDefaultTemplateTitle($contentType, $action)
    {
        return 'th_alert_'.$contentType;
    }
}
