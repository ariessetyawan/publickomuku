<?php /*bc3b8fd00055b6725e2f872a3167e0f6972878b9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_DataWriter_ClassifiedWatch extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return array(
            'kmk_classifieds_classified_watch' => array(
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'email_subscribe' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'watch_key' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'maxLength' => 16
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        if (!is_array($data))
        {
            return false;
        }
        else if (isset($data['user_id'], $data['classified_id']))
        {
            $userId = $data['user_id'];
            $classifiedId = $data['classified_id'];
        }
        else if (isset($data[0], $data[1]))
        {
            $userId = $data[0];
            $classifiedId = $data[1];
        }
        else
        {
            return false;
        }

        return array(
            'kmk_classifieds_classified_watch' => $this->_getClassifiedWatchModel()->getUserClassifiedWatchByClassifiedId($userId, $classifiedId)
        );
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'))
            . ' AND classified_id = ' . $this->_db->quote($this->getExisting('classified_id'));
    }

    protected function _preSave()
    {
        if (!$this->get('watch_key'))
        {
            $this->set('watch_key', substr(md5(uniqid()), 0, 16));
        }
    }

    /**
     * @return KomuKuYJB_Model_ClassifiedWatch
     */
    protected function _getClassifiedWatchModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_ClassifiedWatch');
    }
}