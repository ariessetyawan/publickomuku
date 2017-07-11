<?php
// Team NullXF

class phc_AdvancedRules_ControllerPublic_AR extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
        $waitTime = 0;

        $ruleIds = $this->_input->filterSingle('ruleIds', XenForo_Input::STRING);
        $redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
        $show = $this->_input->filterSingle('show', XenForo_Input::BOOLEAN);

        if(!$ruleIds)
        {
            $showAll = true;

            $ARModel = $this->_getARModel();
            $rules = $ARModel->fetchAllAR();

            foreach($rules as $key => &$rule)
            {
                if(!$rule['active'])
                    unset($rules[$key]);
            }
        }
        elseif($show && $ruleIds)
        {
            $showAll = true;

            // Check Rule Values
            $arIds = @explode(',', $ruleIds);

            if(!$ruleIds || !is_array($arIds) || empty($arIds))
                return $this->responseError(new XenForo_Phrase('ar_rule'));

            $ARModel = $this->_getARModel();
            $rules = $ARModel->fetchRulesByIds($arIds);
        }
        elseif(!$show && $ruleIds)
        {
            $showAll = false;

            // Check Rule Values
            $arIds = @explode(',', $ruleIds);

            if(!$ruleIds || !is_array($arIds) || empty($arIds))
                return $this->responseError(new XenForo_Phrase('ar_rule'));

            $ARModel = $this->_getARModel();
            $rules = $ARModel->fetchRulesByIds($arIds);

            foreach ($rules as $data)
            {
                $waitTime += $data['time'];
            }

            if($waitTime <= 0)
                $waitTime = 10;
        }

        if(empty($rules))
            return $this->responseError('', 404);

        $viewParams = array(
            'waitTime' => $waitTime,
            'redirect' => $redirect,
            'ruleIds' => $ruleIds,
            'rules' => $rules,
            'showAll' => $showAll,
        );

        return $this->responseView('phc_AdvancedRules_ViewPublic_AR', 'ar_rule', $viewParams);
	}

    public function actionConfirm()
    {
        $this->_assertPostOnly();

        $ruleIds = $this->_input->filterSingle('ruleIds', XenForo_Input::STRING);
        $redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);

        $visitor = XenForo_Visitor::getInstance();

        // Check Rule Values
        $arIds = @explode(',', $ruleIds);

        if(!$ruleIds || !is_array($arIds) || empty($arIds))
            return $this->responseError(new XenForo_Phrase('ar_rule_not_found'));


        /*
                $ARModel = $this->_getARModel();
                $rules = $ARModel->fetchRulesByIds($arIds);

                $waitTime = 0;

                foreach ($rules as $data)
                {
                    $waitTime += $data['time'];
                }

                if($waitTime <= 0)
                    $waitTime = 10;

                $ruleStart = XenForo_Application::getSession()->get('ruleStart');
                if (!$ruleStart || ($ruleStart + $waitTime) > time())
                    return $this->responseError(new XenForo_Phrase('ar_you_must_wait_longer_to_accept_rules'));*/

        $this->_getARModel()->acceptRules($arIds, $visitor['user_id']);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $redirect
        );
    }

    protected static function _getARModel()
    {
        return XenForo_Model::create('phc_AdvancedRules_Model_ARModel');
    }
}