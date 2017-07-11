<?php

class KomuKu_SimilarThreads_Model extends XenForo_Model
{
	public function getThreads($searchWord1,$searchWord2,$searchWord3,$currentNodeId,$currentThreadId)
	{
		// declare variables
		$whereclause1 = '';
		$whereclause2 = '';
		$whereclause3 = '';
		$whereclause4 = '';
		$whereclause5 = '';
		$results = array();
		$results1 = array();
		$results2 = array();
		$results3 = array();
		$results4 = array();
		$excludeResults1 = '';
		$excludeResults2 = '';
		$excludeResults3 = '';
		$excludeResults4 = '';
		$resultsCount1 = array();
		$resultsCount2 = array();
		$resultsCount3 = array();
		$resultsCount4 = array();
	
		//########################################
		// $whereclause1
		// viewable node permissions
		//########################################
		
		// get options from Admin CP -> Options -> Similar Threads -> Same Forum  
		$sameForum = XenForo_Application::get('options')->similarThreadsSameForum;		
		
		if (!$sameForum)
		{		
			// get node list
			$viewableNodes = $this->getModelFromCache('XenForo_Model_Node')->getViewableNodeList();
			
			// get $nodeIds
			foreach ($viewableNodes as $node)
			{
				$nodeIds[] = $node['node_id'];
			}
			
			// create whereclause of viewable nodes
			$whereclause1 = 'AND (kmk_thread.node_id = ' . implode(' OR kmk_thread.node_id = ', $nodeIds);
			$whereclause1 = $whereclause1 . ')';
		}
		
		//########################################
		// $whereclause2
		// exclude thread that is being viewed
		//########################################
		
		// if coming from Thread.php don't include the thread we are viewing
		if (isset($currentThreadId))
		{
			$whereclause2 = "AND kmk_thread.thread_id <> '$currentThreadId'";
		}
		
		//########################################
		// $whereclause3
		// show results from same forum
		//########################################
		
		// get options from Admin CP -> Options -> Similar Threads -> Show Results From Same Forum    
        $sameForum = XenForo_Application::get('options')->similarThreadsSameForum;
		
		// check if coming from Thread.php 
		$visitor = XenForo_Visitor::getInstance();
        $userId = $visitor['user_id'];	
		 
		$params = $this->_getDb()->fetchOne("
		SELECT params
		FROM kmk_session_activity
		WHERE user_id = ?
		AND controller_action = 'CreateThread'
		", $userId);
		
		if ($params != '') 
		{
			$pos1 = strpos($params,'node_id=');
			
			if (is_numeric($pos1))
			{
				$currentNodeId = substr($params,8);
			}
		}
		
		// create $whereclause3				
		if ($sameForum == 1 AND $currentNodeId != '')
		{
			$whereclause3 = "AND kmk_thread.node_id = '$currentNodeId'";
		}
		
		//########################################
		// $whereclause4
		// exclude forums
		//########################################
		
		// get options from Admin CP -> Options -> Similar Threads -> Exclude Forums  
		$excludeForumsArray = XenForo_Application::get('options')->similarThreadsExcludeForums;
		
		// run if not empty
		if (!empty($excludeForumsArray))
		{
			// run if not (unspecified)
			if ($excludeForumsArray[0] != 0)
			{
				// create whereclause of nodes to exclude
				$whereclause4 = 'AND (kmk_thread.node_id <> ' . implode(' AND kmk_thread.node_id <> ', $excludeForumsArray);
				$whereclause4 = $whereclause4 . ')';
			}
		}
		
		//########################################
		// misc variables
		//########################################		
		
		// get option from Admin CP -> Options -> Similar Threads -> Maximum Results	
		$maximumResults = XenForo_Application::get('options')->similarThreadsMaximumResults; 
		
		// get options from Admin CP -> Options -> Similar Threads -> Exclude Days    
		$excludeDays = XenForo_Application::get('options')->similarThreadsExcludeDays;	
		
		if ($excludeDays > 0)
		{
			// convert to Unix timestamp
			$gte = time() - (86400 * $excludeDays);
		}
		else
		{
			$gte = 0;
		}				

		//########################################
		// search 1
		//########################################

		if ($searchWord1 != '' AND $searchWord2 != '' AND $searchWord3 != '')
		{
			$maximumResults1 = $maximumResults;
			
			// get threads
			$results1 = $this->_getDb()->fetchAll("
			SELECT kmk_thread.thread_id, 
			kmk_thread.node_id,
			kmk_thread.title, 
			kmk_thread.reply_count,
			kmk_thread.view_count, 
			kmk_thread.username, 
			kmk_thread.post_date,
			kmk_thread.last_post_date, 
			kmk_thread.last_post_user_id, 
			kmk_thread.last_post_username, 
			kmk_thread.prefix_id, 			 
			kmk_node.title AS nodeTitle, 
			kmk_user.user_id, 
			kmk_user.gender, 
			kmk_user.avatar_date	
			FROM kmk_thread
			INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
			INNER JOIN kmk_user ON kmk_user.user_id = kmk_thread.user_id
			WHERE kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord1, 'lr') . "
			AND kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord2, 'lr') . "
			AND kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord3, 'lr') . "
			AND kmk_thread.discussion_state = 'visible'
			AND kmk_thread.discussion_type <> 'redirect'
			AND kmk_thread.post_date >= ?
			$whereclause1
			$whereclause2
			$whereclause3
			$whereclause4
			ORDER BY kmk_thread.post_date DESC
			LIMIT ?
			", array($gte, $maximumResults1));	
			
			// prepare results for return
			$results = $results1;
		}
		
		//########################################
		// search 2
		//########################################		
		
		if ($searchWord1 != '' AND $searchWord2 != '')
		{
			foreach ($results1 AS $k => $v)
			{
				$resultsCount1[] = $v['thread_id'];
				
				// exclude previously found thread_id's
				$excludeResults1 = 'AND kmk_thread.thread_id <> ' . implode(' AND kmk_thread.thread_id <> ', $resultsCount1);
			}
			
			$count = count($resultsCount1);	
			
			if ($count < $maximumResults AND is_numeric($count))
			{
				$maximumResults2 = $maximumResults - $count;					
						
				// get threads
				$results2 = $this->_getDb()->fetchAll("
				SELECT kmk_thread.thread_id, 
				kmk_thread.node_id,
				kmk_thread.title, 
				kmk_thread.reply_count,
				kmk_thread.view_count, 
				kmk_thread.username, 
				kmk_thread.post_date,
				kmk_thread.last_post_date, 
				kmk_thread.last_post_user_id, 
				kmk_thread.last_post_username, 
				kmk_thread.prefix_id, 			 
				kmk_node.title AS nodeTitle, 
				kmk_user.user_id, 
				kmk_user.gender, 
				kmk_user.avatar_date	
				FROM kmk_thread
				INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
				INNER JOIN kmk_user ON kmk_user.user_id = kmk_thread.user_id
				WHERE kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord1, 'lr') . "
				AND kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord2, 'lr') . "
				AND kmk_thread.discussion_state = 'visible'
				AND kmk_thread.discussion_type <> 'redirect'
				AND kmk_thread.post_date >= ?
				$whereclause1
				$whereclause2
				$whereclause3
				$whereclause4
				$excludeResults1
				ORDER BY kmk_thread.post_date DESC
				LIMIT ?
				", array($gte, $maximumResults2));	
				
				// prepare results for return
				$results = array_merge($results1, $results2);
			}
		}		
		
		//########################################
		// search 3
		//########################################

		if ($searchWord1 != '')
		{
			foreach ($results2 AS $k => $v)
			{
				$resultsCount2[] = $v['thread_id'];
				
				// exclude previously found thread_id's
				$excludeResults2 = 'AND kmk_thread.thread_id <> ' . implode(' AND kmk_thread.thread_id <> ', $resultsCount2);
			}
			
			$count = count($resultsCount1) + count($resultsCount2);
			
			if ($count < $maximumResults AND is_numeric($count))
			{
				$maximumResults3 = $maximumResults - $count;
			
				// get threads
				$results3 = $this->_getDb()->fetchAll("
				SELECT kmk_thread.thread_id, 
				kmk_thread.node_id,
				kmk_thread.title, 
				kmk_thread.reply_count,
				kmk_thread.view_count, 
				kmk_thread.username, 
				kmk_thread.post_date,
				kmk_thread.last_post_date, 
				kmk_thread.last_post_user_id, 
				kmk_thread.last_post_username, 
				kmk_thread.prefix_id, 			 
				kmk_node.title AS nodeTitle, 
				kmk_user.user_id, 
				kmk_user.gender, 
				kmk_user.avatar_date		
				FROM kmk_thread
				INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
				INNER JOIN kmk_user ON kmk_user.user_id = kmk_thread.user_id
				WHERE kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord1, 'lr') . "
				AND kmk_thread.discussion_state = 'visible'
				AND kmk_thread.discussion_type <> 'redirect'
				AND kmk_thread.post_date >= ?
				$whereclause1
				$whereclause2
				$whereclause3
				$whereclause4
				$excludeResults1
				$excludeResults2
				ORDER BY kmk_thread.post_date DESC
				LIMIT ?
				", array($gte, $maximumResults3));	
				
				// prepare results for return
				$results = array_merge($results1, $results2, $results3);
			}
		}
		
		//########################################
		// search 4
		//########################################
		
		if ($searchWord2 != '')
		{			
			foreach ($results3 AS $k => $v)
			{
				$resultsCount3[] = $v['thread_id'];
				
				// exclude previously found thread_id's
				$excludeResults3 = 'AND kmk_thread.thread_id <> ' . implode(' AND kmk_thread.thread_id <> ', $resultsCount3);
			}
			
			$count = count($resultsCount1) + count($resultsCount2) + count($resultsCount3);
			
			if ($count < $maximumResults AND is_numeric($count))
			{
				$maximumResults4 = $maximumResults - $count;
				
				// get threads
				$results4 = $this->_getDb()->fetchAll("
				SELECT kmk_thread.thread_id, 
				kmk_thread.node_id,
				kmk_thread.title, 
				kmk_thread.reply_count,
				kmk_thread.view_count, 
				kmk_thread.username, 
				kmk_thread.post_date, 
				kmk_thread.last_post_date, 
				kmk_thread.last_post_user_id, 
				kmk_thread.last_post_username, 
				kmk_thread.prefix_id, 			 
				kmk_node.title AS nodeTitle, 
				kmk_user.user_id, 
				kmk_user.gender, 
				kmk_user.avatar_date		
				FROM kmk_thread
				INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
				INNER JOIN kmk_user ON kmk_user.user_id = kmk_thread.user_id
				WHERE kmk_thread.title LIKE " . XenForo_Db::quoteLike($searchWord2, 'lr') . "
				AND kmk_thread.discussion_state = 'visible'
				AND kmk_thread.discussion_type <> 'redirect'
				AND kmk_thread.post_date >= ?
				$whereclause1
				$whereclause2
				$whereclause3
				$whereclause4
				$excludeResults1
				$excludeResults2
				$excludeResults3
				ORDER BY kmk_thread.post_date DESC
				LIMIT ?
				", array($gte, $maximumResults4));	
				
				// prepare results for return
				$results = array_merge($results1, $results2, $results3, $results4);
			} 
		}	

		return $results;	
	}
}