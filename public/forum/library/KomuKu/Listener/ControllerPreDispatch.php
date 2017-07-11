<?php
$rootDir = XenForo_Autoloader::getInstance()->getRootDir();

$version = 0;
if ($handle = opendir($rootDir . '/KomuKu/Listener/ControllerPreDispatch')) {
	while (false !== ($entry = readdir($handle))) {
		if (intval($entry) > $version) {
			$version = intval($entry);
		}
	}
}

require_once $rootDir . '/KomuKu/Listener/ControllerPreDispatch/' . $version . '.php';