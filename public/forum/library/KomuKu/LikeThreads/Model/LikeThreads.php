<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Model_LikeThreads extends XenForo_Model
{
    //Get constants
    const FETCH_USER = 0x01;
	const FETCH_THREAD = 0x02;
	const FETCH_FORUM = 0x04;
	const FETCH_NODE_PERMS = 0x08;
	
	//Get likes by id
	public function getLikesById($likeId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareLikesFetchOptions( $fetchOptions);
		
		return $this->_getDb()->fetchRow( '
			SELECT likes.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_liked_threads AS likes
			' . $joinOptions['joinTables'] . '
			WHERE like_id = ?
		', $likeId);
	}
	
	//Get likes by ids
	public function getLikesByIds(array $likeIds, array $fetchOptions = array())
	{
		if (! $likeIds)
		{
			return array();
		}
		
		$joinOptions = $this->prepareLikesFetchOptions( $fetchOptions);
		
		return $this->fetchAllKeyed( '
			SELECT likes.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_liked_threads AS likes
			' . $joinOptions['joinTables'] . '
			WHERE like_id IN (' . $this->_getDb()->quote( $likeIds) . ')
		', 'like_id');
	}

	//Count likes for individual threads
	public function countLikesForThreadId($threadId)
	{
        $count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS total
				FROM kmk_liked_threads
			WHERE thread_id = ?
		", $threadId);

		return $count['total'];
	}

	//Insert the likes in the db
	public function insertLikes($input)
	{
		$dw = XenForo_DataWriter::create('KomuKu_LikeThreads_DataWriter_LikeThreads');
		
		if (!empty($input['like_id']) AND $like = $this->getLikesById($input['like_id']))
		{
			$dw->setExistingData($like);
		}
		
		$dw->set('thread_id', $input['thread_id']);
		$dw->set('message', $input['message']);
		
		$dw->save();

		$likes = $this->countLikesForThreadId($input['thread_id']);

		if ($likes >= XenForo_Application::get('options')->like_threads_requirement)
		{
			$this->likeThresholdReachedAction($input['thread_id']);
			
		}

		return true;
	}

	//Set the sticky action when threads reach the like threshold
	public function likeThresholdReachedAction($threadId)
	{
		$options = XenForo_Application::get('options');
		$action = $options->like_sticky;

		$threadWriter = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
		$threadWriter->setExistingData($threadId);

		if(!empty($action))
        {		
			$threadWriter->set('sticky', 1);											
		}

		$threadWriter->save();

		return true;
	}
	
	//Get dislikes for thread
	public function getLikesByThread($threadId, array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults('
                SELECT likes.*, user.*
			    FROM `kmk_liked_threads` AS likes
			    LEFT JOIN kmk_user AS user 
			    ON (user.user_id = likes.user_id)
			    WHERE likes.thread_id = ?
			    ORDER BY likes.like_date DESC
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'like_id', $threadId);
    }
	
	//Get all thread likes
	public function getLikes(array $conditions = array(), array $fetchOptions = array())
	{
		$whereClause = $this->prepareLikesConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareLikesOrderOptions($fetchOptions, 'likes.like_date DESC');
		$joinOptions = $this->prepareLikesFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT likes.*
					' . $joinOptions['selectFields'] . '
				FROM kmk_liked_threads AS likes
				' . $joinOptions['joinTables'] . '
				WHERE ' . $whereClause . '
				' . $orderClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'like_id');
	}
	
	//Prevent abuse of liking threads by setting up a daily limit
	public function dailyLikeLimit(array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null) 
	{
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		$dailylikesgids = XenForo_Permission::hasPermission($viewingUser['permissions'], 'forum', 'dailyLikeLimit');
		
		if ($dailylikesgids > 0) 
		{
			$query = $this->countLikes(array('user_id' => $viewingUser['user_id'], 'like_date' => array('>', XenForo_Application::$time - 86400)));
			
			if ($query >= $dailylikesgids) 
			{
				$errorPhraseKey = array('th_daily_likes_reached', 'username' => $viewingUser['username'], 'dailylikesgids' => $dailylikesgids);
	            return false;
			}
		}
		
		return true;

	}
	
	//Count thread liked for user
	public function countThreadsLikedForUser($threadId)
	{
		$db = $this->_getDb();

		$visitor = XenForo_Visitor::getInstance();

		$thread = $db->fetchRow("
			SELECT user_id, like_count
			FROM kmk_thread
			WHERE thread_id = ?
		", $threadId);

		if ($thread['like_count'] >= XenForo_Application::get('options')->like_threads_requirement AND $thread['user_id'] != 0 )
		{
			$db->query("
				UPDATE kmk_user
				SET liked_thread_count = liked_thread_count + 1
				WHERE user_id = ?
			", $thread['user_id']);
		}
	}
	
	//Rebuild users liked threads count
	public function rebuildUserLikedThreadCount(array $userIds)
	{
		if (!is_array($userIds))
		{
			return false;
		}

		$db = $this->_getDb();

		XenForo_Db::beginTransaction($db);

		foreach ($userIds AS $userId)
		{
			$likedThreadCount = $db->fetchOne("
				SELECT COUNT(*)
				FROM kmk_thread
				WHERE user_id = ?
					AND like_count != 0
			", $userId);

			$db->update('kmk_user', array('liked_thread_count' => $likedThreadCount), 'user_id = ' . $db->quote($userId));
		}

		XenForo_Db::commit($db);

		return true;
	}
	
	//Get the like given by user in thread
	public function getLikesByUserForThreadId($userId, $threadId)
	{
		if (!$like = $this->_getDb()->fetchRow("
			SELECT *
				FROM kmk_liked_threads
			WHERE user_id = ? AND thread_id = ?
		", array($userId, $threadId)))
		{
			return false;
		}

		return $like;
	}
	
	//Count all likes that users have given.
	public function countAllForGiverUser($giverUserId, array $fetchOptions = array()) 
	{
		return $this->countLikes(array('user_id' => $giverUserId), $fetchOptions);
	}
	
	//Get the likes that an uer has given and show them at the visitor navigation.
	public function getAllForGiverUser($giverUserId, array $fetchOptions = array()) 
	{
		return $this->getLikes(array('user_id' => $giverUserId), $fetchOptions);
	}
	
	//Delete all likes from this thread
	public function deleteLikes($threadId)
	{
		$this->_getDb()->query("
			DELETE FROM kmk_liked_threads
			WHERE thread_id = ?
		", $threadId);
		
		//Reset like count to 0 for this thread.
		$this->_getDb()->query('
		   UPDATE kmk_thread 
		   SET like_count = 0
		   WHERE thread_id = ?
		   ', $threadId);

		return true;
	}
	
	//prepareLikesFetchOptions
	public function prepareLikesFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$db = $this->_getDb();
		
		if (! empty( $fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_THREAD)
			{
				$selectFields .= ',
					thread.*, thread.user_id AS thread_user_id, thread.username AS thread_username, likes.user_id, likes.username';
				
				$joinTables .= '
					INNER JOIN kmk_thread AS thread ON
						(thread.thread_id = likes.thread_id and thread.discussion_state = \'visible\')';
			}
			
			if ($fetchOptions['join'] & self::FETCH_FORUM && $fetchOptions['join'] & self::FETCH_THREAD)
			{
				$selectFields .= ',
					node.title AS node_title, node.node_name';
				$joinTables .= '
					INNER JOIN kmk_node AS node ON
						(node.node_id = thread.node_id)';
				
				$selectFields .= ',
						forum.* 
						, forum.last_post_id AS forum_last_post_id
						, forum.last_post_date AS forum_last_post_date
						, forum.last_post_user_id AS forum_last_post_user_id
						, forum.last_post_username AS forum_last_post_username
						, thread.last_post_id, thread.last_post_date, thread.last_post_user_id, thread.last_post_username
					';
				$joinTables .= '
					LEFT JOIN kmk_forum AS forum ON
						(forum.node_id = thread.node_id)'; 
			}
			
			if (!empty($fetchOptions['join']))
		    {
			   if ($fetchOptions['join'] & self::FETCH_USER)
			   {
				  $selectFields .= ',
						user.*, user_profile.*';
				  $joinTables .= '
						INNER JOIN kmk_user AS user ON
							(user.user_id = likes.user_id)
						INNER JOIN kmk_user_profile AS user_profile ON
							(user_profile.user_id = likes.user_id)';
			   }
		   }
			
			if ($fetchOptions['join'] & self::FETCH_NODE_PERMS)
			{
				$selectFields .= ',
					permission.cache_value AS node_permission_cache';
				$joinTables .= '
					LEFT JOIN kmk_permission_cache_content AS permission
						ON (permission.permission_combination_id = user.permission_combination_id
							AND permission.content_type = \'node\'
							AND permission.content_id = thread.node_id)';
			}
			
			if (!empty($fetchOptions['permissionCombinationId']))
			{
				$selectFields .= ',
				permission.cache_value AS node_permission_cache';
				$joinTables .= '
				LEFT JOIN kmk_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'node\'
						AND permission.content_id = thread.node_id)';
			}
		}
		
		return array(
			'selectFields' => $selectFields,
			'joinTables' => $joinTables
		);
	}
	
	//prepareLikesConditions
	public function prepareLikesConditions(array $conditions, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['user_id']))
		{
			if (is_array($conditions['user_id']))
			{
				$sqlConditions[] = 'likes.user_id IN (' . $db->quote($conditions['user_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'likes.user_id = ' . $db->quote($conditions['user_id']);
			}
		}

		if (!empty($conditions['thread_id']))
		{
			if (is_array($conditions['thread_id']))
			{
				$sqlConditions[] = 'likes.thread_id IN (' . $db->quote($conditions['thread_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'likes.thread_id = ' . $db->quote($conditions['thread_id']);
			}
		}			

		return $this->getConditionsForClause($sqlConditions);
	}	
		
	//prepareLikesOrderOptions
	public function prepareLikesOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'like_date' => 'likes.like_date',
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}	
	
	//Count the number of likes that meet the given criteria.
	public function countLikes(array $conditions = array())
	{
		$fetchOptions = array();

		$whereClause = $this->prepareLikesConditions($conditions, $fetchOptions);
		$joinOptions = $this->prepareLikesFetchOptions($fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM kmk_liked_threads AS likes
			' . $joinOptions['joinTables'] . '
			WHERE ' . $whereClause
		);
	}
	
	//Delete all user's thread likes.
	public function getLikesByUser($userId)
	{
		return $this->fetchAllKeyed("
			SELECT *
			FROM kmk_liked_threads
			WHERE user_id = ?
			ORDER BY like_date DESC
		", 'like_id', $userId);
	}

	/**
	 * @param integer $userId
	 *
	 * @return int Total likings deleted
	 */
	public function deleteLikesByUser($userId)
	{
		$likings = $this->getLikesByUser($userId);
		
		$i = 0;
		
		foreach ($likings AS $liking)
		{
			$dw = XenForo_DataWriter::create('KomuKu_LikeThreads_DataWriter_LikeThreads');
			$dw->setExistingData($liking, true);
			$dw->delete();
			$i++;
		}

		return $i;
	}
	
	//Top liked threads in the forum index
	public function getLikedThreads($limit = 0)
    {
        $visitor = XenForo_Visitor::getInstance();

        $exclforums = XenForo_Application::get('options')->exluded_liked_threads_fids;
		
        $conditions = array(
            'deleted' => false,
            'moderated' => false
        );

        $fetchOptions = array(
            'join' => XenForo_Model_Thread::FETCH_USER,
            'permissionCombinationId' => $visitor['permission_combination_id'],
            'readUserId' => $visitor['user_id'],
            'watchUserId' => $visitor['user_id'],
            'postCountUserId' => $visitor['user_id'],
            'order' => 'like_count',
            'orderDirection' => 'desc',
            'limit' => $limit,
        );


        $whereConditions = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadConditions($conditions, $fetchOptions);
        $sqlClauses = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadFetchOptions($fetchOptions);
        $limitOptions = $this->getModelFromCache('XenForo_Model_Thread')->prepareLimitFetchOptions($fetchOptions);

        if (!empty($exclforums))
        {
            $whereConditions .= ' AND thread.node_id NOT IN (' . $this->_getDb()->quote($exclforums) . ')';
        }
		
		$whereConditions .= ' AND thread.like_count > 0';

        $sqlClauses['joinTables'] = str_replace('(user.user_id = thread.user_id)', '(user.user_id = thread.user_id)', $sqlClauses['joinTables']);

        $threads = $this->fetchAllKeyed($this->limitQueryResults('
				SELECT thread.*
					' . $sqlClauses['selectFields'] . '
				FROM kmk_thread AS thread
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
        ), 'thread_id');

        foreach($threads AS $threadId => &$thread)
        {
            if ($this->getModelFromCache('XenForo_Model_Thread')->canViewThreadAndContainer($thread, $thread))
            {
                $thread = $this->getModelFromCache('XenForo_Model_Thread')->prepareThread($thread, $thread);
                $thread['canInlineMod'] = false;
            }
            else
            {
                unset($threads[$threadId]);
            }
        }

        return $threads;
    }
	
	//Top liked threads in the navigation tab
	public function getLikedThreadsArchieve(array $fetchOptions = array())
    {
	    $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
        $visitor = XenForo_Visitor::getInstance();

        $exclforums = XenForo_Application::get('options')->exluded_liked_threads_fids;
		
        $conditions = array(
            'deleted' => false,
            'moderated' => false
        );

        $fetchOptions = array(
            'join' => XenForo_Model_Thread::FETCH_USER,
            'permissionCombinationId' => $visitor['permission_combination_id'],
            'readUserId' => $visitor['user_id'],
            'watchUserId' => $visitor['user_id'],
            'postCountUserId' => $visitor['user_id'],
            'order' => 'like_count',
            'orderDirection' => 'desc',
        );


        $whereConditions = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadConditions($conditions, $fetchOptions);
        $sqlClauses = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadFetchOptions($fetchOptions);

        if (!empty($exclforums))
        {
            $whereConditions .= ' AND thread.node_id NOT IN (' . $this->_getDb()->quote($exclforums) . ')';
        }
		
		$whereConditions .= ' AND thread.like_count > 0';

        $sqlClauses['joinTables'] = str_replace('(user.user_id = thread.user_id)', '(user.user_id = thread.user_id)', $sqlClauses['joinTables']);

        $threads = $this->fetchAllKeyed($this->limitQueryResults('
				SELECT thread.*
					' . $sqlClauses['selectFields'] . '
				FROM kmk_thread AS thread
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
        ), 'thread_id');

        foreach($threads AS $threadId => &$thread)
        {
            if ($this->getModelFromCache('XenForo_Model_Thread')->canViewThreadAndContainer($thread, $thread))
            {
                $thread = $this->getModelFromCache('XenForo_Model_Thread')->prepareThread($thread, $thread);
                $thread['canInlineMod'] = false;
            }
            else
            {
                unset($threads[$threadId]);
            }
        }

        return $threads;
    }
	
	//Can like threads permissions
	public function canLikeThreads(array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null) 
	{
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		//Don't allow liking if thread is moderated/not visible
		if ($thread['discussion_state'] != 'visible')
		{
			return false;
		}

		//You can't like your own threads silly :D
		if ($thread['user_id'] == $viewingUser['user_id'])
		{			
			$errorPhraseKey = 'th_no_like_own_threads';
			return false;
		}
		
		//Don't like threads if you do not have permissiosn to view them
		if (!$this->getModelFromCache('XenForo_Model_Thread')->canViewThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) 
		{
			return false;
		}
		
		//Get the groups who can like threads
		if (!XenForo_Permission::hasPermission($viewingUser['permissions'], 'forum', 'canViewLikes')) 
		{
			return false;
		}

		return true;
	}
	
	//Can view thread likes for own threads or all threads
	public function canViewThreadLikes(array $thread, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

		//For all threads
		if (XenForo_Permission::hasContentPermission($nodePermissions, 'viewAnyThreadLikes'))
		{
            return true;
		}

		//For own threads only
		if (XenForo_Permission::hasContentPermission($nodePermissions, 'viewOwnThreadLikes') && $thread['user_id'] == $viewingUser['user_id'])
		{
            return true; 
		}
		
		return false;
	}
	
	//Can delete thread likes for own threads or all threads
	public function canDeleteThreadLikes(array $thread, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

		//For all threads
		if (XenForo_Permission::hasContentPermission($nodePermissions, 'deleteAnyThreadLikes'))
		{
            return true;
		}

		//For own threads only
		if (XenForo_Permission::hasContentPermission($nodePermissions, 'deleteOwnThreadLikes') && $thread['user_id'] == $viewingUser['user_id'])
		{
            return true; 
		}
		
		return false;
	}
	
	//Checks to see if the user can view newsfeed likes.	
	public function canViewNewsFeedLikes(array $thread, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'forum', 'canViewLikes'))
		{
			return true;	
		}
		
		return false;
	}
	
	//Can view most liked threads page
	public function canViewMostLikedPage(array $user = null)
	{
		$this->standardizeViewingUserReference($user);

		return XenForo_Permission::hasPermission($user['permissions'], 'forum', 'canViewLikesArchive');		
	}
	
	//Send like threads alert
	public function sendThreadLikeAlert($alertType, $threadId, array $threadstarters, XenForo_Visitor $visitor = null)
	{
		$visitor = XenForo_Visitor::getInstance(); 
		
		if (!$visitor)
		{
			$visitor = XenForo_Visitor::getInstance();
		}

		if (!empty($threadstarters))
		{
			foreach ($threadstarters AS $threadstarter)
			{
				$user = $this->_getUserModel()->getUserByName($threadstarter);
				
				if (XenForo_Model_Alert::userReceivesAlert($user, 'thread', $alertType))
				{
					XenForo_Model_Alert::alert($user['user_id'],
							$visitor['user_id'], $visitor['username'],
							'thread', 
							$threadId,
							$alertType
					);
				}
			}
		}
		
		return false;
	}
	
	/**
	 * @return XenForo_Model_User
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}	
}