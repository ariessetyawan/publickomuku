<?php

class Brivium_ExtraTrophiesAwarded_ViewPublic_Member_TrophyIcon extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = XenForo_ViewRenderer_Json::jsonEncodeForOutput( $this->_params);
		return $output;
	}
}