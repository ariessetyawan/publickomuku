<?php

/**
 * Model for Dir
 * (Forum / Directory Node and category related model)
 * General Directory related functions
 * @package XenForo_Forum
 */
class KomuKuJVC_Model_Dir extends XenForo_Model_Forum
{
	
	
	 /**
	 * Fetches the statitic counts for directory listings (given the directory node id)
	 * @param integer $node_id Node ID
	 *
	 * @return array
	 */
	public function getStatisticCountsByNodeId($node_id)
	{
		$sql= $this->_getDb()->fetchRow("
		SELECT catagory_count, sub_catagory_count, active_count, non_active_count, review_count".
		" FROM (".
		" SELECT count(title) AS catagory_count".
		" FROM kmk_jobvacan_directory_node".
		" WHERE parent_node_id =0".
		") AS a, (".
			 	
		" SELECT count(title) AS sub_catagory_count".
		" FROM kmk_jobvacan_directory_node".
		" WHERE parent_node_id !=0".
		") AS b, (".

		" SELECT count(node_id) AS active_count".
		" FROM kmk_thread".
		" WHERE node_id = '$node_id'".
		" AND discussion_state = 'visible'".
		") AS c, (".
							
		" SELECT count(node_id) AS non_active_count".
		" FROM kmk_thread".
		" WHERE node_id = '$node_id'".
		" AND discussion_state != 'visible'".
		") AS d, (".
													
		" SELECT count(kmk_post.thread_id) AS review_count".
		" FROM kmk_post".
		" LEFT JOIN kmk_thread ON kmk_post.thread_id = kmk_thread.thread_id".
		" WHERE kmk_thread.node_id = '$node_id'".
		" AND kmk_post.position > 0".
		" AND kmk_thread.discussion_state = 'visible'".
		") AS e");
	return $sql;	
	}
	
	/**
	 * Fetches the combined node-forum record for the specified node id
	 *
	 * @param integer $id Node ID
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array
	 */
	public function getForumById($id, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);

		$sqls= $this->_getDb()->fetchRow('
			SELECT node.*, forum.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_dir AS forum
			INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
			' . $joinOptions['joinTables'] . '
			WHERE node.node_id = ?
		', $id);
		
		return $sqls;
	}

	/**
	 * Fetches the combined node-forum record for the specified node name
	 *
	 * @param string $name Node name
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array
	 */
	public function getForumByNodeName($name, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT node.*, forum.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_dir AS forum
			INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
			' . $joinOptions['joinTables'] . '
			WHERE node.node_name = ?
				AND node.node_type_id = \'Forum\'
		', $name);
	}

	/**
	 * Fetches the combined node-forum records for the specified forum/node IDs.
	 *
	 * @param array $forumIds
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array Format: [node id] => info
	 */
	public function getForumsByIds(array $forumIds, array $fetchOptions = array())
	{
		if (!$forumIds)
		{
			return array();
		}

		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT node.*, forum.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_dir AS forum
			INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
			' . $joinOptions['joinTables'] . '
			WHERE node.node_id IN (' . $this->_getDb()->quote($forumIds) . ')
		', 'node_id');
	}


	
	/**
	* Gets an ordered (parent category contains child) object from a list of all directory categories
	*
	* @return array
	*/
	public function getCategoryList(array $allDirs)
	{
		 // set a blank option, so an error is thrown when user gets to here somehow and forgets to set this
		$category_list[] = array( 
			'url' => '',
			'title'=> '', 
			'node_id'=> '', 
			'discussion_count' => '',
			'child_nodes' => '',
			'parent_node_id' => '' 
		);		
		foreach ($allDirs AS $thisDir)
		{
		// if we are at the top level, just get all the dirs with no parents, and put the relavant children in		
			if($thisDir['parent_node_id'] == ""){
			//$category_list[$thisDir['title']] = $this->getChildDirs($thisDir['node_id'],$allDirs);
				$category_list[] = array( 
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'], 
					'node_id'=>$thisDir['node_id'], 
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $this->getChildrenNameAndId($thisDir['node_id'],$allDirs),
					'parent_node_id' => ''					 
				);
				
			}
		}	
			
	return $category_list;	
	}	
	
	/**
	* gets a Flat Category list as a 1D array, Children contain an extra dash 
	* 
	* {parant, -- child, -- child, ---- childchild, parent, -- child}
	* 
	* Prepared for a combo/select box
	* 
	* @return array
	*/
	public function getFlatCatList(array $allDirs) {
		// must find a better way of doing this!
		
		// set a blank option, so an error is thrown when user gets to here somehow and forgets to set this
		$flat_cat_list[] = array( 
			'url' => '', 'title' => '', 'dashtitle' => '', 'node_id' => '', 'discussion_count' => '', 'parent_node_id' => ''	
		);		

		// 1st get all of the parants, then for each parant get all of the children
		foreach ($allDirs AS $thisDir)
		{
			if($thisDir['parent_node_id'] == ""){
				$flat_cat_list[] = array( 
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'], 'dash_title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'], 'discussion_count' => $thisDir['discussion_count'],
					'parent_node_id' => ''					 
				);	
			
				// children of $thisDir['node_id']
				foreach ($allDirs AS $thisDirLvl2){	
					if($thisDirLvl2['parent_node_id'] == $thisDir['node_id']){
						$flat_cat_list[] = array( 
							'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDirLvl2['node_id'], $thisDirLvl2['title'], true),
							'title'=> $thisDirLvl2['title'], 'dash_title'=> " -- ".$thisDirLvl2['title'], 
							'node_id'=>$thisDirLvl2['node_id'], 'discussion_count' => $thisDirLvl2['discussion_count'],
							'parent_node_id' => $thisDirLvl2['parent_node_id']					 
						);
						
						// children of $thisDirLvl2['node_id']
						foreach ($allDirs AS $thisDirLvl3){	
							if($thisDirLvl3['parent_node_id'] == $thisDirLvl2['node_id']){
								$flat_cat_list[] = array( 
									'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDirLvl3['node_id'], $thisDirLvl3['title'], true),
									'title'=> $thisDirLvl3['title'], 'dash_title'=> " ---- ".$thisDirLvl3['title'],  
									'node_id'=>$thisDirLvl3['node_id'], 'discussion_count' => $thisDirLvl3['discussion_count'],
									'parent_node_id' => $thisDirLvl3['parent_node_id']				 
								);
								
								// children of $thisDirLvl3['node_id']
								foreach ($allDirs AS $thisDirLvl4){	
									if($thisDirLvl4['parent_node_id'] == $thisDirLvl3['node_id']){
										$flat_cat_list[] = array( 
											'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDirLvl4['node_id'], $thisDirLvl4['title'], true),
											'title'=> $thisDirLvl4['title'], 'dash_title'=> " ------ ".$thisDirLvl4['title'], 
											'node_id'=>$thisDirLvl4['node_id'], 'discussion_count' => $thisDirLvl4['discussion_count'],
											'parent_node_id' => $thisDirLvl4['parent_node_id']					 
										);								
								
										// children of $thisDirLvl4['node_id']
										foreach ($allDirs AS $thisDirLvl5){	
											if($thisDirLvl5['parent_node_id'] == $thisDirLvl4['node_id']){
												$flat_cat_list[] = array( 
													'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDirLvl5['node_id'], $thisDirLvl5['title'], true),
													'title'=> $thisDirLvl5['title'], 'dash_title'=> " -------- ".$thisDirLvl5['title'], 
													'node_id'=>$thisDirLvl5['node_id'], 'discussion_count' => $thisDirLvl5['discussion_count'],
													'parent_node_id' => $thisDirLvl5['parent_node_id']		
												);									
												
												// children of $thisDirLvl5['node_id']
												foreach ($allDirs AS $thisDirLvl6){	
													if($thisDirLvl6['parent_node_id'] == $thisDirLvl5['node_id']){
														$flat_cat_list[] = array( 
															'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDirLvl6['node_id'], $thisDirLvl6['title'], true),
															'title'=> " ---------- ".$thisDirLvl6['title'], 'dash_title'=> $thisDirLvl6['title'], 
															'node_id'=>$thisDirLvl6['node_id'], 'discussion_count' => $thisDirLvl6['discussion_count'],
															'parent_node_id' => $thisDirLvl6['parent_node_id']					 
												
														);					
													}
												}
											}
										}
									}
								}
							}								
						}
					}
				}	
			}
		}	
		return $flat_cat_list;			
	}
	

	
	
	public function getNodeByIdFromAllDirs($node_id, $allDirs)
	{
		$returnDir = array(); 
		foreach($allDirs AS $thisDir){
			if ($thisDir["node_id"] == $node_id){
				
				$returnDir = array(
					'url' => XenForo_Link::buildPublicLink('directory', array('node_id' => $thisDir['node_id'], 'title' => $thisDir['title'])),
					'title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'],
					'parent_node_id'=>$thisDir['parent_node_id'],
					'discussion_count' => $thisDir['discussion_count']
					);		
				return $returnDir; // this is expensive, return as soon as you hit a match
			}	
		}	
	return false;	
	}	
	
	public function getAllParentsForChildId($node_id, $allDirs)
	{
		// loop through allDirs, and keep adding the relavant parents until we get to parent_id = 0
		$parentDirs = array();	
		$thisNode = $this->getNodeByIdFromAllDirs($node_id, $allDirs);
		
		while($parentNode = $this->getParentfromChildId($thisNode['node_id'], $allDirs))
		{
			$parentDirs[] = $parentNode;
			$thisNode = $parentNode;
		}
		$parentDirs = $this->orderParentDirs($parentDirs);
		return $parentDirs;
		
	}
	
	
	public function buidBreadCrumb(array $crumbs)
	{
		$breadCrumbs = array();
		foreach($crumbs as $key => $crumb)
		{
			$breadCrumbs[] = array(
				'href' => $crumb,
				'value' => $key,
			);
		}
		return $breadCrumbs;
	}
	
	public function orderParentDirs($parentDirs)
	{
		// these need to be ordered with parent_node_id = 0 at the top
		if(count($parentDirs) < 1){return $parentDirs;}
		
		$parentIndex = array();
		foreach($parentDirs as $parentDir)
		{
				$parentIndex[$parentDir['parent_node_id']] = $parentDir;	
		}
	
		$orderedIndex = array();
		$orderedIndex[0] = $parentIndex[0];
		
		for ($i = 1; $i < count($parentDirs); $i++) {
   			$orderedIndex[$i] = $parentIndex[$orderedIndex[$i-1]['node_id']];
		}
		
		
		return $orderedIndex;

	}
		
	
	public function getParentfromChildId($node_id, $allDirs)
	{
		if($node_id == 0){return false;}
		$thisNode = $this->getNodeByIdFromAllDirs($node_id, $allDirs);
		$parent_node_id = $thisNode["parent_node_id"];
		if($parent_node_id == 0){return false;}		
		$parentNode = $this->getNodeByIdFromAllDirs($parent_node_id, $allDirs);
		return $parentNode;
	}
	
	public function getChildrenNameAndId($parent_node_id, $allDirs)
	{
		if($parent_node_id == ""){return "";}
		$childDirs = array(); //array( array(title=>'', node_id=>''), array(title=>'', node_id=>''))
		foreach($allDirs AS $thisDir)
		{
			if ($thisDir["parent_node_id"] == $parent_node_id)
			{
				$childDirs[] = array(
				'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
				'title'=>$thisDir['title'],
				'node_id'=>$thisDir['node_id'],
				'discussion_count' => $thisDir['discussion_count'],
				'child_nodes' => $this->getChildrenNameAndId2nd($thisDir['node_id'], $allDirs),
				'parent_node_id' => $parent_node_id
				);
			}	
		}
		return $childDirs;	
	}	

	// I absolutely hate the way I've done this, this has the potential of being infinite deep, but I've limited it with bad programming, argh!	will clean this up /*To Do*/
	public function getChildrenNameAndId2nd($parent_node_id, $allDirs){
		if($parent_node_id == ""){return "";}
		$childDirs = array(); //array( array(title=>'', node_id=>''), array(title=>'', node_id=>''))
		foreach($allDirs AS $thisDir){
			if ($thisDir["parent_node_id"] == $parent_node_id){
				$childDirs[] = array(
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'],
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $this->getChildrenNameAndId3rd($thisDir['node_id'], $allDirs),
					'parent_node_id' => $parent_node_id
					);
				}	
			}	
			return $childDirs;	
		}	
	

	public function getChildrenNameAndId3rd($parent_node_id, $allDirs){
		if($parent_node_id == ""){return "";}
		$childDirs = array(); //array( array(title=>'', node_id=>''), array(title=>'', node_id=>''))
		foreach($allDirs AS $thisDir){
			if ($thisDir["parent_node_id"] == $parent_node_id){
				$childDirs[] = array(
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'],
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $this->getChildrenNameAndId4th($thisDir['node_id'], $allDirs),
					'parent_node_id' => $parent_node_id
					);
				}	
			}	
			return $childDirs;	
		}		
		
	public function getChildrenNameAndId4th($parent_node_id, $allDirs){
		if($parent_node_id == ""){return "";}
		$childDirs = array(); //array( array(title=>'', node_id=>''), array(title=>'', node_id=>''))
		foreach($allDirs AS $thisDir){
			if ($thisDir["parent_node_id"] == $parent_node_id){
				$childDirs[] = array(
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'],
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $this->getChildrenNameAndId5th($thisDir['node_id'], $allDirs),
					'parent_node_id' => $parent_node_id
					);
				}	
			}	
			return $childDirs;	
		}	
	
		
	public function getChildrenNameAndId5th($parent_node_id, $allDirs){
		if($parent_node_id == ""){return "";}
		$childDirs = array(); //array( array(title=>'', node_id=>''), array(title=>'', node_id=>''))
		foreach($allDirs AS $thisDir){
			if ($thisDir["parent_node_id"] == $parent_node_id){
				$childDirs[] = array(
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'],
					'node_id'=>$thisDir['node_id'],
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => '',
					'parent_node_id' => $parent_node_id
					);
				}	
			}	
			return $childDirs;							
		}
	
	
	/**
	 * Gets all recent directory reviews
	 *
	 *
	 * @return array
	 */	
	public function getRecentReviews(){
	
		$recentReviewLimit = XenForo_Application::get('options')->recentReviewLimit;
		$directoryForums = XenForo_Application::get('options')->directoryForum;
		$node_id = $directoryForums[0];
		
		return $this->fetchAllKeyed($this->limitQueryResults('
			SELECT review . * , user.username, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar, thread.title
			FROM kmk_post AS review
			LEFT JOIN kmk_user AS user ON ( user.user_id = review.user_id )
			LEFT JOIN kmk_thread AS thread ON ( review.thread_id = thread.thread_id )
			WHERE thread.discussion_state = \'visible\'
			AND thread.node_id = \''.$node_id.'\'
			AND review.position >0
			ORDER BY review.post_date DESC
			', $recentReviewLimit
		), 'comment_id');
			
	}
	
		/**
	 * Gets all recent directory reviews
	 *
	 *
	 * @return array
	 */	
	public function getRecentBusinessListings(){
	
		$recentBusinessListingLimit = XenForo_Application::get('options')->recentBusinessListingLimit;
		$directoryForums = XenForo_Application::get('options')->directoryForum;
		$node_id = $directoryForums[0];
		
		$SQL = '
			SELECT listing . * , user.username, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar
			FROM kmk_thread AS listing
			LEFT JOIN kmk_user AS user ON ( user.user_id = listing.user_id )
			WHERE listing.discussion_state = \'visible\'
			AND listing.node_id = \''.$node_id.'\'
			ORDER BY listing.post_date DESC
			';
		$q = $this->fetchAllKeyed($this->limitQueryResults($SQL , $recentBusinessListingLimit), 'comment_id');
		
		//var_dump($SQL);
		
		return $q;
		
		
			
	}
	

	
	
	/**
	 * Gets all directories FROM kmk_jobvacan_dir
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array
	 */

	
	public function getDirs(array $conditions = array(), array $fetchOptions = array())
	{
		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		$options = XenForo_Application::get('options');
		$order = 'lft';
		if($options->subCatOrder == "alphabetically"){$order = 'title';}
		
		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT node.*, forum.*
					' . $joinOptions['selectFields'] . '
				FROM kmk_jobvacan_dir AS forum
				INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
				' . $joinOptions['joinTables'] . '
				order by '.$order.'
			', $limitOptions['limit'], $limitOptions['offset']
		), 'node_id');
	}
	
	
	public function getChildDirs($node_id, array $conditions = array(), array $fetchOptions = array()){
		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		$options = XenForo_Application::get('options');
		$order = 'lft';		
		
		return $this->fetchAllKeyed($this->limitQueryResults(
		'
			SELECT node.*, forum.*
			FROM kmk_jobvacan_dir AS forum
			INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
			where node.parent_node_id = '.$node_id.'
			order by '.$order.'
			', $limitOptions['limit'], $limitOptions['offset']
		), 'node_id'); 
	}
	
	
	
	/**
	 * Gets the extra data that applies to the specified forum nodes.
	 *
	 * @param array $nodeIds
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array Format: [node id] => extra info
	 */
	public function getExtraForumDataForNodes(array $nodeIds, array $fetchOptions = array())
	{
		if (!$nodeIds)
		{
			return array();
		}

		$joinOptions = $this->prepareForumJoinOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT forum.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_jobvacan_dir AS forum
			INNER JOIN kmk_jobvacan_directory_node AS node ON (node.node_id = forum.node_id)
			' . $joinOptions['joinTables'] . '
			WHERE forum.node_id IN (' . $this->_getDb()->quote($nodeIds) . ')
		', 'node_id');
	}


	



}