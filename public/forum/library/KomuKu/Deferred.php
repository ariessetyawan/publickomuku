<?php
$rootDir = XenForo_Autoloader::getInstance()->getRootDir();

$version = 0;
if ($handle = opendir($rootDir . '/KomuKu/Deferred')) {
	while (false !== ($entry = readdir($handle))) {
		if (intval($entry) > $version) {
			$version = intval($entry);
		}
	}
}

require_once $rootDir . '/KomuKu/Deferred/' . $version . '.php';