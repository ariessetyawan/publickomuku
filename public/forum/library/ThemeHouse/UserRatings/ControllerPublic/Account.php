<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_ControllerPublic_Account extends XFCP_ThemeHouse_UserRatings_ControllerPublic_Account
{
    //Display ratings that this user has given.
    public function actionGivenRatings()
    {
        $userId = XenForo_Visitor::getUserId();

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        //Pagination.
        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = XenForo_Application::get('options')->ratingsPagination;

        //Get id of users being rated and visible ratings.
        $conditions = array(
            'from_user' => $userId,
            'active' => 1,
        );

        //Fetch ratings and pagination.
        $fetchOptions = array(
            'join' => ThemeHouse_UserRatings_Model_Ratings::FETCH_BOTH_FULL_USERS,
            'page' => $page,
            'perPage' => $perPage,
        );

        //Get all ratings for this user.
        $entries = $ratingModel->getRatings($conditions, $fetchOptions);

        //Count all ratings for this user.
        $count = $ratingModel->countRatings($conditions);

        //Register variables for use in our template.
        $viewParams = array(
            'user' => $userId,
            'entries' => $entries,
            'count' => $count,
            'page' => $page,
            'perPage' => $perPage,
        );

        return $this->_getWrapper(
            'account', 'givenratings',
            $this->responseView('XenForo_ViewPublic_Base', 'th_user_given_ratings', $viewParams)
        );
    }
}
