<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_ControllerPublic_Member extends XFCP_ThemeHouse_UserRatings_ControllerPublic_Member
{
    /**
     * It hides the retaing link and stats from users with no permisisons.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        $parent = parent::actionIndex();

        if (isset($parent->params['user'])) {
            /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
           $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

           // Register variable for use in our template.
           $parent->params['viewRatings'] = $ratingModel->canViewRatings();
        }

        return $parent;
    }

    /**
     * It shows the form to rate an user.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionAddRating()
    {
        //Get $user.
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $user = $this->getHelper('UserProfile')->getUserOrError($userId);

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        //Can add rating permissions.
        if (!$ratingModel->canRate()) {
            throw $this->getNoPermissionResponseException();
        }

        if (XenForo_Visitor::getInstance()->user_id == $user['user_id']) {
            return $this->responseError(new XenForo_Phrase('th_no_rating_own_profile'));
        }

        //Prevent abuse of the rating system by setting up a daily limit.
        if (!$ratingModel->dailyLimit($user, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        //Register variable for use in our template.
        $viewParams = array(
            'user' => $user,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_AddRating', 'th_rate_this_user', $viewParams);
    }

    /**
     * It adds the ratings in db.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionSaveRating()
    {
        //Action via $_POST only.
        $this->_assertPostOnly();

        //Guests not allowed to rate.
        $this->_assertRegistrationRequired();

        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        //Filter data.
        $input = $this->_input->filter(array(
            'from_user_id' => XenForo_Input::UINT,
            'to_user_id' => XenForo_Input::UINT,
            'rating' => XenForo_Input::INT,
            'message' => XenForo_Input::STRING,
        ));

        /* @var $dw ThemeHouse_UserRatings_DataWriter_Ratings */
        $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');

        //Get the user that is rating.
        $fromUser = $userModel->getUserById($input['from_user_id']);
        $dw->set('from_username', $fromUser['username']);

        //Get the user that is being rated.
        $toUser = $userModel->getUserById($input['to_user_id']);
        $dw->set('to_username', $toUser['username']);

        //Moderate ratings for this group.
        $gidjusi = XenForo_Permission::hasPermission(XenForo_Visitor::getInstance()->permissions, 'ratings', 'ratingsModerate');
        $active = !$gidjusi ? '1' : '0';

        $dw->set('active', $active);

        $dw->bulkSet($input);

        //Make sure they choose a rating.
        if ($input['rating'] == '404') {
            $dw->error(new XenForo_Phrase('th_please_choose_rating'));
        }

        //Set up a maxium message length. Staff is excluded.	
        if (!empty(XenForo_Application::get('options')->requiredMessage) and empty($input['message']) and !XenForo_Visitor::getInstance()->is_admin and !XenForo_Visitor::getInstance()->is_moderator and !XenForo_Visitor::getInstance()->is_staff) {
            $dw->error(new XenForo_Phrase('th_please_enter_message'), 'message');
        }

        $dw->preSave();

        //Set up flooding restriction.
        if (!$dw->hasErrors()) {
            $this->assertNotFlooding('message-'.$input['from_user_id'].'-'.$input['to_user_id'], XenForo_Application::get('options')->floodCheck);
        }

        //Add the data in db.
        $dw->save();

        $data = $dw->getMergedData();

        //Send alert to rated users.
        if (XenForo_Model_Alert::userReceivesAlert($toUser, 'ratings', 'insert')) {
            XenForo_Model_Alert::alert($input['to_user_id'], $input['from_user_id'], $fromUser['username'], 'ratings', $data['rating_id'], 'insert');
        }

        //Rating submitted.
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('members', array('user_id' => $data['to_user_id'])),
            new XenForo_Phrase('th_your_rating_has_been_successfully_submitted')
        );
    }

    /**
     * It adds the link to rate users in their member cards.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionCard()
    {
        $parent = parent::actionCard();

        if ($parent instanceof XenForo_ControllerResponse_View) {
            /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
           $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

           //Register variable for use in our template.
           $parent->params['viewRatings'] = $ratingModel->canViewRatings();
            $parent->params['canRate'] = $ratingModel->canRate();
        }

        return $parent;
    }

    /**
     * It shows all users who rated in users profiles.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionRatingsView()
    {
        //Get $user.
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $user = $this->getHelper('UserProfile')->getUserOrError($userId);

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        //Can view ratings permissions.
        if (!$ratingModel->canViewRatings()) {
            throw $this->getNoPermissionResponseException();
        }

        //Pagination.
        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = XenForo_Application::get('options')->ratingsPagination;

        //Get id of users being rated and visible ratings.
        $conditions = array(
            'to_user' => $userId,
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
            'user' => $user,
            'entries' => $entries,
            'count' => $count,
            'page' => $page,
            'perPage' => $perPage,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_RatingsView', 'th_users_ratings_view', $viewParams);
    }

    /**
     * It shows rating stats in users profiles.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionMember()
    {
        $parent = parent::actionMember();

        if (isset($parent->params['user'])) {
            //Get $user.
           $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
            $user = $this->getHelper('UserProfile')->getUserOrError($userId);

           /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
           $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

           /* @var $statsModel ThemeHouse_UserRatings_Model_User */
           $statsModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_User');

            //Get rated user.
            $ratedUser = $statsModel->getUserById($userId);

            if ($ratedUser) {
                $user = array_merge($user, $ratedUser);
            } else {
                $user['userNotExist'] = true;
            }

            //Get id of users being rated.
            $conditions = array(
              'to_user' => $userId,
            );

           //Count all ratings for this user.
           $count = $ratingModel->countRatings($conditions);

           //Register variables for use in our template.
           $parent->params['user'] = $user;
            $parent->params['count'] = $count;
            $parent->params['stats'] = $statsModel->getUserRatingMonthlyStats($userId);
            $parent->params['viewRatings'] = $ratingModel->canViewRatings();
            $parent->params['canRate'] = $ratingModel->canRate();
        }

        return $parent;
    }
}
