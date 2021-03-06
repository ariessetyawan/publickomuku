<?php

class XenForo_Install_Upgrade_1010570 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.1.5';
	}

	public function step1()
	{
		$this->executeUpgradeQuery('
			UPDATE kmk_bb_code_media_site SET
				match_urls = \'facebook.com/*video.php?v={$id:digits}\nfacebook.com/*photo.php?v={\$id:digits}\',
				embed_html = \'<iframe src="https://www.facebook.com/video/embed?video_id={$id}" width="500" height="300" frameborder="0"></iframe>\'
			WHERE media_site_id = \'facebook\'
				AND match_urls = \'facebook.com/*video.php?v={\$id:digits}\'
				AND embed_html LIKE \'%<object%\'
		');

		return true;
	}
}