<?php

/**
 * Model similar to threads.
 * ThreadMap : Maps the directory details to the thread and contains map/video/contact/extra directory details
 * @package KomuKuJVC_Model_ThreadMap
 */
class KomuKuJVC_Model_ThreadMap extends XenForo_Model
{

	/**
	 * Returns a thread record based
	 *
	 * @param integer $threadId
	 * @param array $fetchOptions Collection of options related to fetching
	 *
	 * @return array|false
	 */
	public function getThreadMapById($threadId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareThreadMapFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT threadmap.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_thread_map AS threadmap
			' . $joinOptions['joinTables'] . '
			WHERE threadmap.thread_id = ?
		', $threadId);
	}

	/**
	 * Gets the named threads.
	 *
	 * @param array $threadIds
	 * @param array $fetchOptions Collection of options related to fetching
	 *
	 * @return array Format: [thread id] => info
	 */
	public function getThreadMapsByIds(array $threadIds, array $fetchOptions = array())
	{
		if (!$threadIds)
		{
			return array();
		}

		$joinOptions = $this->prepareThreadMapFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT threadmap.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_thread_map AS threadmap' . $joinOptions['joinTables'] . '
			WHERE threadmap.thread_id IN (' . $this->_getDb()->quote($threadIds) . ')
		', 'thread_id');
	}

	
	// need this until I can figure out how to send url correctly
	public function getThreadTitleFromId($threadId){
		$db = XenForo_Application::get('db'); 
	    $titleQuery = "SELECT title FROM kmk_thread Where thread_id = '".$threadId."'";
		$row = $db->fetchRow($titleQuery);			
		return 	$row['title'];				
	}	
	
	/**
	 * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
	 * and returns SQL snippets to join the specified tables if required
	 *
	 * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
	 *
	 * @return array Containing selectFields, joinTables, orderClause keys.
	 * 		Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '; orderClause = ORDER BY x.y
	 */
	public function prepareThreadMapFetchOptions(array $fetchOptions)
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
	 * Prepares a collection of thread fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareThreadMapConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['forum_id']) && empty($conditions['node_id']))
		{
			$conditions['node_id'] = $conditions['forum_id'];
		}

		if (!empty($conditions['node_id']))
		{
			if (is_array($conditions['node_id']))
			{
				$sqlConditions[] = 'thread.node_id IN (' . $db->quote($conditions['node_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'thread.node_id = ' . $db->quote($conditions['node_id']);
			}
		}

		if (!empty($conditions['prefix_id']))
		{
			if (is_array($conditions['prefix_id']))
			{
				$sqlConditions[] = 'thread.prefix_id IN (' . $db->quote($conditions['prefix_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'thread.prefix_id = ' . $db->quote($conditions['prefix_id']);
			}
		}

		if (isset($conditions['sticky']))
		{
			$sqlConditions[] = 'thread.sticky = ' . ($conditions['sticky'] ? 1 : 0);
		}

		if (isset($conditions['deleted']) || isset($conditions['moderated']))
		{
			$sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'thread', 'discussion_state');
		}

		if (!empty($conditions['last_post_date']) && is_array($conditions['last_post_date']))
		{
			list($operator, $cutOff) = $conditions['last_post_date'];

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "thread.last_post_date $operator " . $db->quote($cutOff);
		}

		// fetch threads only from forums with find_new = 1
		if (!empty($conditions['find_new']) && isset($fetchOptions['join']) && $fetchOptions['join'] & self::FETCH_FORUM_OPTIONS)
		{
			$sqlConditions[] = 'forum.find_new = 1';
		}

		// thread starter
		if (isset($conditions['user_id']))
		{
			$sqlConditions[] = 'thread.user_id = ' . $db->quote($conditions['user_id']);
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Gets threads and threadMaps that match the given conditions.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [thread id] => info
	 */
	public function getThreadMaps(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->prepareThreadConditions($conditions, $fetchOptions);

		$sqlClauses = $this->prepareThreadMapFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT threadMap.*, thread.*
					' . $sqlClauses['selectFields'] . '
				FROM kmk_thread AS thread
				' . $sqlClauses['joinTables'] . '
				LEFT JOIN kmk_jobvacan_thread_map AS threadMap ON (threadMap.thread_id = thread.thread_id)
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'thread_id');
	}



	
	/**
	 * Gets the count of threadMaps with the specified criteria.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countThreadMaps(array $conditions)
	{
		$fetchOptions = array();
		$whereConditions = $this->prepareThreadConditions($conditions, $fetchOptions);
		$sqlClauses = $this->prepareThreadMapFetchOptions($fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM kmk_jobvacan_thread_map AS threadMap
			' . $sqlClauses['joinTables'] . '
			WHERE ' . $whereConditions . '
		');
	}

	/**
	 * Gets an array of the node IDs in which the specified threads reside
	 *
	 * @param array $threads
	 *
	 * @return array
	 */
	public function getNodeIdsFromThreadMaps(array $threadMaps)
	{
		$nodeIds = array();
		foreach ($threadMaps AS $threadMap)	{$nodeIds[] = $threadMap['node_id'];}
		return array_unique($nodeIds);
	}


	/**
	 * Helper to delete the specified threadMap, via a soft or hard delete.
	 *
	 * ToDo
	 *
	 */
	public function deleteThreadMap($threadId, $deleteType, array $options = array())
	{
		
		$options = array_merge(array(
			'reason' => ''
		), $options);

		$dw = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		$dw->setExistingData($threadId);
		if ($deleteType == 'hard')
		{
			$dw->delete();
		}
		return $dw;
	}


	 

	
	/**
	 * Gets the category id (main category, + 4 extra categories) for a given threadId
	 *
	 */
	public function getAllCategoriesByThreadId($threadId)
	{
		return $this->_getDb()->fetchRow('
			SELECT directory_category AS cat1, cat2, cat3, cat4, cat5, 
			cat1title, cat2title, cat3title, cat4title, cat5title
			FROM kmk_jobvacan_thread_map 
			WHERE thread_id  = '.$threadId.' 				
			');					
	}
	
	

	
	
	public function countThreadForThisDir($ForumID,$DirID, array $conditions){
		$fetchOptions = array();
		$whereConditions = $this->prepareThreadMapConditions($conditions, $fetchOptions);
		$sqlClauses = $this->prepareThreadMapFetchOptions($fetchOptions);
		
		$count= $this->_getDb()->fetchOne('
			SELECT COUNT("node_id") as C
			FROM kmk_thread AS thread, kmk_jobvacan_thread_map AS map
			WHERE 
			' . $whereConditions . '
			And
			thread.node_id = '.$ForumID.' AND
			thread.thread_id = map.thread_id 
			AND (
				map.directory_category = '.$DirID.' OR
				map.cat2 = '.$DirID.' OR 
				map.cat3 = '.$DirID.' OR 
				map.cat4 = '.$DirID.' OR 
				map.cat5 = '.$DirID.'  
				)
			');
		return $count;
	}

	/**
	 * Updates counters by category 
	 *
	 */	
	 
	public function rebuildCategoryCountsById($category_id)
	{
		 	$thisSQL = '
		 		UPDATE kmk_jobvacan_dir SET discussion_count = 
		 			(
		 				SELECT count(directory_category) 
		 				FROM kmk_jobvacan_thread_map 
		 				WHERE
		 				directory_category ='.$category_id.' OR
		 				cat2 = '.$category_id.' OR 
						cat3 = '.$category_id.' OR 
						cat4 = '.$category_id.' OR 
						cat5 = '.$category_id.'
		 			) 
		 			WHERE node_id = '.$category_id.'';
			$this->_getDb()->query($thisSQL);
	return true;
	}	
		
	public function rebuildCategoryCountsByIds(array $category_ids)
	{
		foreach ($category_ids AS $category_id){	
		 	$thisSQL = '
		 		UPDATE kmk_jobvacan_dir SET discussion_count = 
		 			(
		 				SELECT count(directory_category) 
		 				FROM kmk_jobvacan_thread_map 
		 				WHERE
		 				directory_category ='.$category_id.' OR
		 				cat2 = '.$category_id.' OR 
						cat3 = '.$category_id.' OR 
						cat4 = '.$category_id.' OR 
						cat5 = '.$category_id.'
		 			) 
		 			WHERE node_id = '.$category_id.'';
			$this->_getDb()->query($thisSQL);
		}
	return true;
	}

	
		
	public function rebuildAllCategoryCounts()
	{
		// This function is expensive with many categories, avoid using unless we have to!  	
		
		// remove orphanded categories	
		// if thread no longer exists in kmk_thread, remove it from kmk_jobvacan_thread_map
		$this->_getDb()->query("delete from kmk_jobvacan_thread_map where thread_id not in (select thread_id from kmk_thread)");
		// get all categories
		$stmt = $this->_getDb()->fetchCol('select node_id from kmk_jobvacan_dir');
		foreach ($stmt AS $directory_category){	
		// go through each catgories, including the parents and update count 
		 	$thisSQL = '
		 		UPDATE kmk_jobvacan_dir SET discussion_count = 
		 			(
		 				SELECT count(directory_category) 
		 				FROM kmk_jobvacan_thread_map 
		 				WHERE
		 				directory_category ='.$directory_category.' OR
		 				cat2 = '.$directory_category.' OR 
						cat3 = '.$directory_category.' OR 
						cat4 = '.$directory_category.' OR 
						cat5 = '.$directory_category.'
		 			) 
		 			WHERE node_id = '.$directory_category.'';
	
			$this->_getDb()->query($thisSQL);
		}
	return true;
	}
	
	
	
	
	
		
	
	/**
	 * @return KomuKuJVC_Model_ThreadMap
	 */	
	protected function _getThreadMapModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ThreadMap');
	}

	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}

	/**
	 * @return XenForo_Model_Post
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
}