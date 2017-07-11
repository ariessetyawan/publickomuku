<?php /*f951ad6f7938b596ba72538923dc434328e99647*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_CronEntry_ConversationStats extends GFNCore_CronEntry_Abstract
{
    protected function _run()
    {
        /** @var GFNClassifieds_Model_Trader $model */
        $model = $this->_getModelFromCache('GFNClassifieds_Model_Trader');
        $db = $this->_getDb();

        $userIds = $db->fetchCol(
            'SELECT classified.user_id
            FROM kmk_classifieds_classified AS classified
            INNER JOIN kmk_classifieds_conversation AS conversation
              ON (conversation.classified_id = classified.classified_id)
            INNER JOIN kmk_conversation_message AS message
              ON (conversation.conversation_id = message.conversation_id)
            WHERE message_date > ?', XenForo_Application::$time - 172800
        );

        foreach ($userIds as $userId)
        {
            $model->recalculateConversationStates($userId);
        }
    }
}