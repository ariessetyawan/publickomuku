<?php
class KomuKu_Extend_ControllerPublic_Member extends XFCP_KomuKu_Extend_ControllerPublic_Member {
	public function actionReputation() {
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->getUserOrError($userId);
		
		$givenModel = $this->getModelFromCache('KomuKu_Model_Given');
		
		if (!$givenModel->canViewUser($user)) {
			return $this->responseNoPermission();
		}
		
		$fetchOptions = array(
			'join' => KomuKu_Model_Given::FETCH_GIVEN_USER |  KomuKu_Model_Given::FETCH_POST,
			'order' => 'give_date',
			'direction' => 'desc',
		);
		
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$recordsPerPage = KomuKu_Option::get('recordsPerPage');
		$fetchOptions['page'] = $page;
		$fetchOptions['perPage'] = $recordsPerPage;
		
		$records = $givenModel->getAllForReceivedUser($user['user_id'], $fetchOptions);
		$totalRecords = $givenModel->countAllForReceivedUser($user['user_id'], $fetchOptions);
		
		$viewParams = array(
			'user' => $user,
		
			'records' => $records,
			'page' => $page,
			'recordsPerPage' => $recordsPerPage,
			'totalRecords' => $totalRecords,
		);
		
		return $this->responseView('KomuKu_ViewPublic_Member_Reputation', 'KomuKu_member_reputation', $viewParams);
	}
}