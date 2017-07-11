<?php /*d71121e21614deb3c918419affc26478eab39d05*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_Model_Conversation extends XFCP_KomuKuYJB_Extend_XenForo_Model_Conversation
{
    public function getConversationForUser($conversationId, $viewingUser, array $fetchOptions = array())
    {
        if (is_array($viewingUser))
        {
            $this->standardizeViewingUserReference($viewingUser);
            $userId = $viewingUser['user_id'];
        }
        else
        {
            $userId = $viewingUser;
        }

        $joinOptions = $this->prepareConversationFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow('
			SELECT conversation_master.*,
				conversation_user.*,
				conversation_recipient.recipient_state, conversation_recipient.last_read_date,
				conversation_classified.classified_id, conversation_classified.show_location
				' . $joinOptions['selectFields'] . '
			FROM kmk_conversation_user AS conversation_user
			INNER JOIN kmk_conversation_master AS conversation_master ON
				(conversation_user.conversation_id = conversation_master.conversation_id)
			INNER JOIN kmk_conversation_recipient AS conversation_recipient ON
					(conversation_user.conversation_id = conversation_recipient.conversation_id
					AND conversation_user.owner_user_id = conversation_recipient.user_id)
            LEFT JOIN kmk_classifieds_conversation AS conversation_classified ON
                (conversation_classified.conversation_id = conversation_master.conversation_id)
				' . $joinOptions['joinTables'] . '
			WHERE conversation_user.conversation_id = ?
				AND conversation_user.owner_user_id = ?
		', array($conversationId, $userId));
    }
}