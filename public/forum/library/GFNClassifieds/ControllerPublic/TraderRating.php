<?php /*9c49e0fdbe17af86166c46edc24f050b161c5a5a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_TraderRating extends GFNClassifieds_ControllerPublic_Abstract
{
    /**
     * @var array
     */
    protected $_trader;

    protected function _preDispatch($action)
    {
        switch (strtolower($action))
        {
            case 'add':
                break;

            default:
                $this->_trader = $this->getContentHelper()->getTraderOrError();
                break;
        }
    }

    public function actionIndex()
    {
        if ($this->_input->filterSingle('feedback_id', XenForo_Input::UINT))
        {
            return $this->responseReroute(__CLASS__, 'view');
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
            $this->_buildLink('classifieds/traders', $this->_trader)
        );
    }

    public function actionView()
    {
        list ($rating, $reviewer, $reviewee) = $this->_getRatingRelatedDetails();

        if (empty($rating['parent_feedback_id']))
        {
            $isParentFeedback = true;
            $feedbackResponse = $this->models()->traderRating()->getTraderRatingByParentFeedbackId($rating['feedback_id']);
            $parentFeedback = false;
        }
        else
        {
            $isParentFeedback = false;
            $feedbackResponse = false;
            $parentFeedback = $this->models()->traderRating()->getTraderRatingById($rating['parent_feedback_id']);
        }

        if ($rating['classified_id'])
        {
            $classified = $this->models()->classified()->getClassifiedById($rating['classified_id'], array(
                'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY,
                'permissionCombinationId' => XenForo_Visitor::getInstance()->get('permission_combination_id')
            ));
        }
        else
        {
            $classified = false;
        }

        $viewParams = array(
            'rating' => $rating,

            'reviewer' => $reviewer,
            'reviewee' => $reviewee,
            'classified' => $classified ? $this->models()->classified()->prepareClassified($classified, $classified) : false,

            'isParentFeedback' => $isParentFeedback,
            'feedbackResponse' => $feedbackResponse ? $this->models()->traderRating()->prepareTraderRating($feedbackResponse) : false,
            'parentFeedback' => $parentFeedback ? $this->models()->traderRating()->prepareTraderRating($parentFeedback) : false,

            'canViewIps' => $this->models()->user()->canViewIps()
        );

        return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_View', 'classifieds_trader_rating_view', $viewParams);
    }

    public function actionAdd()
    {
        $this->_assertRegistrationRequired();
        $visitor = XenForo_Visitor::getInstance();

        if (XenForo_Application::isRegistered('classifiedUserRatingClassified'))
        {
            $classified = XenForo_Application::get('classifiedUserRatingClassified');
        }
        else
        {
            $classified = false;
        }

        if (XenForo_Application::isRegistered('classifiedUserRatingUser'))
        {
            $user = XenForo_Application::get('classifiedUserRatingUser');
        }
        else
        {
            $user = false;
        }

        if (!$classified && !$user)
        {
            return $this->responseNoPermission();
        }

        if (!$this->models()->traderRating()->canAddTraderRating($classified, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($user && $user['user_id'] == $visitor['user_id'])
        {
            throw $this->getErrorOrNoPermissionResponseException('you_cannot_rate_yourself');
        }

        return $this->_getAddEditResponse(array(), array(
            'user' => $user,
            'classified' => $classified
        ));
    }

    public function actionEdit()
    {
        list ($rating, , $reviewee) = $this->_getRatingRelatedDetails();

        if (!$this->models()->traderRating()->canEditTraderRating($rating, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $viewParams = array(
            'user' => $reviewee
        );

        if (!empty($rating['classified_id']))
        {
            $viewParams['classified'] = $this->models()->classified()->getClassifiedById($rating['classified_id'], array(
                'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
            ));
        }

        return $this->_getAddEditResponse($rating, $viewParams);
    }

    public function actionRespond()
    {
        list ($rating, $reviewer) = $this->_getRatingRelatedDetails();

        if (!$this->models()->traderRating()->canRespondToTraderRating($rating, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $viewParams = array(
            'user' => $reviewer,
            'parentRating' => $rating
        );

        if (!empty($rating['classified_id']))
        {
            $viewParams['classified'] = $this->models()->classified()->getClassifiedById($rating['classified_id'], array(
                'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
            ));
        }

        return $this->_getAddEditResponse(array(), $viewParams);
    }

    protected function _getAddEditResponse(array $rating, array $viewParams = array())
    {
        $user = isset($viewParams['user']) ? $viewParams['user'] : false;
        $classified = isset($viewParams['classified']) ? $viewParams['classified'] : false;

        $criterias = $this->models()->ratingCriteria()->getRatingCriteriasForEdit(
            empty($classified['category_id']) ? 0 : $classified['category_id'],
            empty($rating['feedback_id']) ? 0 : $rating['feedback_id']
        );

        $criterias = $this->models()->ratingCriteria()->prepareRatingCriterias($criterias);

        $viewParams += array(
            'rating' => $rating,
            'criterias' => $criterias
        );

        return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Edit', 'classifieds_trader_rating_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $visitor = XenForo_Visitor::getInstance();

        $input = $this->_input->filter(array(
            'parent_rating_id' => XenForo_Input::UINT,
            'classified_id' => XenForo_Input::UINT,
            'user_id' => XenForo_Input::UINT,
            'rating' => XenForo_Input::INT,
            'review' => XenForo_Input::STRING
        ));

        $user = $this->models()->user()->getUserById($input['user_id']);
        if (!$user)
        {
            throw $this->getErrorOrNoPermissionResponseException('selected_trader_not_found');
        }

        if ($user['user_id'] == $visitor['user_id'])
        {
            throw $this->getErrorOrNoPermissionResponseException('you_cannot_rate_yourself');
        }

        if (!$input['rating'] && $this->_input->filterSingle('rating', XenForo_Input::STRING) !== '0')
        {
            throw $this->getErrorOrNoPermissionResponseException('please_select_one_of_three_ratings');
        }

        if ($input['classified_id'])
        {
            $classified = $this->models()->classified()->getClassifiedById($input['classified_id'], array(
                'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
            ));
        }
        else
        {
            $classified = false;
        }

        $data = array(
            'classified_id' => $classified ? $classified['classified_id'] : 0,
            'parent_feedback_id' => $input['parent_rating_id'],
            'user_id' => $visitor['user_id'],
            'username' => $visitor['username'],
            'for_user_id' => $user['user_id'],
            'for_username' => $user['username'],
            'rating' => $input['rating'],
            'message' => $input['review']
        );

        /** @var GFNClassifieds_DataWriter_TraderRating $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating');

        $feedbackId = $this->_input->filterSingle('feedback_id', XenForo_Input::UINT);
        if ($feedbackId)
        {
            $writer->setExistingData($feedbackId);
        }
        else
        {
            if (!$this->models()->traderRating()->canAddTraderRating($classified, $key))
            {
                throw $this->getErrorOrNoPermissionResponseException($key);
            }
        }

        $writer->bulkSet($data);

        $ratingCriteria = $this->getContentHelper()->getRatingCriteriaValues($null, $criteriasShown);
        $writer->setRatingCriteria($ratingCriteria, $criteriasShown);
        $writer->preSave();

        if (!$writer->hasErrors() && $writer->isInsert())
        {
            $this->assertNotFlooding('post');
        }

        $writer->save();
        $feedback = $writer->getMergedData();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CREATED,
            $this->_buildLink('classifieds/traders/ratings', $feedback)
        );
    }

    public function actionLike()
    {
        list ($rating) = $this->_getRatingRelatedDetails();

        if (!$this->models()->traderRating()->canLikeTraderRating($rating, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $likeModel = $this->_getLikeModel();
        $existingLike = $likeModel->getContentLikeByLikeUser('classified_trader_rating', $rating['feedback_id'], XenForo_Visitor::getUserId());

        if ($this->_request->isPost())
        {
            if ($existingLike)
            {
                $latestUsers = $likeModel->unlikeContent($existingLike);
            }
            else
            {
                $latestUsers = $likeModel->likeContent('classified_trader_rating', $rating['feedback_id'], $rating['user_id']);
            }

            $liked = ($existingLike ? false : true);

            if ($this->_noRedirect() && $latestUsers !== false)
            {
                $rating['likeUsers'] = $latestUsers;
                $rating['likes'] += ($liked ? 1 : -1);
                $rating['like_date'] = ($liked ? XenForo_Application::$time : 0);

                $viewParams = array(
                    'rating' => $rating,
                    'liked' => $liked
                );

                return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_LikeConfirmed', '', $viewParams);
            }
            else
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $this->_buildLink('classifieds/traders/ratings', $rating)
                );
            }
        }
        else
        {
            $viewParams = array(
                'rating' => $rating,
                'like' => $existingLike
            );

            return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Like', 'classifieds_trader_rating_like', $viewParams);
        }
    }

    public function actionLikes()
    {
        list ($rating) = $this->_getRatingRelatedDetails();

        $likes = $this->_getLikeModel()->getContentLikes('classified_trader_rating', $rating['feedback_id']);
        if (!$likes)
        {
            return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_trader_rating_yet'), 404);
        }

        $viewParams = array(
            'rating' => $rating,
            'likes' => $likes
        );

        return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Likes', 'classifieds_trader_rating_likes', $viewParams);
    }

    public function actionDelete()
    {
        list ($rating, , $reviewee) = $this->_getRatingRelatedDetails();

        $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
        $deleteType = ($hardDelete ? 'hard' : 'soft');

        if (!$this->models()->traderRating()->canDeleteTraderRating($rating, $deleteType, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        if ($this->isConfirmedPost())
        {
            /** @var GFNClassifieds_DataWriter_TraderRating $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating');
            $writer->setExistingData($rating);

            if ($hardDelete)
            {
                $writer->delete();

                XenForo_Model_Log::logModeratorAction('classified_trader_rating', $rating, 'delete_hard');
            }
            else
            {
                $reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
                $writer->setExtraData($writer::DATA_DELETE_REASON, $reason);
                $writer->set('feedback_state', 'deleted');
                $writer->save();

                if (XenForo_Visitor::getUserId() != $rating['user_id'])
                {
                    XenForo_Model_Log::logModeratorAction('classified_trader_rating', $rating, 'delete_soft', array('reason' => $reason));
                }
            }

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/traders', $reviewee)
            );
        }
        else
        {
            $viewParams = array(
                'rating' => $rating,
                'canHardDelete' => $this->models()->traderRating()->canDeleteTraderRating($rating, 'hard')
            );

            return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Delete', 'classifieds_trader_rating_delete', $viewParams);
        }
    }

    public function actionShow()
    {
        list ($rating) = $this->_getRatingRelatedDetails();

        if (!$this->_request->isXmlHttpRequest())
        {
            return $this->responseNoPermission();
        }

        $viewParams = array(
            'rating' => $rating
        );

        return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Show', 'classifieds_trader_rating_feedback_list_item', $viewParams);
    }

    public function actionUndelete()
    {
        list ($rating) = $this->_getRatingRelatedDetails();

        if (!$this->models()->traderRating()->canUndeleteTraderRating($rating, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating');
            $writer->setExistingData($rating);
            $writer->set('feedback_state', 'visible');
            $writer->save();

            XenForo_Model_Log::logModeratorAction('classified_trader_rating', $rating, 'undelete');

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/traders/ratings', $rating)
            );
        }
        else
        {
            $viewParams = array(
                'rating' => $rating
            );

            return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Undelete', 'classifieds_trader_rating_undelete', $viewParams);
        }
    }

    public function actionIp()
    {
        list ($rating) = $this->_getRatingRelatedDetails();

        if (!$this->models()->user()->canViewIps($key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $ipInfo = $this->models()->ip()->getContentIpInfo($rating);
        if (empty($ipInfo['contentIp']))
        {
            return $this->responseError(new XenForo_Phrase('no_ip_information_available'));
        }

        $viewParams = array(
            'rating' => $rating,
            'ipInfo' => $ipInfo
        );

        return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Ip', 'classifieds_trader_rating_ip', $viewParams);
    }

    public function actionReport()
    {
        list ($rating, $reviewer, $reviewee) = $this->_getRatingRelatedDetails();

        if (!$this->models()->traderRating()->canReportTraderRating($rating, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        if ($this->isConfirmedPost())
        {
            $reportMessage = $this->_input->filterSingle('message', XenForo_Input::STRING);
            if (!$reportMessage)
            {
                return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
            }

            $this->assertNotFlooding('report');

            /* @var $reportModel XenForo_Model_Report */
            $reportModel = XenForo_Model::create('XenForo_Model_Report');
            $reportModel->reportContent('classified_trader_rating', $rating, $reportMessage);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/traders/ratings', $rating),
                new XenForo_Phrase('thank_you_for_reporting_this_message')
            );
        }
        else
        {
            $viewParams = array(
                'rating' => $rating
            );

            return $this->responseView('GFNClassifieds_ViewPublic_TraderRating_Report', 'classifieds_trader_rating_report', $viewParams);
        }
    }

    protected function _getRatingRelatedDetails()
    {
        $reviewer = $this->_trader;
        $rating = $this->getContentHelper()->assertTraderRatingValidAndViewable();

        if ($rating['user_id'] != $reviewer['user_id'])
        {
            throw $this->responseException($this->responseRedirect(
                XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
                $this->_buildLink('classifieds/traders/ratings', $rating)
            ));
        }

        try
        {
            $reviewee = $this->getContentHelper()->getTraderOrError($rating['for_user_id']);
        }
        catch (XenForo_ControllerResponse_Exception $e)
        {
            throw $this->responseException($this->responseError(new XenForo_Phrase('trader_being_reviewed_does_not_exist'), 404));
        }

        $rating = $this->models()->traderRating()->prepareTraderRating($rating);

        return array($rating, $reviewer, $reviewee);
    }
}