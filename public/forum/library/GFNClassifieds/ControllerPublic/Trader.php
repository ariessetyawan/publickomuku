<?php /*cc0130ec8d89f78b55cc60d0aa1c86a67641e34a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_Trader extends GFNClassifieds_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if ($this->_input->filterSingle('user_id', XenForo_Input::UINT))
        {
            return $this->responseReroute(__CLASS__, 'view');
        }

        $traders = $this->models()->trader()->getTraders(array(
            'rating_count' => array('>', 0)
        ), array(
            'order' => 'weighted',
            'direction' => 'desc'
        ));

        if (!$traders)
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                $this->_buildLink('classifieds/traders/all')
            );
        }

        $totalClassifiedCounts = $this->models()->trader()->getTotalClassifiedCounts(array_keys($traders));
        foreach ($totalClassifiedCounts as $userId => $count)
        {
            $traders[$userId]['classified_count'] = $count;
        }

        $viewParams = array(
            'traders' => $traders
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Trader_List', 'classifieds_trader_list_top_rated', $viewParams);
    }

    public function actionAll()
    {
        if (!$this->_getUserModel()->canViewMemberList())
        {
            return $this->responseNoPermission();
        }

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = XenForo_Application::get('options')->membersPerPage;

        $criteria = array('user_state' => 'valid', 'is_banned' => 0);
        $totalTraders = $this->_getUserModel()->countUsers($criteria);

        $this->canonicalizePageNumber($page, $perPage, $totalTraders, 'classifieds/traders/all');
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/traders/all', null, array('page' => $page)));

        $traders = $this->models()->trader()->getTraders(array(), array(
            'page' => $page,
            'perPage' => $perPage,
            'order' => 'username'
        ));

        if (!$traders)
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                $this->_buildLink('classifieds')
            );
        }

        $viewParams = array(
            'traders' => $traders,
            'ignoredNames' => $this->_getIgnoredContentUserNames($traders),

            'page' => $page,
            'perPage' => $perPage,
            'totalTraders' => $totalTraders
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Trader_List', 'classifieds_trader_list', $viewParams);
    }

    public function actionView()
    {
        $visitor = XenForo_Visitor::getInstance();

        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $userFetchOptions = array('join' => XenForo_Model_User::FETCH_LAST_ACTIVITY | XenForo_Model_User::FETCH_USER_PERMISSIONS);

        try
        {
            $user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId, $userFetchOptions);
        }
        catch (XenForo_ControllerResponse_Exception $e)
        {
            if ($e->getControllerResponse()->responseCode == 404)
            {
                return $this->responseError(new XenForo_Phrase('requested_trader_not_found'));
            }

            throw $e;
        }

        $user['activity'] = ($user['view_date'] ? $this->getModelFromCache('XenForo_Model_Session')->getSessionActivityDetails($user) : false);

        $userModel = $this->models()->user();
        $userProfileModel = $this->models()->userProfile();
        $ratingModel = $this->models()->traderRating();

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = XenForo_Application::get('options')->messagesPerPage;

        $criteria = $ratingModel->getPermissionBasedFetchConditions();
        $criteria['for_user_id'] = $user['user_id'];
        $totalRatings = $ratingModel->countTraderRatings($criteria);

        $this->canonicalizePageNumber($page, $perPage, $totalRatings, 'classifieds/traders', $user);
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/traders', $user, array('page' => $page)));

        $ratings = $ratingModel->getTraderRatings($criteria, array(
            'join' => $ratingModel::FETCH_USER | $ratingModel::FETCH_DELETION_LOG,
            'likeUserId' => $visitor->getUserId(),
            'perPage' => $perPage,
            'page' => $page
        ));

        $ratings = $ratingModel->prepareTraderRatings($ratings);
        $inlineModOptions = $ratingModel->getInlineModOptionsForTraderRatings($ratings);

        $ignoredNames = $this->_getIgnoredContentUserNames($ratings);

        if ($user['following'])
        {
            $followingToShowCount = 6;
            $followingCount = substr_count($user['following'], ',') + 1;

            $following = $userModel->getFollowedUserProfiles($userId, $followingToShowCount, 'RAND()');
            if (count($following) < $followingToShowCount)
            {
                $followingCount = count($following);
            }
        }
        else
        {
            $followingCount = 0;
            $following = array();
        }

        $followersCount = $userModel->countUsersFollowingUserId($userId);
        $followers = $userModel->getUsersFollowingUserId($userId, 6, 'RAND()');

        $birthday = $userProfileModel->getUserBirthdayDetails($user);
        $user['age'] = $birthday['age'];

        $user['isFollowingVisitor'] = $userModel->isFollowing($visitor['user_id'], $user);

        if ($userModel->canViewWarnings())
        {
            $canViewWarnings = true;
            $warningCount = $this->getModelFromCache('XenForo_Model_Warning')->countWarningsByUser($user['user_id']);
        }
        else
        {
            $canViewWarnings = false;
            $warningCount = 0;
        }

        $traderRating = array('positive' => 0, 'neutral' => 0, 'negative' => 0);

        if (!empty($user['rating_count']))
        {
            $traderRating['positive'] = ($user['rating_positive_count'] / $user['rating_count']) * 100;
            $traderRating['neutral'] = ($user['rating_neutral_count'] / $user['rating_count']) * 100;
            $traderRating['negative'] = ($user['rating_negative_count'] / $user['rating_count']) * 100;
        }

        $viewParams = array(
            'user' => $user,
            'isTraderView' => true,
            'canViewOnlineStatus' => $userModel->canViewUserOnlineStatus($user),
            'canViewCurrentActivity' => $userModel->canViewUserCurrentActivity($user),
            'canIgnore' => $this->models()->userIgnore()->canIgnoreUser($visitor['user_id'], $user),
            'canCleanSpam' => (XenForo_Permission::hasPermission($visitor['permissions'], 'general', 'cleanSpam') && $userModel->couldBeSpammer($user)),
            'canBanUsers' => ($visitor['is_admin'] && $visitor->hasAdminPermission('ban') && $user['user_id'] != $visitor->getUserId() && !$user['is_admin'] && !$user['is_moderator']),
            'canEditUser' => $userModel->canEditUser($user),
            'canViewIps' => $userModel->canViewIps(),
            'canReport' => $this->_getUserModel()->canReportUser($user),
            'canRate' => $ratingModel->canAddTraderRating() && $visitor['user_id'] != $user['user_id'],

            'warningCount' => $warningCount,
            'canViewWarnings' => $canViewWarnings,
            'canWarn' => $userModel->canWarnUser($user),

            'followingCount' => $followingCount,
            'followersCount' => $followersCount,

            'following' => $following,
            'followers' => $followers,

            'birthday' => $birthday,

            'canStartConversation' => $userModel->canStartConversationWithUser($user),

            'ratings' => $ratings,
            'traderRating' => $traderRating,
            'inlineModOptions' => $inlineModOptions,
            'page' => $page,
            'perPage' => $perPage,
            'totalTraderRatings' => $totalRatings,

            'ignoredNames' => $ignoredNames,

            'showRecentActivity' => $userProfileModel->canViewRecentActivity($user)
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Trader_View', 'member_view', $viewParams);
    }

    public function actionClassifieds()
    {
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);

        if (!$user = $this->_getUserModel()->getUserById($userId))
        {
            return $this->responseError(new XenForo_Phrase('requested_trader_not_found'), 404);
        }

        if (!$this->_request->isXmlHttpRequest())
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                $this->_buildLink('members', $user) . '#classifieds'
            );
        }

        $classifiedModel = $this->models()->classified();
        $advertTypeModel = $this->models()->advertType();

        $criteria = array('user_id' => $userId);
        $criteria += $classifiedModel->getPermissionBasedFetchConditions();
        $totalClassifieds = $classifiedModel->countClassifieds($criteria);

        if (!$totalClassifieds)
        {
            return $this->responseError(new XenForo_Phrase('requested_user_has_no_classifieds'));
        }

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = GFNClassifieds_Options::getInstance()->get('classifiedsPerPage');

        $fetchOptions = $this->_getClassifiedListFetchOptions();

        if ($criteria['deleted'])
        {
            $fetchOptions['join'] |= $classifiedModel::FETCH_DELETION_LOG;
        }

        $fetchOptions += array(
            'perPage' => $perPage,
            'page' => $page,
            'order' => 'item_date',
            'direction' => 'desc'
        );

        $classifieds = $classifiedModel->getClassifieds($criteria, $fetchOptions);
        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $classifieds = $classifiedModel->prepareClassifieds($classifieds);

        $advertTypes = $advertTypeModel->getAllAdvertTypes();
        $advertTypeModel->prepareAdvertTypes($advertTypes);

        $viewParams = array(
            'classifieds' => $classifieds,
            'advertTypes' => $advertTypes,

            'totalClassifieds' => $totalClassifieds,

            'page' => $page,
            'perPage' => $perPage,

            'user' => $user,
            'fromProfile' => $this->_input->filterSingle('profile', XenForo_Input::BOOLEAN)
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Trader_Classified', 'classifieds_trader_view_classified', $viewParams);
    }

    public function actionRate()
    {
        $this->_assertRegistrationRequired();

        if (!$this->models()->traderRating()->canAddTraderRating(null, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $visitor = XenForo_Visitor::getInstance();

        $user = false;
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
        $userFetchOptions = array('join' => XenForo_Model_User::FETCH_USER_FULL);

        if ($userId)
        {
            $user = $this->_getUserModel()->getUserById($userId, $userFetchOptions);
        }
        elseif ($this->isConfirmedPost())
        {
            $username = $this->_input->filterSingle('username', XenForo_Input::STRING);
            if ($username)
            {
                $user = $this->_getUserModel()->getUserByName($username, $userFetchOptions);
            }

            if (!$user)
            {
                return $this->responseError(new XenForo_Phrase('selected_trader_does_not_exist'));
            }
        }

        if ($user)
        {
            if ($user['user_id'] == $visitor['user_id'])
            {
                throw $this->getErrorOrNoPermissionResponseException('you_cannot_rate_yourself');
            }

            XenForo_Application::set('classifiedUserRatingUser', $user);
            return $this->responseReroute('GFNClassifieds_ControllerPublic_TraderRating', 'add');
        }

        return $this->responseView('GFNClassifieds_ViewPublic_Trader_Rate', 'classifieds_trader_rate_start');
    }

    /**
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }

    /**
     * @return XenForo_Model_UserProfile
     */
    protected function _getUserProfileModel()
    {
        return $this->getModelFromCache('XenForo_Model_UserProfile');
    }

    public static function getSessionActivityDetailsForList(array $activities)
    {
        $userIds = array();

        foreach ($activities as $activity)
        {
            if (!empty($activity['params']['user_id']))
            {
                $userIds[$activity['params']['user_id']] = intval($activity['params']['user_id']);
            }
        }

        $userData = array();

        if ($userIds)
        {
            /** @var XenForo_Model_User $userModel */
            $userModel = XenForo_Model::create('XenForo_Model_User');

            $users = $userModel->getUsersByIds($userIds, array(
                'join' => XenForo_Model_User::FETCH_USER_PRIVACY
            ));

            foreach ($users as $user)
            {
                $userData[$user['user_id']] = array(
                    'username' => $user['username'],
                    'url' => XenForo_Link::buildPublicLink('classifieds/traders', $user)
                );
            }
        }

        $output = array();

        foreach ($activities as $key => $activity)
        {
            $user = false;

            if (!empty($activity['params']['user_id']))
            {
                $userId = $activity['params']['user_id'];
                if (isset($userData[$userId]))
                {
                    $user = $userData[$userId];
                }
            }

            if ($user)
            {
                $output[$key] = array(
                    new XenForo_Phrase('viewing_trader_profile'),
                    $user['username'],
                    $user['url'],
                    false
                );
            }
            else
            {
                $output[$key] = new XenForo_Phrase('viewing_trader');
            }
        }

        return $output;
    }
}