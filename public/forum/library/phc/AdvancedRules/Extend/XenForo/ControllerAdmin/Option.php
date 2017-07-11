<?php

// Team NullXF

class phc_AdvancedRules_Extend_XenForo_ControllerAdmin_Option extends XFCP_phc_AdvancedRules_Extend_XenForo_ControllerAdmin_Option
{
	public function actionList()
	{
        $res = parent::actionList();

        if(isset($res->params['preparedOptions']['ar_std_rule']['option_value']))
        {
            $ar_id = $res->params['preparedOptions']['ar_std_rule']['option_value'];

            $optionModel = $this->_getOptionModel();

            $type = array('type' => 'default');

            if($ar_id)
            {
                $link = XenForo_Link::buildPublicLink('rule', '', array('ruleIds' => $ar_id, 'show' => 'true'));
                $type = array('type' => 'custom', 'custom' => $link);
            }

            $optionModel->updateOption('tosUrl', $type);
        }

        return $res;
	}
}