<?php

class XenForo_Install_Upgrade_1020070 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.2.0';
	}

	public function step1()
	{
		$this->executeUpgradeQuery('
			UPDATE kmk_bb_code_media_site SET
				match_urls = \'#metacafe\\\\.com/watch/(?P<id>\\\\d+)/#siU\',
				embed_html = \'<iframe src="http://www.metacafe.com/embed/{$id:digits}/" width="500" height="300" allowFullScreen frameborder=0></iframe>\'
			WHERE media_site_id = \'metacafe\'
				AND embed_html LIKE \'%<embed%\'
				AND match_is_regex = 1
		');

		$this->executeUpgradeQuery('
			UPDATE kmk_bb_code_media_site SET
				embed_html = \'<iframe width="500" height="300" src="http://www.liveleak.com/ll_embed?i={$id}" frameborder="0" allowfullscreen></iframe>\'
			WHERE media_site_id = \'liveleak\'
				AND embed_html LIKE \'%<object%\'
		');
	}
}