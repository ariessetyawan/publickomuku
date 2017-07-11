<?php /*167679baf899a7484b9eea58e6a36d6a601ad0c1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 10
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_DataWriter_ConversationMaster extends XFCP_GFNClassifieds_Extend_XenForo_DataWriter_ConversationMaster
{
    protected function _getFields()
    {
        return XenForo_Application::mapMerge(parent::_getFields(), array(
            'kmk_classifieds_conversation' => array(
                'conversation_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => array('kmk_conversation_master', 'conversation_id')
                ),
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => array('kmk_conversation_master', 'user_id')
                ),
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'show_location' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                )
            )
        ));
    }

    protected function _getExistingData($data)
    {
        $return = parent::_getExistingData($data);
        if (!$return)
        {
            return false;
        }

        $data = array();
        foreach ($return as $fields)
        {
            $data = XenForo_Application::mapMerge($data, $fields);
        }

        $related = $this->_getClassifiedRelatedData($data['conversation_id']);
        if ($related)
        {
            $data = XenForo_Application::mapMerge($data, $related);
        }

        return $this->getTablesDataFromArray($data);
    }

    protected function _getClassifiedRelatedData($conversationId)
    {
        return $this->_db->fetchRow(
            'SELECT *
            FROM kmk_classifieds_conversation
            WHERE conversation_id = ?', $conversationId
        );
    }
}