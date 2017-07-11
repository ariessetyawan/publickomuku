<?php

class KomuKu_ProfileCover_ViewPublic_Account_DoCrop extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = $this->_params;

		return json_encode($output);
	}
}