<?php

class phc_AlphabeticalXF_Extend_XenForo_Model_Conversation extends XFCP_phc_AlphabeticalXF_Extend_XenForo_Model_Conversation
{
    public function getConversationsForUser($userId, array $conditions = array(), array $fetchOptions = array())
    {
        if(!empty($GLOBALS['alpha']) || !XenForo_Application::get('options')->alphaxf_sort_results)
        {
            return parent::getConversationsForUser($userId, $conditions, $fetchOptions);
        }
        
        $whereClause = $this->prepareConversationConditions($conditions, $fetchOptions);
        $sqlClauses = $this->prepareConversationFetchOptions($fetchOptions);

        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        $sql = $this->limitQueryResults(
            '
				SELECT conversation_master.*,
					conversation_user.*,
					conversation_starter.*,
					conversation_master.username AS username,
					conversation_recipient.recipient_state, conversation_recipient.last_read_date
					' . $sqlClauses['selectFields'] . '
				FROM kmk_conversation_user AS conversation_user
				INNER JOIN xf_conversation_master AS conversation_master ON
					(conversation_user.conversation_id = conversation_master.conversation_id)
				INNER JOIN xf_conversation_recipient AS conversation_recipient ON
					(conversation_user.conversation_id = conversation_recipient.conversation_id
					AND conversation_user.owner_user_id = conversation_recipient.user_id)
				LEFT JOIN xf_user AS conversation_starter ON
					(conversation_starter.user_id = conversation_master.user_id)
					' . $sqlClauses['joinTables'] . '
				WHERE conversation_user.owner_user_id = ?
					AND ' . $whereClause . '
				ORDER BY conversation_master.title ASC
			', $limitOptions['limit'], $limitOptions['offset']
        );

        return $this->fetchAllKeyed($sql, 'conversation_id', $userId);
    }

	public function prepareConversationConditions(array $conditions, array &$fetchOptions)
	{
        $res = parent::prepareConversationConditions($conditions, $fetchOptions);

        $res = $this->_getAlphaModel()->getAlphaStatement('conversation_master.title', $res);

        return $res;
	}

    protected function _getAlphaModel()
    {
        return $this->getModelFromCache('phc_AlphabeticalXF_Model_Alpha');
    }
}