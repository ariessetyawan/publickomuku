<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_ControllerPublic_Ratings extends XenForo_ControllerPublic_Abstract
{
    /**
     * It shows all user ratings and rating stats.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        /* @var $statsModel ThemeHouse_UserRatings_Model_User */
        $statsModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_User');

        //Restrict groups from viewing ratings.
        if (!$ratingModel->canViewRatings()) {
            return $this->responseNoPermission();
        }

        //Get $options.
        $options = XenForo_Application::get('options');

        //Limit.
        $limit = $options->userLimit;

        //Pagination.
        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = $options->ratingsPagination;

        $conditions = array();

        //Fetch ratings and pagination.
        $fetchOptions = array(
            'join' => ThemeHouse_UserRatings_Model_Ratings::FETCH_BOTH_FULL_USERS,
            'page' => $page,
            'perPage' => $perPage,
        );

        //Count all ratings.
        $count = $ratingModel->countRatings($conditions);

        //Get ratings.
        $ratings = $ratingModel->getRatings($conditions, $fetchOptions);

        //Get edit/delete ratings permissions.
        foreach ($ratings as $ratingId => $rating) {
            $ratings[$ratingId]['canEdit'] = $ratingModel->canEditRating($rating);
            $ratings[$ratingId]['canDelete'] = $ratingModel->canDeleteRating($rating);
        }

        //Get most negative rated users.
        $negativeUsers = $statsModel->getUsers(array(
            'join' => ThemeHouse_UserRatings_Model_User::FETCH_USER,
            'order' => 'total',
            'direction' => 'desc',
            'limit' => $limit,
        ));

        foreach ($negativeUsers as $k => $user) {
            if ($user['rating'] >= $options->negativeThreshold) {
                unset($negativeUsers[$k]);
            }
        }

        //Get most positive rated users.
        $topUsers = $statsModel->getUsers(array(
            'join' => ThemeHouse_UserRatings_Model_User::FETCH_USER,
            'order' => 'total',
            'direction' => 'desc',
            'limit' => $limit,
        ));

        foreach ($topUsers as $k => $user) {
            if ($user['rating'] <= 1) {
                unset($topUsers[$k]);
            }
        }

        //Register variables for use in our template.
        $viewParams = array(
            'ratings' => $ratings,
            'topUsers' => $topUsers,
            'negativeUsers' => $negativeUsers,
            'stats' => $ratingModel->getRatingStats(),
            'canReport' => $ratingModel->canReport(),
            'count' => $count,
            'page' => $page,
            'perPage' => $perPage,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Index', 'th_rating_stats_index', $viewParams);
    }

    /**
     * It shows the form to edit a rating.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionEdit()
    {
        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        list($rating) = $ratingModel->prepareRating(array($rating));

        //Get permissions.
        if (!$ratingModel->canEditRating($rating)) {
            throw $this->getNoPermissionResponseException();
        }

        //Register variable for use in our template.
        $viewParams = array(
            'rating' => $rating,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Edit', 'th_rate_user_edit', $viewParams);
    }

    /**
     * It saves the edits made to a rating.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionSave()
    {
        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        //Filter data.
        $input = $this->_input->filter(array(
            'rating' => XenForo_Input::INT,
            'message' => XenForo_Input::STRING,
        ));

        /* @var $dw ThemeHouse_UserRatings_DataWriter_Ratings */
        $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');

        $dw->setExistingData($ratingId);

        $dw->bulkSet($input);

        //Make sure they choose a rating.
        if ($input['rating'] == '404') {
            $dw->error(new XenForo_Phrase('th_please_choose_rating'));
        }

        //Set up a maxium message length. Staff is excluded.	
        if (!empty(XenForo_Application::get('options')->requiredMessage) and empty($input['message']) and !XenForo_Visitor::getInstance()->is_admin and !XenForo_Visitor::getInstance()->is_moderator and !XenForo_Visitor::getInstance()->is_staff) {
            $dw->error(new XenForo_Phrase('th_please_enter_message'), 'message');
        }

        //Changes saved in the db.
        $dw->save();

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('ratings', array('rating_id' => $ratingId)));
    }

    /**
     * It deletes a rating.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionDelete()
    {
        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        list($rating) = $ratingModel->prepareRating(array($rating));

        //Delete rating permissions.
        if (!$ratingModel->canDeleteRating($rating)) {
            throw $this->getNoPermissionResponseException();
        }

        if ($this->isConfirmedPost()) {
            /* @var $dw ThemeHouse_UserRatings_DataWriter_Ratings */
            $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');
            $dw->setExistingData($ratingId);
            $dw->delete();

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('ratings', array('rating_id' => $ratingId)));
        }

        //Register variable for use in our template.
        $viewParams = array(
            'rating' => $rating,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Delete', 'th_rate_user_delete', $viewParams);
    }

    /**
     * It shows the report form.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionReport()
    {
        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        if ($this->_request->isPost()) {
            $message = $this->_input->filterSingle('message', XenForo_Input::STRING);
            if (!$message) {
                return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
            }

            $reportModel = XenForo_Model::create('XenForo_Model_Report');

            if (!$reportModel->reportContent('ratings', $rating, $message)) {
                return $this->responseError(new XenForo_Phrase('th_failed_to_report_rating'));
            }

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('ratings', $rating),
                new XenForo_Phrase('th_thank_you_for_reporting_this_rating')
            );
        }

        //Register variables for use in our template.
        $viewParams = array(
            'rating' => $rating,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Report', 'th_user_rating_report', $viewParams);
    }

    /**
     * It shows a dialog containing the permalink to a rating.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionPermalink()
    {
        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        //Register variables for use in our template.
        $viewParams = array(
            'rating' => $rating,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Permalink', 'th_ratings_permalink_template', $viewParams);
    }

    /**
     * It unapproves the rating.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionUnapprove()
    {
        //Get token
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

        //Get ratings.
        $ratingId = $this->_input->filterSingle('rating_id', XenForo_Input::UINT);
        $rating = $this->_getRatingOrError($ratingId);

        //Only admins can unapprove ratings.
        if (!XenForo_Visitor::getInstance()->is_admin) {
            throw $this->getNoPermissionResponseException();
        }

        $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');
        $dw->setExistingData($rating['rating_id']);
        $dw->set('active', 0);
        $dw->save();

        //Rating unapproved. Redirect to it.
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('ratings')
        );
    }

    /**
     * It shows the form for rating moderation.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionModerated()
    {
        //Only Super Admin(s) can approve/disapprove ratings. 
        if (!XenForo_Visitor::getInstance()->isSuperAdmin()) {
            throw $this->getNoPermissionResponseException();
        }

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        //Get moderated ratings.
        $conditions = array(
            'active' => 0,
        );

        //Fetch ratings.
        $fetchOptions = array(
            'join' => ThemeHouse_UserRatings_Model_Ratings::FETCH_BOTH_FULL_USERS,
        );

        //Get the unapproved ratings.
        $unapprovedRatings = $ratingModel->getRatings($conditions, $fetchOptions);

        //Return the empty list after all ratings are approved.
        if (!$unapprovedRatings) {
            return $this->responseMessage(new XenForo_Phrase('no_ratings_awaiting_approval'));
        }

        //Register the var for use in the template
        $viewParams = array(
            'unapproved' => $unapprovedRatings,
        );

        return $this->responseView('ThemeHouse_UserRatings_ViewPublic_Moderated', 'th_user_ratings_moderation', $viewParams);
    }

    /**
     * It updates the rating moderation queue.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionModeratedUpdate()
    {
        $this->_assertPostOnly();

        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings');

        $messagesInput = $this->_input->filterSingle('messages', XenForo_Input::ARRAY_SIMPLE);
        $messages = $ratingModel->getRatingByIds(array_keys($messagesInput));

        foreach ($messages as $message) {
            if (!isset($messagesInput[$message['rating_id']])) {
                continue;
            }

            $messageControl = $messagesInput[$message['rating_id']];

            if (empty($messageControl['action']) || $messageControl['action'] == 'none') {
                continue;
            }

            //Process ratings.
            $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings')->processRatingModeration(
                $message, $messageControl['action']
            );
        }

        //Redirect to the rating moderation center after the action.
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('ratings/moderated'),
            $this->getDynamicRedirect()
        );
    }

    /**
     * It shows users viewing the ratings page.
     *
     * @return XenForo_ControllerResponse_View
     */
    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('th_viewing_user_ratings', array('tabName' => XenForo_Application::get('options')->tabName));
    }

    /**
     * @return _getRatingOrError
     */
    protected function _getRatingOrError($ratingId)
    {
        $rating = $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings')->getRatingById($ratingId, array('join' => ThemeHouse_UserRatings_Model_Ratings::FETCH_BOTH_FULL_USERS));

        if (!$rating) {
            throw $this->responseException($this->responseNoPermission());
        }

        return $rating;
    }
}
