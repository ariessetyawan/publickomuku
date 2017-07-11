<?php

class Brivium_ExtraTrophiesAwarded_Deferred_Trophy extends XenForo_Deferred_Abstract
{
	public function execute(array $deferred, array $data, $targetRunTime, &$status)
	{
		$data = array_merge(array(
			'position' => 0,
			'batch' => 100
		), $data);
		$data['batch'] = max(1, $data['batch']);

		/* @var $trophyModel XenForo_Model_Trophy */
		
		$trophyModel = XenForo_Model::create('XenForo_Model_Trophy');
		$trophies = $trophyModel->getAllTrophies();
		if (!$trophies)
		{
			return true;
		}
		
		$userModel = XenForo_Model::create('Brivium_ExtraTrophiesAwarded_Model_User');

		$userIds = $userModel->getBretaUserIdsInRange($data['position'], $data['batch']);
		
		if (sizeof($userIds) == 0)
		{
			return true;
		}
		
		$userTrophies = $trophyModel->getUserTrophiesByUserIds($userIds);

		foreach ($userIds AS $userId)
		{
			$data['position'] = $userId;
			
			$user = $userModel->getUserById($userId, array(
					'join' => XenForo_Model_User::FETCH_USER_FULL
				)
			);

			XenForo_Model::create('Brivium_ExtraTrophiesAwarded_Model_exTrophy')->updateLevelForUser(
				$user,
				isset($userTrophies[$user['user_id']]) ? $userTrophies[$user['user_id']] : array(),
				$trophies
			);
		}

		$rbPhrase = new XenForo_Phrase('rebuilding');
		$typePhrase = new XenForo_Phrase('trophies');
		$status = sprintf('%s... %s (%s)', $rbPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

		return $data;
	}

	public function canCancel()
	{
		return true;
	}
}