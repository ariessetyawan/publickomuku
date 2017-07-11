<?php

class BestAnswer_Model_User extends XFCP_BestAnswer_Model_User
{
	public function prepareUserConditions(array $conditions, array &$fetchOptions)
	{
		$result = parent::prepareUserConditions($conditions, $fetchOptions);
	
		if (!empty($conditions['best_answer_count']) && is_array($conditions['best_answer_count']))
		{
			$result .= ' AND (' . $this->getCutOffCondition("user.best_answer_count", $conditions['best_answer_count']) . ')';
		}
	
		return $result;
	}
	
	public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'best_answer_count' => 'user.best_answer_count'
		);
		$order = $this->getOrderByClause($choices, $fetchOptions);
		if ($order)
		{
			return $order;
		}
	
		return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
	}
}