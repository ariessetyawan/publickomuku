<?php
class KomuKu_Model_Given extends XenForo_Model {
	const FETCH_RECEIVED_USER = 0x01;
	const FETCH_GIVEN_USER = 0x02;
	const FETCH_POST = 0x04;
	
	public function give(array $post, $points, $comment, $givenUser = null) {
		$this->standardizeViewingUserReference($givenUser);
		
		$dw = XenForo_DataWriter::create('KomuKu_DataWriter_Given');
		$dw->set('post_id', $post['post_id']);
		$dw->set('received_user_id', $post['user_id']);
		$dw->set('received_username', $post['username']);
		$dw->set('given_user_id', $givenUser['user_id']);
		$dw->set('given_username', $givenUser['username']);
		$dw->set('points', $points);
		$dw->set('comment', $comment);
		
		$dw->save();
	}
	
	public function canView(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null) {
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		if ($post['user_id'] == $viewingUser['user_id']) {
			// special case: always possible to view self
			return true;
		}
		
		if ($post['user_id'] == 0) {
			// special case: always IMpossible to view guest
			return false;
		}
		
		if (!$this->getModelFromCache('XenForo_Model_Post')->canViewPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
			return false;
		}

		if (!XenForo_Permission::hasContentPermission($nodePermissions, 'KomuKu_view')) {
			return false;
		}

		return true;
	}
	
	public function canGive(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null) {
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		if ($post['user_id'] == $viewingUser['user_id']) {
			// special case: always IMpossible to give self
			return false;
		}
		
		if ($post['user_id'] == 0) {
			// special case: always IMpossible to give guest
			return false;
		}
		
		if (!$this->getModelFromCache('XenForo_Model_Post')->canViewPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
			return false;
		}
		
		if (!XenForo_Permission::hasContentPermission($nodePermissions, 'KomuKu_give')) {
			return false;
		}

		return true;
	}
	
	public function canViewGlobal(array $viewingUser = null) {
		$this->standardizeViewingUserReference($viewingUser);

		if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'KomuKu_viewGlobal')) {
			return true;
		}

		return false;
	}
	
	public function canViewUser(array $user, array $viewingUser = null) {
		if ($user['user_id'] == 0) {
			// special case: always IMpossible to view guest
			return false;
		}
		
		$canView = $this->canViewGlobal($viewingUser);
		
		if (!$canView) {
			// special case: always possible to view self
			$this->standardizeViewingUserReference($viewingUser);
			
			if ($user['user_id'] == $viewingUser['user_id']) {
				$canView = true;
			}
		}

		return $canView;
	}
	
	public function canSpecify(array $viewingUser = null) {
		$this->standardizeViewingUserReference($viewingUser);

		if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'KomuKu_specify')) {
			return true;
		}

		return false;
	}
	
	public function canGiveNegative(array $viewingUser = null) {
		$this->standardizeViewingUserReference($viewingUser);

		if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'KomuKu_giveNegative')) {
			return true;
		}

		return false;
	}
	
	public function getMaximumGivenPoints(array $viewingUser = null) {
		$this->standardizeViewingUserReference($viewingUser);
		
		$permissionPoints = XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'KomuKu_points');
		
		if ($permissionPoints == -1) {
			// TODO
			$points = 9999;			
		} elseif ($permissionPoints > 0) {
			$points = $permissionPoints;
		} else {
			$points = 1;
			
			$factors = array(
				'factorRegisterDate' => 'register_date',
				'factorMessageCount' => 'message_count',
				'factorTrophiesPoint' => 'trophy_points',
				'factorReputation' => 'kmk_komuku_given',
			);
			
			foreach ($factors as $factor => $userKey) {
				$factorValue = KomuKu_Option::get($factor);
				if ($factorValue > 0 AND !empty($viewingUser[$userKey])) {
					
					switch ($factor) {
						case 'factorRegisterDate':
							$userValue = (XenForo_Application::$time - $viewingUser[$userKey]) / 86400;
							break;
						default:
							$userValue = $viewingUser[$userKey];
							break;
					}
					
					$points += max(0, floor($userValue / $factorValue));
				}
			}
		}
		
		return $points;
	}
	
	public function assertWithinLimit(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null) {
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'KomuKu_exempt')) {
			// this user has exempt limit permission
			// nothing to do here
			return true;
		}
		
		$limitDaily = KomuKu_Option::get('limitDaily');
		$limitDailyPremium = KomuKu_Option::get('limitDailyPremium');
		$limitDailySaiyaMember = KomuKu_Option::get('limitDailySaiyaMember'); 
		if ($limitDaily > 0 || $limitDailyPremium > 0 || $limitDailySaiyaMember > 0) {
			$countDaily = $this->countAllGiven(array('given_user_id' => $viewingUser['user_id'], 'give_date' => array('>', XenForo_Application::$time - 86400)));
			if($viewingUser['secondary_group_ids'] == 4 || $viewingUser['secondary_group_ids'] == 6){
				$batasreputation = $limitDailyPremium;
			}elseif($viewingUser['secondary_group_ids'] == 3 || $viewingUser['secondary_group_ids'] == 2 || $viewingUser['secondary_group_ids'] == 8){
				$batasreputation = $limitDailySaiyaMember;
			}elseif($viewingUser['secondary_group_ids'] == 7 || $viewingUser['secondary_group_ids'] == ""){
				$batasreputation = $limitDaily;
			}
			if ($countDaily >= $batasreputation) {
				throw new XenForo_Exception(new XenForo_Phrase('KomuKu_daily_limit_of_x_reputation_gives_has_been_exceeded', array('times' => $countDaily." >= ".$batasreputation)), true);
			}
		}
		
		$limitUserSpread = KomuKu_Option::get('limitUserSpread');
		if ($limitUserSpread > 0) {
			$records = $this->getAllGiven(array('given_user_id' => $viewingUser['user_id']), array('limit' => $limitUserSpread, 'order' => 'give_date', 'direction' => 'desc'));
			foreach ($records as $record) {
				if ($record['received_user_id'] == $post['user_id']) {
					throw new XenForo_Exception(new XenForo_Phrase('KomuKu_x_different_users_must_be_given_reputation', array('users' => $limitUserSpread)), true);
				}
			}
		}
		
		return true;
	}
	
	public function getAllFromGivenUserForPostIds($givenUserId, array $postIds, array $fetchOptions = array()) {
		return $this->getAllGiven(array('given_user_id' => $givenUserId, 'post_id' => $postIds), $fetchOptions);
	}
	
	public function getOneFromGivenUserForPostId($givenUserId, $postId, array $fetchOptions = array()) {
		$all = $this->getAllFromGivenUserForPostIds($givenUserId, array($postId), $fetchOptions);
		
		return reset($all);
	}
	
	public function getAllForPostId($postId, array $fetchOptions = array()) {
		return $this->getAllGiven(array('post_id' => $postId), $fetchOptions);
	}
	
	public function getAllForReceivedUser($receivedUserId, array $fetchOptions = array()) {
		return $this->getAllGiven(array('received_user_id' => $receivedUserId), $fetchOptions);
	}
	
	public function countAllForReceivedUser($receivedUserId, array $fetchOptions = array()) {
		return $this->countAllGiven(array('received_user_id' => $receivedUserId), $fetchOptions);
	}
	
	public function getList(array $conditions = array(), array $fetchOptions = array()) {
		$data = $this->getAllGiven($conditions, $fetchOptions);
		$list = array();
		
		foreach ($data as $id => $row) {
			$list[$id] = $row['received_username'];
		}
		
		return $list;
	}

	public function getGivenById($id, array $fetchOptions = array()) {
		$data = $this->getAllGiven(array ('given_id' => $id), $fetchOptions);
		
		return reset($data);
	}
	
	public function getAllGiven(array $conditions = array(), array $fetchOptions = array()) {
		$whereConditions = $this->prepareGivenConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareGivenOrderOptions($fetchOptions);
		$joinOptions = $this->prepareGivenFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults("
				SELECT given.*
					$joinOptions[selectFields]
				FROM `kmk_komuku_given` AS given
					$joinOptions[joinTables]
				WHERE $whereConditions
					$orderClause
			", $limitOptions['limit'], $limitOptions['offset']
		), 'given_id');
	}
		
	public function countAllGiven(array $conditions = array(), array $fetchOptions = array()) {
		$whereConditions = $this->prepareGivenConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareGivenOrderOptions($fetchOptions);
		$joinOptions = $this->prepareGivenFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
		return $this->_getDb()->fetchOne("
			SELECT COUNT(*)
			FROM `kmk_komuku_given` AS given
				$joinOptions[joinTables]
			WHERE $whereConditions
		");
	}
	
	public function prepareGivenConditions(array $conditions, array &$fetchOptions) {
		$sqlConditions = array();
		$db = $this->_getDb();
		
		foreach (array('given_id', 'post_id', 'received_user_id', 'given_user_id', 'given_username') as $intField) {
			if (!isset($conditions[$intField])) continue;
			
			if (is_array($conditions[$intField])) {
				$sqlConditions[] = "given.$intField IN (" . $db->quote($conditions[$intField]) . ")";
			} else {
				$sqlConditions[] = "given.$intField = " . $db->quote($conditions[$intField]);
			}
		}
		
		if (!empty($conditions['give_date']) && is_array($conditions['give_date'])){
			list($operator, $cutOff) = $conditions['give_date'];

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "given.give_date $operator " . $db->quote($cutOff);
		}
		
		return $this->getConditionsForClause($sqlConditions);
	}
	
	public function prepareGivenFetchOptions(array $fetchOptions) {
		$selectFields = '';
		$joinTables = '';
		
		if (!empty($fetchOptions['join'])) {
			if ($fetchOptions['join'] & self::FETCH_RECEIVED_USER) {
				$selectFields .= '
					, user.*';
				$joinTables .= '
					INNER JOIN `kmk_user` AS user
						ON (user.user_id = given.received_user_id)';
			} elseif ($fetchOptions['join'] & self::FETCH_GIVEN_USER) {
				$selectFields .= '
					, user.*';
				$joinTables .= '
					INNER JOIN `kmk_user` AS user
						ON (user.user_id = given.given_user_id)';
			}
			
			if ($fetchOptions['join'] & self::FETCH_POST) {
				$selectFields .= '
					, post.message AS post_message';
				$joinTables .= '
					INNER JOIN `kmk_post` AS post
						ON (post.post_id = given.post_id)';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}
	
	public function prepareGivenOrderOptions(array &$fetchOptions, $defaultOrderSql = '') {
		$choices = array(
			'give_date' => 'given.give_date',
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}
}