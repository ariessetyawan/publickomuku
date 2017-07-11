<?php
class Brivium_ExtraTrophiesAwarded_Model_exTrophy extends XenForo_Model_Trophy
{	
	protected static $_sumTrophyPoints = null;
	
	public function getAllAwards()
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM kmk_trophy
			WHERE breta_select <> \'hide_icon\' OR breta_select IS NULL
			ORDER BY trophy_points
		', 'trophy_id');
	}
	
	public function getAwardsForUserId($user, array $fetchOptions = array())
	{
		$db = $this->_getDb();
		$sqlConditions = '';

		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
		return $this->fetchAllKeyed($this->limitQueryResults('
			SELECT trophy.*,
				user_trophy.award_date
			FROM kmk_user_trophy AS user_trophy
			INNER JOIN kmk_trophy AS trophy ON (trophy.trophy_id = user_trophy.trophy_id)
			WHERE user_trophy.user_id = ?
				AND user_trophy.breta_show_icon = 1
				'. $sqlConditions .'
				AND (breta_select <> \'hide_icon\' OR breta_select IS NULL)
			ORDER BY trophy.trophy_points DESC
		',$limitOptions['limit'], $limitOptions['offset']
		), 'trophy_id', $user['user_id']);
	}
	
	public function getAwardsForUserById($userId, array $fetchOptions = array())
	{
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
		return $this->fetchAllKeyed('
			SELECT *
			FROM kmk_user_trophy AS user_trophy
			INNER JOIN kmk_trophy AS trophy ON (trophy.trophy_id = user_trophy.trophy_id)
			WHERE user_trophy.user_id = ?
				AND (breta_select <> \'hide_icon\' OR breta_select IS NULL)
			ORDER BY trophy.trophy_points DESC
		', 'trophy_id', $userId);
	}
	
	public function getUserAwardsByUserIds(array $userIds)
	{
		if (!$userIds)
		{
			return array();
		}

		$db = $this->_getDb();

		$output = array();
		$userTrophiesResult = $db->query('
			SELECT trophy.*,
				user_trophy.user_id, user_trophy.award_date
			FROM kmk_user_trophy AS user_trophy
				LEFT JOIN kmk_trophy AS trophy
					ON trophy.trophy_id = user_trophy.trophy_id
			WHERE user_id IN (' . $db->quote($userIds) . ')
				AND breta_show_icon = 1
				AND (breta_select <> \'hide_icon\' OR breta_select IS NULL)
			ORDER BY trophy.trophy_points DESC
		');
		
		$limit = XenForo_Application::get('options')->BRETA_defaultTrophyIcons;
		
		$countTrophies = array();
		
		while ($userTrophy = $userTrophiesResult->fetch())
		{
			if (!isset($countTrophies[$userTrophy['user_id']])) {
				$countTrophies[$userTrophy['user_id']] = 0;
			}
			
			if ($countTrophies[$userTrophy['user_id']] < $limit) {
				$output[$userTrophy['user_id']][$userTrophy['trophy_id']] = $userTrophy;
				$countTrophies[$userTrophy['user_id']] += 1;
			}
		}
		
		return $output;
	}
	
	public function getSumTrophyPoints()
	{
		return intval($this->_getDb()->fetchOne("
			SELECT SUM(trophy_points)
			FROM kmk_trophy
		"));
	}
	
	public function _showIcon($trophyId)
	{
		$visitor = XenForo_Visitor::getInstance();

		$db = $this->_getDb();

		return (boolean)$db->query('
			UPDATE kmk_user_trophy
			SET breta_show_icon = 1
			WHERE user_id = ?
				AND trophy_id = ?
			', array($visitor['user_id'], $trophyId));
	}
	
	public function _hideIcon($trophyId)
	{
		$visitor = XenForo_Visitor::getInstance();

		$db = $this->_getDb();
		
		return (boolean)$db->query('
			UPDATE kmk_user_trophy
			SET breta_show_icon = 0
			WHERE user_id = ?
				AND trophy_id = ?
			', array($visitor['user_id'], $trophyId));
	}
	
	public function updateLevelForUser(array $user, array $userTrophies = null, array $trophies = null)
	{
		$awarded = 0;

		if ($trophies === null)
		{
			$trophies = $this->getAllTrophies();
		}
		if (!$trophies)
		{
			return 0;
		}

		if ($userTrophies === null)
		{
			$userTrophies = $this->getTrophiesForUserId($user['user_id']);
		}

		foreach ($trophies AS $trophy)
		{
			if (isset($userTrophies[$trophy['trophy_id']]))
			{
				continue;
			}

			if (XenForo_Helper_Criteria::userMatchesCriteria($trophy['user_criteria'], false, $user))
			{
				$this->awardUserTrophy($user, $user['username'], $trophy);
				$awarded++;
			}
		}
		
		if(self::$_sumTrophyPoints === null)
		{
			self::$_sumTrophyPoints = $this->getSumTrophyPoints();
		}
		
		$trophyRatio = $user['trophy_points'] / self::$_sumTrophyPoints * 100;
		
		if($trophyRatio >= 0 && $trophyRatio < 5){
			$userLevel = 1; $curentLevel = 0; $nextLevel = round(self::$_sumTrophyPoints * 5 / 100);
		}elseif($trophyRatio >= 5 && $trophyRatio < 10){
			$userLevel = 2; $curentLevel = round(self::$_sumTrophyPoints * 5 / 100); $nextLevel = round(self::$_sumTrophyPoints * 10 / 100);
		}elseif($trophyRatio >= 10 && $trophyRatio < 20){
			$userLevel = 3; $curentLevel = round(self::$_sumTrophyPoints * 10 / 100); $nextLevel = round(self::$_sumTrophyPoints * 20 / 100);
		}elseif($trophyRatio >= 20 && $trophyRatio < 30){
			$userLevel = 4; $curentLevel = round(self::$_sumTrophyPoints * 20 / 100);  $nextLevel = round(self::$_sumTrophyPoints * 30 / 100);
		}elseif($trophyRatio >= 30 && $trophyRatio < 40){
			$userLevel = 5; $curentLevel = round(self::$_sumTrophyPoints * 30 / 100);  $nextLevel = round(self::$_sumTrophyPoints * 40 / 100);
		}elseif($trophyRatio >= 40 && $trophyRatio < 60){
			$userLevel = 6; $curentLevel = round(self::$_sumTrophyPoints * 40 / 100);  $nextLevel = round(self::$_sumTrophyPoints * 60 / 100);
		}elseif($trophyRatio >= 60 && $trophyRatio < 80){
			$userLevel = 7; $curentLevel = round(self::$_sumTrophyPoints * 60 / 100);  $nextLevel = round(self::$_sumTrophyPoints * 80 / 100);
		}else{
			$userLevel = 8; $curentLevel = round(self::$_sumTrophyPoints * 80/ 100);  $nextLevel = self::$_sumTrophyPoints;
		}
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);
		
		$db->query('
			UPDATE kmk_user
			SET breta_user_level = ?, breta_curent_level = ?, breta_next_level = ?
			WHERE user_id = ?
			', array($userLevel, $curentLevel, $nextLevel, $user['user_id']));
				
		XenForo_Db::commit($db);
		
		return $awarded;
	}
}