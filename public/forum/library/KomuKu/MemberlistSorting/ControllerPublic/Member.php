<?php

class KomuKu_MemberlistSorting_ControllerPublic_Member extends XFCP_KomuKu_MemberlistSorting_ControllerPublic_Member
{
	public function actionIndex()
	{
		$pagination = $this->alphabeticalSorting();
		array_unshift($pagination, '#');

		$begin = $this->_input->filterSingle('begin', XenForo_Input::STRING);
		$GLOBALS['begin'] = $begin;
		
		$parent = parent::actionIndex();
		
		$parent->params['begin'] = $begin;
		$parent->params['pagination'] = $pagination;

		return $parent;
	}
	
	/**
	 * Member list
	 *
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionList()
	{
		if (!XenForo_Application::getOptions()->enableMemberList)
		{
			return $this->responseNoPermission();
		}
		
		if (!$this->_getUserModel()->canViewMemberList())
		{
			return $this->responseNoPermission();
		}

		$this->canonicalizeRequestUrl(
			XenForo_Link::buildPublicLink('members/list')
		);

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$usersPerPage = XenForo_Application::get('options')->membersPerPage;

		$criteria = array(
			'user_state' => 'valid',
			'is_banned' => 0
		);
		
		if(XenForo_Application::get('options')->memberlistActivity != 0)
		{
		  $criteria['last_activity'] = array('>', XenForo_Application::$time - (XenForo_Application::get('options')->memberlistActivity * 86400));
		}
		
		if(XenForo_Application::get('options')->memberListmessageCount != 0)
		{
		    $criteria['message_count'] = array('>=', XenForo_Application::get('options')->memberListmessageCount);
		}
		
		if(XenForo_Application::get('options')->memberlistGroups)
		{
			$userGroupModel = $this->_getUserGroupModel();
			
			$criteria['user_group_id'] = array_diff(array_keys($userGroupModel->getAllUserGroups()), XenForo_Application::get('options')->memberlistGroups);
		}

		$userModel = $this->_getUserModel();

		$totalUsers = $userModel->countUsers($criteria);

		$this->canonicalizePageNumber($page, $usersPerPage, $totalUsers, 'members/list');
		
		$defaultOrder = XenForo_Application::get('options')->memberListSortOrder;
		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		
		if (!$defaultOrder)
		{
			$defaultOrder = 'username';
		}
		
		$defaultOrderDirection = 'desc';

		if ($order == 'username')
		{
			$defaultOrderDirection = 'asc';
		}

		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));

		$pagination = $this->alphabeticalSorting();
		array_unshift($pagination, '#');

		$begin = $this->_input->filterSingle('begin', XenForo_Input::STRING);
		$GLOBALS['begin'] = $begin;

		// users for the member list
		$users = $userModel->getUsers($criteria, array(
			'join' => XenForo_Model_User::FETCH_USER_FULL,
			'perPage' => $usersPerPage,
			'page' => $page,
			'order' => $order,
			'direction' => $orderDirection,
			'pagination' => $pagination,
			'begin' => $begin,
		));

		// most recent registrations
		$latestUsers = $userModel->getLatestUsers($criteria, array('limit' => XenForo_Application::get('options')->latestUsers));

		// most active users (highest post count)
		$activeUsers = $userModel->getMostActiveUsers($criteria, array('limit' => XenForo_Application::get('options')->activeUsers));
		
		$pageNavParams = array(
			'order' => ($order != $defaultOrder ? $order : false),
			'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false)
		);
		
		$viewParams = array(
			'users' => $users,
			'totalUsers' => $totalUsers,
			'page' => $page,
			'usersPerPage' => $usersPerPage,
			'pageNavParams' => $pageNavParams,
			'order' => $order,
			'direction' => $orderDirection,
			'defaultOrder' => $defaultOrder,
			'latestUsers' => $latestUsers,
			'activeUsers' => $activeUsers,
			'pagination' => $pagination,
			'begin' => $begin,
		);

		return $this->responseView('XenForo_ViewPublic_Member_List', 'member_list', $viewParams);
	}
	
	public function alphabeticalSorting()
	{
		for($i = 'A'; $i <'Z'; $i++)
		{
			$pagination[] = $i;
		}
		
		$pagination[] = 'Z';
		
		return $pagination;
	}
	
	/**
	 * @return XenForo_Model_UserGroup
	 */
	protected function _getUserGroupModel()
	{
		return $this->getModelFromCache('XenForo_Model_UserGroup');
	}
}