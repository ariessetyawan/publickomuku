<?php

class AnonymousPosting_Model_Log extends XenForo_Model
{
    public function getLogById($logId)
    {
        return $this->_getDb()->fetchRow('
            SELECT *
            FROM kmk_post
            WHERE post_id = ? AND anonymous_posting_real_user_id > 0
        ', $logId);
    }

    public function getLogs(array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults('
			SELECT *
			FROM kmk_post
			WHERE anonymous_posting_real_user_id > 0
			ORDER BY post_date DESC
		', $limitOptions['limit'], $limitOptions['offset']), 'post_id');
    }

    public function countLogs(array $fetchOptions = array())
    {
        return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM kmk_post
			WHERE anonymous_posting_real_user_id > 0
		');
    }

    public function delete(array $log)
    {
        $this->_getDb()->update('kmk_post', array(
            'anonymous_posting_real_user_id' => 0,
            'anonymous_posting_real_username' => ''
        ), array(
            'post_id = ?' => $log['post_id'],
        ));
    }

    public function deleteAll()
    {
        $this->_getDb()->update('kmk_post', array(
            'anonymous_posting_real_user_id' => 0,
            'anonymous_posting_real_username' => ''
        ), 'anonymous_posting_real_user_id > 0');
    }

}
