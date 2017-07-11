<?php /*2a4c95fa3cef341ed3aeb69f36e310f05f507ccb*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_DataWriter_CategoryWatch extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return array(
            'kmk_classifieds_category_watch' => array(
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'category_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'notify_on' => array(
                    'type' => self::TYPE_STRING,
                    'allowedValues' => array('', 'classified'),
                    'default' => ''
                ),
                'send_alert' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'send_email' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'include_children' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 1
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
        else if (isset($data['user_id'], $data['category_id']))
        {
            $userId = $data['user_id'];
            $categoryId = $data['category_id'];
        }
        else if (isset($data[0], $data[1]))
        {
            $userId = $data[0];
            $categoryId = $data[1];
        }
        else
        {
            return false;
        }

        return array(
            'kmk_classifieds_category_watch' => $this->_getCategoryWatchModel()->getUserCategoryWatchByCategoryId($userId, $categoryId)
        );
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'))
            . ' AND category_id = ' . $this->_db->quote($this->getExisting('category_id'));
    }

    /**
     * @return KomuKuYJB_Model_CategoryWatch
     */
    protected function _getCategoryWatchModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_CategoryWatch');
    }
}