<?php

class KomuKu_Deferred extends XenForo_Deferred_Abstract
{

	public function execute(array $deferred, array $data, $targetRunTime, &$status)
	{
		if (isset($data['install']) && $data['install']) {
			$result = KomuKu_Install::postInstall($data, $targetRunTime, $status);
		} elseif (isset($data['uninstall']) && $data['uninstall']) {
			$result = KomuKu_Install::postUninstall($data, $targetRunTime, $status);
		} else {
			$result = true;
		}

		if ($result === true) {
			return false;
		} else {
			return $data;
		}
	}
}