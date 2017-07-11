<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Extends_Model_User extends XFCP_KomuKu_ThreadCount_Extends_Model_User
{
    public function prepareUserConditions(array $conditions, array &$fetchOptions)
	{
		$result = parent::prepareUserConditions($conditions, $fetchOptions);

		if (!empty($conditions['thread_count']) && is_array($conditions['thread_count']))
		{
			$result .= ' AND (' . $this->getCutOffCondition("user.thread_count", $conditions['thread_count']) . ')';
		}

		return $result;
	}

	public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'thread_count' => 'user.thread_count'
		);
		$order = $this->getOrderByClause($choices, $fetchOptions);
		if ($order)
		{
			return $order;
		}

		return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
	}

	public function mergeUsers(array $target, array $source)
	{
		$success = parent::mergeUsers($target, $source);
		if ($success && $target['user_id'] != $source['user_id'] && !empty($source['thread_count']))
		{
			$this->_getDb()->query("
				UPDATE kmk_user
				SET thread_count = thread_count + ?
				WHERE user_id = ?
			", array($source['thread_count'], $target['user_id']));
		}

		return $success;
	}

}