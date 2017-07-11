<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Deferred_UserLikedThreadCount extends XenForo_Deferred_Abstract
{
	public function execute(array $deferred, array $data, $targetRunTime, &$status)
	{
		$data = array_merge(array(
			'position' => 0,
			'batch' => 10
		), $data);
		$data['batch'] = max(1, $data['batch']);

		$model = XenForo_Model::create('KomuKu_LikeThreads_Model_LikeThreads');

		$userModel = XenForo_Model::create('XenForo_Model_User');

		$userIds = $userModel->getUserIdsInRange($data['position'], $data['batch']);
		if (sizeof($userIds) == 0)
		{
			return true;
		}

		$model->rebuildUserLikedThreadCount($userIds);

		$data['position'] = end($userIds);

		$actionPhrase = new XenForo_Phrase('rebuilding');
		$typePhrase = new XenForo_Phrase('th_liked_threads_user_count');
		$status = sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

		return $data;
	}

	public function canCancel()
	{
		return true;
	}
}