<?php /*d1fc0a7a6d3e2a16a7ae893f49f6f776c535dcbc*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ControllerPublic_Account extends XFCP_GFNClassifieds_Extend_XenForo_ControllerPublic_Account
{
    public function actionPreferencesSave()
    {
        if ($this->_input->filterSingle('default_classified_watch_state', XenForo_Input::BOOLEAN))
        {
            if ($this->_input->filterSingle('default_classified_watch_state_email', XenForo_Input::BOOLEAN))
            {
                $defaultWatchState = 'watch_email';
            }
            else
            {
                $defaultWatchState = 'watch_no_email';
            }
        }
        else
        {
            $defaultWatchState = '';
        }

        if ($this->_input->filterSingle('all_classified_watch_state', XenForo_Input::BOOLEAN))
        {
            if ($this->_input->filterSingle('all_classified_watch_state_email', XenForo_Input::BOOLEAN))
            {
                $allWatchState = 'watch_email';
            }
            else
            {
                $allWatchState = 'watch_no_email';
            }
        }
        else
        {
            $allWatchState = '';
        }

        XenForo_Application::set('classifiedAccountPrefWatchState', array(
            'default' => $defaultWatchState,
            'all' => $allWatchState
        ));
        return parent::actionPreferencesSave();
    }
}