<?php /*fdee16b858bf627e6757ad433ed7b49fd08fbf77*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_DataWriter_User extends XFCP_KomuKuYJB_Extend_XenForo_DataWriter_User
{
    protected function _getFields()
    {
        return XenForo_Application::mapMerge(parent::_getFields(), array(
            'kmk_classifieds_trader' => array(
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => array('kmk_user', 'user_id'),
                    'required' => true
                ),
                'classified_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'rating_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'rating_positive_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'rating_neutral_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'rating_negative_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'rating_avg' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                ),
                'rating_weighted' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                )
            ),
            'kmk_user_option' => array(
                'default_classified_watch_state' => array(
                    'type' => self::TYPE_STRING,
                    'allowedValues' => array('', 'watch_no_email', 'watch_email'),
                    'default' => 'watch_no_email'
                ),
                'all_classified_watch_state' => array(
                    'type' => self::TYPE_STRING,
                    'allowedValues' => array('', 'watch_no_email', 'watch_email'),
                    'default' => ''
                )
            )
        ));
    }

    protected function _preSave()
    {
        parent::_preSave();

        if (XenForo_Application::isRegistered('classifiedAccountPrefWatchState'))
        {
            $state = XenForo_Application::get('classifiedAccountPrefWatchState');

            $this->set('default_classified_watch_state', $state['default']);
            $this->set('all_classified_watch_state', $state['all']);
        }
    }
}