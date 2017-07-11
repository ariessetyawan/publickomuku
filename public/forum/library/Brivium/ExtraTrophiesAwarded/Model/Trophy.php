<?php
class Brivium_ExtraTrophiesAwarded_Model_Trophy extends XFCP_Brivium_ExtraTrophiesAwarded_Model_Trophy
{	
	protected static $_sumTrophyPoints = null;
	
	public function updateTrophiesForUser(array $user, array $userTrophies = null, array $trophies = null)
	{
		$response = parent::updateTrophiesForUser($user, $userTrophies, $trophies);
		
		if(self::$_sumTrophyPoints === null)
		{
			self::$_sumTrophyPoints = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy')->getSumTrophyPoints();
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
		
		return $response;
	}
}