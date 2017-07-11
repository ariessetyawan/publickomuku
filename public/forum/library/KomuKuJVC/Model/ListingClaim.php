<?php

/**
 * 
 * ListingClaim : Claim made by user to claim a directory listing
 * 
 */
class KomuKuJVC_Model_ListingClaim extends XenForo_Model
{

	/**
	 * Returns a ListingClaim
	 *
	 * @param integer $threadId
	 * @param array $fetchOptions Collection of options related to fetching
	 *
	 * @return array|false
	 */
	public function getListingClaimById($threadId, array $fetchOptions = array())
	{
		
		$joinOptions = $this->prepareListingClaimOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT listingclaim.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_listingclaim AS listingclaim
			' . $joinOptions['joinTables'] . '
			WHERE listingclaim.thread_id = ?
		', $threadId);
		
		
		
		
	}
	
	
	public function prepareListingClaimOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$orderBy = '';

		if (!empty($fetchOptions['order']))
		{
			$orderBySecondary = '';

			switch ($fetchOptions['order'])
			{
				case 'title':
				case 'post_date':
				case 'view_count':
					$orderBy = 'thread.' . $fetchOptions['order'];
					break;

				case 'reply_count':
				case 'first_post_likes':
					$orderBy = 'thread.' . $fetchOptions['order'];
					$orderBySecondary = ', thread.last_post_date DESC';
					break;

				case 'last_post_date':
				default:
					$orderBy = 'thread.last_post_date';
			}
			if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
			{
				$orderBy .= ' DESC';
			}
			else
			{
				$orderBy .= ' ASC';
			}

			$orderBy .= $orderBySecondary;
		}

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_USER)
			{
				$selectFields .= ',
					user.*, IF(user.username IS NULL, thread.username, user.username) AS username';
				$joinTables .= '
					LEFT JOIN kmk_user AS user ON
						(user.user_id = thread.user_id)';
			}
			else if ($fetchOptions['join'] & self::FETCH_AVATAR)
			{
				$selectFields .= ',
					user.avatar_date, user.gravatar';
				$joinTables .= '
					LEFT JOIN kmk_user AS user ON
						(user.user_id = thread.user_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_FORUM)
			{
				$selectFields .= ',
					node.title AS node_title';
				$joinTables .= '
					INNER JOIN kmk_node AS node ON
						(node.node_id = thread.node_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_FORUM_OPTIONS)
			{
				$selectFields .= ',
					forum.*';
				$joinTables .= '
					INNER JOIN kmk_forum AS forum ON
						(forum.node_id = thread.node_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_FIRSTPOST)
			{
				$selectFields .= ',
					post.message, post.attach_count';
				$joinTables .= '
					INNER JOIN kmk_post AS post ON
						(post.post_id = thread.first_post_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
			{
				$selectFields .= ',
					deletion_log.delete_date, deletion_log.delete_reason,
					deletion_log.delete_user_id, deletion_log.delete_username';
				$joinTables .= '
					LEFT JOIN kmk_deletion_log AS deletion_log ON
						(deletion_log.content_type = \'thread\' AND deletion_log.content_id = thread.thread_id)';
			}
		}

		if (isset($fetchOptions['readUserId']))
		{
			if (!empty($fetchOptions['readUserId']))
			{
				$autoReadDate = XenForo_Application::$time - (XenForo_Application::get('options')->readMarkingDataLifetime * 86400);

				$joinTables .= '
					LEFT JOIN kmk_thread_read AS thread_read ON
						(thread_read.thread_id = thread.thread_id
						AND thread_read.user_id = ' . $this->_getDb()->quote($fetchOptions['readUserId']) . ')';

				$joinForumRead = (!empty($fetchOptions['includeForumReadDate'])
					|| (!empty($fetchOptions['join']) && $fetchOptions['join'] & self::FETCH_FORUM)
				);
				if ($joinForumRead)
				{
					$joinTables .= '
						LEFT JOIN kmk_forum_read AS forum_read ON
							(forum_read.node_id = thread.node_id
							AND forum_read.user_id = ' . $this->_getDb()->quote($fetchOptions['readUserId']) . ')';

					$selectFields .= ",
						GREATEST(COALESCE(thread_read.thread_read_date, 0), COALESCE(forum_read.forum_read_date, 0), $autoReadDate) AS thread_read_date";
				}
				else
				{
					$selectFields .= ",
						IF(thread_read.thread_read_date > $autoReadDate, thread_read.thread_read_date, $autoReadDate) AS thread_read_date";
				}
			}
			else
			{
				$selectFields .= ',
					NULL AS thread_read_date';
			}
		}

		if (isset($fetchOptions['watchUserId']))
		{
			if (!empty($fetchOptions['watchUserId']))
			{
				$selectFields .= ',
					IF(thread_watch.user_id IS NULL, 0,
						IF(thread_watch.email_subscribe, \'watch_email\', \'watch_no_email\')) AS thread_is_watched';
				$joinTables .= '
					LEFT JOIN kmk_thread_watch AS thread_watch
						ON (thread_watch.thread_id = thread.thread_id
						AND thread_watch.user_id = ' . $this->_getDb()->quote($fetchOptions['watchUserId']) . ')';
			}
			else
			{
				$selectFields .= ',
					0 AS thread_is_watched';
			}
		}

		if (isset($fetchOptions['postCountUserId']))
		{
			if (!empty($fetchOptions['postCountUserId']))
			{
				$selectFields .= ',
					IF(thread_user_post.user_id IS NULL, 0, thread_user_post.post_count) AS user_post_count';
				$joinTables .= '
					LEFT JOIN kmk_thread_user_post AS thread_user_post
						ON (thread_user_post.thread_id = thread.thread_id
						AND thread_user_post.user_id = ' . $this->_getDb()->quote($fetchOptions['postCountUserId']) . ')';
			}
			else
			{
				$selectFields .= ',
					0 AS user_post_count';
			}
		}

		if (!empty($fetchOptions['permissionCombinationId']))
		{
			$selectFields .= ',
				permission.cache_value AS node_permission_cache';
			$joinTables .= '
				LEFT JOIN kmk_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $this->_getDb()->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'node\'
						AND permission.content_id = thread.node_id)';
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables,
			'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}

	
	
	/**
	 * @return KomuKuJVC_Model_ThreadMap
	 */	
	protected function _getListingClaimModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ListingClaim');
	}	
		
	
	/**
	 * @return KomuKuJVC_Model_ThreadMap
	 */	
	protected function _getThreadMapModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ThreadMap');
	}


}