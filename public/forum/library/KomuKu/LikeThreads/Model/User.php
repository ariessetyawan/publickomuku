<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Model_User extends XFCP_KomuKu_LikeThreads_Model_User
{
	public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'liked_thread_count' => 'user.liked_thread_count'
		);
		
		$parent = $this->getOrderByClause($choices, $fetchOptions, '');
		
		if($parent)
			return $parent;
		else
			return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
	}
}