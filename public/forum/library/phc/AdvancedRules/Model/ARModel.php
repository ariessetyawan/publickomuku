<?php

// Team NullXF

class phc_AdvancedRules_Model_ARModel extends XenForo_Model
{
    public function fetchAllAR()
    {
        return $this->fetchAllKeyed('
			SELECT
			    ar.*
			FROM phc_advanced_rules as ar
		', 'ar_id');
    }

    public function fetchARById($ar_id)
    {
        $db = $this->_getDb();

        return $db->fetchRow(
            '
                SELECT
                    ar.*
                FROM
                    phc_advanced_rules as ar

                WHERE ar_id = ' . $ar_id
            );
    }

    public function fetchRulesByIds(array $Ids)
    {
        $db = $this->_getDb();

        return $db->fetchAll(
            '
                SELECT
                    ar.*
                FROM
                    phc_advanced_rules as ar

                WHERE
                  ar_id IN (' . $db->quote($Ids) . ')
            ');
    }

    public function resetData($id)
    {
        if($id)
        {
            $this->_getDb()->query('DELETE FROM phc_advanced_rules_accepted WHERE ar_id = ' . $id);
        }
        else
        {
            $this->_getDb()->query('TRUNCATE phc_advanced_rules_accepted');
        }
    }

    public function resetUserData($userId)
    {
        $this->_getDb()->query('DELETE FROM phc_advanced_rules_accepted WHERE user_id = ' . $userId);
    }

    public function rebuildARCache()
    {
        $AR = $this->fetchAllAR();

        foreach($AR as $key => &$data)
        {
            if(!$data['active'])
                unset($AR[$key]);

            $data['actions'] = unserialize($data['actions']);
            $data['node_ids'] = unserialize($data['node_ids']);
            $data['group_ids'] = unserialize($data['group_ids']);
        }

        XenForo_Application::setSimpleCacheData('phcARCache', $AR);
    }

    public function checkIfRuleAccept($user_id, array $rules)
    {
        $db = $this->_getDb();

        return $db->fetchCol(
            '
                SELECT
                    ara.ar_id
                FROM
                    phc_advanced_rules_accepted as ara

                WHERE
                    user_id = ' . $user_id . '

                AND
                    ar_id IN (' . $db->quote(array_keys($rules)) . ')
        ');
    }

    public function acceptRules(array $ruleIds, $userId)
    {
        $db = $this->_getDb();

        foreach($ruleIds as $id)
        {
            $db->query('
			INSERT IGNORE INTO phc_advanced_rules_accepted
				(user_id, ar_id, dateline)
			VALUES
				(?, ?, ?)
            ', array($userId, $id, XenForo_Application::$time));
        }
    }
}