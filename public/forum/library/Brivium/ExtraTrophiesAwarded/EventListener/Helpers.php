<?php
class Brivium_ExtraTrophiesAwarded_EventListener_Helpers extends XenForo_Template_Helper_Core
{
	public static function helperGetAwardIcon($trophy)
	{
		if (!$trophy){
			return '';
		}else{
			$url = self::_getAwardIcon($trophy);
			return htmlspecialchars($url);
		}
	}
	protected static function _getAwardIcon($trophy)
	{
		if ($trophy['breta_select'] == 'upload_file'){
			return "//192.168.1.200/komukupublic/public/img/medali/".$trophy['trophy_id'].".jpg?".$trophy['breta_icon_date'];
		}else if($trophy['trophy_id'] < 10){
			return "//192.168.1.200/komukupublic/public/img/medali/".$trophy['trophy_id'].".gif";
		}else{
			return "//192.168.1.200/komukupublic/public/img/medali/default.png";
		}
	}
}