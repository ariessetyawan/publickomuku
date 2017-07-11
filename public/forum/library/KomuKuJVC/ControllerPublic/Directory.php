<?php

class KomuKuJVC_ControllerPublic_Directory extends XenForo_ControllerPublic_Forum
{
	

	
	public function actionIndex()
	{
		$this->_assertCanViewDirectory();
		$node_id = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		if ($node_id == ""){return $this->directoryIndex();}
		else{return $this->subDirectoryIndex($node_id);}	
	}
	
	
		
		
	public function directoryIndex(){
		
		$options = XenForo_Application::get('options');
		$visitor = XenForo_Visitor::getInstance();	
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir');
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
		
		$category_list = array();
		$category_node_id = array();
		$numer_of_cols = $options->numer_of_cols;
		
		// I need to count the number of parent elements (move $directory_details to before this, so we can use the results)
		$directoryForums = $options->directoryForum;	
		$directory_details = $dirModel->getStatisticCountsByNodeId($directoryForums[0]);
		$recentReviews = $dirModel->getRecentReviews();
		$recentBusinessListings = $dirModel->getRecentBusinessListings();		
		$total_categories = $directory_details['catagory_count'];
		
		$i =0;
		foreach ($allDirs AS $thisDir)
		{
			
		// if we are at the top level, just get all the dirs with no parents, and put the relavant children in		
			if($thisDir['parent_node_id'] == ""){					
			$new_row = false;if ($i%$numer_of_cols == 0) {$new_row = true;}
			$end_row = false;if (($i + 1)%$numer_of_cols == 0) {$end_row = true;}

			// also end the row if we are on the last element
			if($total_categories == $i+1){$end_row = true;}
			
			// I need to count up all the listings in discussion_count of the child nodes for child_discussion_count
			$child_nodes = $dirModel->getChildrenNameAndId($thisDir['node_id'], $allDirs);
			
			
			// horrible nesting to get counts, needs to support 5 categories deep:
			// USA >> Texas >> Oklahoma >> Cities >> Pubs and Bars
			
			$child_discussion_count = 0;
			foreach($child_nodes AS &$thisChild)
			{
				$thisChild['tot_count'] = $thisChild['discussion_count'];
				if($options->topCountIncChild)
				{
					$sub_nodes = $dirModel->getChildrenNameAndId($thisChild['node_id'], $allDirs);
					$sub_discussion_count = 0;$sub_sub_discussion_count = 0;$sub_sub_sub_discussion_count = 0;
					foreach($sub_nodes AS $sub_node)
					{
						$sub_discussion_count = $sub_discussion_count + $sub_node['discussion_count'];
						$sub_sub_nodes = $dirModel->getChildrenNameAndId($sub_node['node_id'], $allDirs);
						foreach($sub_sub_nodes AS $sub_sub_node)
						{
							$sub_sub_discussion_count = $sub_sub_discussion_count + $sub_sub_node['discussion_count'];	
							$sub_sub_sub_nodes = $dirModel->getChildrenNameAndId($sub_sub_node['node_id'], $allDirs);
							foreach($sub_sub_sub_nodes AS $sub_sub_sub_node)
							{
								$sub_sub_sub_discussion_count = $sub_sub_sub_discussion_count + $sub_sub_sub_node['discussion_count'];
							}						
						}	
					}
					$thisChild['tot_count'] =  $sub_sub_sub_discussion_count + $sub_sub_discussion_count + $sub_discussion_count + $thisChild['discussion_count'];
				}			
				$child_discussion_count = $child_discussion_count + $thisChild['tot_count'];
			}
			
			

						
			$catlist_reuse = array( 
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title' => $thisDir['title'], 
					'node_id' => $thisDir['node_id'], 
					'discussion_count' => $thisDir['discussion_count'],
					'child_discussion_count' => $child_discussion_count, 
					'tot_count' => $child_discussion_count + $thisDir['discussion_count'],
					'child_nodes' => $child_nodes,
					'new_row' => $new_row,
					'end_row' => $end_row 
				);
				
			$category_list[] = $catlist_reuse;
			$i++;	
			}	
		}
		
		
		switch ($numer_of_cols)	{
			case '1':$col_width = 100;
			break;
			case '2':$col_width = 50;
			break;
			case '3':$col_width = 33;
			break;
			case '4':$col_width = 25;
			break;	
		}
		
        $viewParams = array(
        	'perms' => $this->_getDirPermsModel()->getPermissions(),
        	'allowDirIdxCatSideBar' => $options->allowDirIdxCatSideBar,
        	'subLisingCount' => $options->subLisingCount,
        	'topLisingCount' => $options->topLisingCount,
        	'topCountIncChild' => $options->topCountIncChild,      	
        	'allowMainDirFilter' => $options->allowMainDirFilter,
			'category_list' => $category_list,
			'details' => $directory_details,
			'recentReviews' => $recentReviews,
			'recentBusinessListings' => $recentBusinessListings,
			'DisplayRecentBusinessListings' => $options->DisplayRecentBusinessListings,
			'DisplayDirectoryStatistics' => $options->DisplayDirectoryStatistics,
			'DisplayRecentBusinessReviews' => $options->DisplayRecentBusinessReviews,
			'DisplayDirectoryRSidebar' => $options->DisplayDirectoryRSidebar,
			'DisplaySubDirectoryRSidebar' => $options->DisplaySubDirectoryRSidebar,
			'DisplayCommas' => $options->DisplayCommas,
			'col_width' => $col_width
		);
       return $this->responseView('KomuKuJVC_ViewPublic_Directory_Index', 'sfdirectory_index', $viewParams);
	}
	
	
	
	
	
	
	public function actionCreateListing()
	{
		
		// this should really be setting $this->_params['forum']
		// (so other plug-ins can make use of this, I'll need to look into this)
		
		$options = XenForo_Application::get('options');

		
		
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir');
		$directoryModel = $this->getModelFromCache('KomuKuJVC_Model_DirectoryNode');
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
		$category_list = $dirModel->getCategoryList($allDirs);
	
		$ftpHelper = $this->getHelper('ForumThreadPost');
			
		$forum = $directoryModel->getNodeById($forumId);
		//this variable should really have been called something like directoryForumCategory, but it makes it very confusing with the internal categories 
		// of the directory, will try to clean up at some point in the future /*To Do*/
		
		$forumId = $forum['node_id'];
		if (!$forum){$forum = array();} // work around, when there is no node_id or forum, but we still want to let user create listing from anywhere
		if (!$forumId ){$forumId  = 0;$forum['node_id'] = $forumId;}
		

		$attachmentParams = $this->getModelFromCache('XenForo_Model_Forum')->getAttachmentParams($forum, array(
			'node_id' => $forumId 
		));

		
		$directoryForums = $options->directoryForum;
		$directoryForum = $directoryForums[0];
		$prefixes = $this->_getPrefixModel()->getUsablePrefixesInForums($directoryForum); //  get prefixes avaiable to the directory forum
		
		$viewParams = array(	
		
		    'prefixes' => $prefixes,
			'displayPrefixPrompt' => $options->displayPrefixPrompt,
			'allowGoogleMap' => $options->allowGoogleMap, 
			'allowDeepLinks' => $options->allowDeepLinks, 
			'allowLogo' => $options->allowLogo, 
			'allowYoutube' => $options->allowYoutube, 
			'allowContactLink' => $options->allowContactLink,
			'allowTelephone' => $options->allowTelephone,
			'allowSiteURL' => $options->allowSiteURL,
			'allowLocation' => $options->allowLocation,
			'thread' => array('discussion_open' => 1),
			'forum' => $forum,
			'category_list' => $category_list,
			'attachmentParams' => $attachmentParams,
			'captcha' => XenForo_Captcha_Abstract::createDefault(),
			'displayCustomA' => $options->displayCustomA,
			'customCategoryTitleA' => $options->customCategoryTitleA,
			'customFieldA1' => $options->customFieldA1,
			'customFieldA2' => $options->customFieldA2,
			'customFieldA3' => $options->customFieldA3,
			'customFieldA4' => $options->customFieldA4,
			'displayCustomB' => $options->displayCustomB,
			'customCategoryTitleB' => $options->customCategoryTitleB,
			'customFieldB1' => $options->customFieldB1,
			'customFieldB2' => $options->customFieldB2,
			'customFieldB3' => $options->customFieldB3,
			'customFieldB4' => $options->customFieldB4,
		);
		return $this->responseView('XenForo_ViewPublic_Thread_Create', 'sfcreate_listing', $viewParams);
	}
	
	
	
	
	
	
	

	
	
	
	public function subDirectoryIndex($node_id){
		
		$directoryId = $node_id;	
		$options = XenForo_Application::get('options');	
		$visitor = XenForo_Visitor::getInstance();	
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir');
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, 
			'permissionCombinationId' => 1
		));  /*To Do*/ // We could really benifit from this being cached... really! 
		
		
		
		
		$root_directories = array();
		$category_list = array();
		foreach ($allDirs AS $thisDir)
		{
		// if we are at the top level, just get all the dirs with no parents, and put the relavant children in		
			if($thisDir['parent_node_id'] == ""){

				
				$child_nodes = $dirModel->getChildrenNameAndId($thisDir['node_id'], $allDirs);			
				$root_directories[] = array( 
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'], 
					'node_id'=>$thisDir['node_id'], 
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $child_nodes, 
				);
				
				
					
				
			}
			if($thisDir['parent_node_id'] == $node_id)
			{

				
				$child_nodes = $dirModel->getChildrenNameAndId($thisDir['node_id'], $allDirs);
				$child_discussion_count = 0;
				
				
				if($options->topCountIncChild)
				{
					foreach($child_nodes AS &$thisChild)
					{
						$thisChild['tot_count'] = $thisChild['discussion_count'];
						$sub_nodes = $dirModel->getChildrenNameAndId($thisChild['node_id'], $allDirs);
						$sub_discussion_count = 0;$sub_sub_discussion_count = 0;$sub_sub_sub_discussion_count = 0;
						foreach($sub_nodes AS $sub_node)
						{
							$sub_discussion_count = $sub_discussion_count + $sub_node['discussion_count'];
							$sub_sub_nodes = $dirModel->getChildrenNameAndId($sub_node['node_id'], $allDirs);
							foreach($sub_sub_nodes AS $sub_sub_node)
							{
								$sub_sub_discussion_count = $sub_sub_discussion_count + $sub_sub_node['discussion_count'];	
								$sub_sub_sub_nodes = $dirModel->getChildrenNameAndId($sub_sub_node['node_id'], $allDirs);
								foreach($sub_sub_sub_nodes AS $sub_sub_sub_node)
								{
									$sub_sub_sub_discussion_count = $sub_sub_sub_discussion_count + $sub_sub__sub_node['discussion_count'];
								}						
							}	
						}
						$thisChild['tot_count'] =  $sub_sub_sub_discussion_count + $sub_sub_discussion_count + $sub_discussion_count + $thisChild['discussion_count'];			
						$child_discussion_count = $child_discussion_count + $thisChild['tot_count'];
					}
				}
				
							
				
				$category_list[] = array( 
					'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($thisDir['node_id'], $thisDir['title'], true),
					'title'=>$thisDir['title'], 
					'node_id'=>$thisDir['node_id'], 
					'discussion_count' => $thisDir['discussion_count'],
					'child_nodes' => $child_nodes,
					'tot_count' => $child_discussion_count + $thisDir['discussion_count']
				);	
				
				
			} 			
		}	
		
		/*
		
			// I need to count up all the listings in discussion_count of the child nodes for child_discussion_count
			$child_nodes = $dirModel->getChildrenNameAndId($thisDir['node_id'], $allDirs);
			$child_discussion_count = 0;
			foreach($child_nodes AS &$thisChild)
			{
				$thisChild['tot_count'] = $thisChild['discussion_count'];
			}
		*/
		
		
		
		//dig out the details for this sub-directory
		$sub_directory  = $dirModel->getNodeByIdFromAllDirs($node_id, $allDirs);
			
		// now we need to return the list of submited direcotory listings (or threads)
		// and show the number of reviews (/ replied posts)
		// we're going to intergrate with a particular (hidden? optional) forum and use the standard threads and view of threads from there

		$options = XenForo_Application::get('options');
		$displaySubFilterDropDown = $options->displaySubFilterDropDown;
		$directoryForums = $options->directoryForum;
		$directoryForum = $directoryForums[0];
		$forumId = $directoryForum;		
		$forumName = "";
		if (!$forumId && !$forumName)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('index')
			);
		}
		
		// we only want to return threads that are part of this directory
		

		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forumFetchOptions = array('readUserId' => $visitor['user_id']);
		$forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName, $forumFetchOptions);
		$forumId = $forum['node_id'];
		$prefix_id =  $this->_input->filterSingle('prefix_id', XenForo_Input::INT);
		$threadModel = $this->getModelFromCache('XenForo_Model_Thread');
		$forumModel = $this->getModelFromCache('XenForo_Model_Forum');

		$threadMapModel = $this->getModelFromCache('KomuKuJVC_Model_ThreadMap'); 
		$directoryNodeModel = $this->getModelFromCache('KomuKuJVC_Model_DirectoryNode'); 
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir'); 
					
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$threadsPerPage = XenForo_Application::get('options')->discussionsPerPage;
		$defaultOrder = 'last_post_date';
		$defaultOrderDirection = 'desc';
		

		
		
		
		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));

		$displayConditions = $this->_getDisplayConditions($forum);

		$fetchElements = $this->_getThreadFetchElements(
			$forum, $displayConditions,
			$threadsPerPage, $page, $order, $orderDirection
		);
		$threadFetchConditions = $fetchElements['conditions'];
		
		
		$threadFetchOptions = $fetchElements['options'] + array(
			'perPage' => $threadsPerPage,
			'page' => $page,
			'order' => $order,
			'orderDirection' => $orderDirection
		);
		

		
		if (!empty($threadFetchConditions['deleted']))
		{
			$threadFetchOptions['join'] |= XenForo_Model_Thread::FETCH_DELETION_LOG;
		}

		
		$totalThreads = $threadMapModel->countThreadForThisDir($forumId, $directoryId, $threadFetchConditions);

		
		$this->canonicalizePageNumber($page, $threadsPerPage, $totalThreads, 'forums', $forum);
		
		
		
		$threads = $directoryNodeModel->getThreadsInDirectory($forumId, $directoryId, $threadFetchConditions, $threadFetchOptions);

		// for each thread, need to check if it is claimable (and thus possibly set the thread creator avatar to anonymous on thread view)
		//only do if we care about anonymous

		
		
		if ($page == 1)
		{
			$stickyThreads = $threadModel->getStickyThreadsInForum($forumId, $threadFetchConditions, $threadFetchOptions);
			foreach (array_keys($stickyThreads) AS $stickyThreadId)
			{
				unset($threads[$stickyThreadId]);
			}
		}
		else
		{
			$stickyThreads = array();
		}

		// prepare all threads for the thread list
		$inlineModOptions = array();
		$permissions = $visitor->getNodePermissions($forumId);

		foreach ($threads AS &$thread)
		{
			$threadModOptions = $threadModel->addInlineModOptionToThread($thread, $forum, $permissions);
			$inlineModOptions += $threadModOptions;

			$thread = $threadModel->prepareThread($thread, $forum, $permissions);
		}
		foreach ($stickyThreads AS &$thread)
		{
			$threadModOptions = $threadModel->addInlineModOptionToThread($thread, $forum, $permissions);
			$inlineModOptions += $threadModOptions;

			$thread = $threadModel->prepareThread($thread, $forum, $permissions);
		}
		unset($thread);

		// if we've read everything on the first page of a normal sort order, probably need to mark as read
		if ($visitor['user_id'] && $page == 1
			&& $order == 'last_post_date' && $orderDirection == 'desc'
			&& $forum['forum_read_date'] < $forum['last_post_date']
		)
		{
			$hasNew = false;
			foreach ($threads AS $thread)
			{
				if ($thread['isNew'])
				{
					$hasNew = true;
					break;
				}
			}

			
			

		}

		// get the ordering params set for the header links
		$orderParams = array();
		foreach (array('title', 'post_date', 'reply_count', 'view_count', 'last_post_date') AS $field)
		{
			$orderParams[$field]['order'] = ($field != $defaultOrder ? $field : false);
			if ($order == $field)
			{
				$orderParams[$field]['direction'] = ($orderDirection == 'desc' ? 'asc' : 'desc');
			}
		}		
		
		
		$prefixModel = XenForo_Model::create('XenForo_Model_ThreadPrefix');
		
		// need url equivalent:		
		foreach ($threads AS &$thread){
		$thread['url'] = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'],$thread['title']);	
		$threadprefix = new XenForo_Phrase($prefixModel->getPrefixTitlePhraseName($thread['prefix_id'])); 		
		$thread['prefix_url'] = XenForo_Link::getTitleForUrl($threadprefix, true);
		
		}
		

	
		$directory_details = $dirModel->getStatisticCountsByNodeId($directoryForum);
		
		
		// need a list of prefixes for this forum:
		
		$prefixIds = array_keys($prefixModel->getPrefixesInForum($forumId));			
		if($prefixIds){
			$prefixes = $prefixModel->getPrefixes(array('prefix_ids' => $prefixIds));
			$prepare_prefixes = $prefixModel->preparePrefixes($prefixes);
			}
		else {$prefixes = array();$prepare_prefixes = array();}
		
	
	
		foreach ($prepare_prefixes as $key => $pre)
       	{$prepare_prefixes[$key]['urltitle'] = XenForo_Link::getTitleForUrl($pre['title']);}
		

		if($prefix_id) {
			$forum_prefix = new XenForo_Phrase($prefixModel->getPrefixTitlePhraseName($prefix_id)); 
			$forum['forum_prefix'] = $forum_prefix;
			$forum['prefix_title'] = XenForo_Link::getTitleForUrl($forum_prefix, true);
			$pageNavParams['t'] = $forum['prefix_title'];	
			$pageNavParams = array_merge((array)$pageNavParams, (array)$displayConditions);
		}
		else {
		$pageNavParams = $displayConditions;
		}
		$pageNavParams['order'] = ($order != $defaultOrder ? $order : false);
		$pageNavParams['direction'] = ($orderDirection != $defaultOrderDirection ? $orderDirection : false);
		
		
		
		$nodeList =	$this->getModelFromCache('XenForo_Model_Node')->getNodeDataForListDisplay($forum, 0);	
		
		$breadCrumbValues = array();
		
		
		
		$parent_nodes = $dirModel->getAllParentsForChildId($directoryId, $allDirs);
		foreach($parent_nodes as $parent_node)
		{
			$breadCrumbValues[$parent_node['title']] = $parent_node['url'];
		}
		if($options->includeCurretPageInSubBread)
		{
			$breadCrumbValues[$sub_directory['title']] = $sub_directory['url'];
		}
		$breadCrumbs = $dirModel->buidBreadCrumb($breadCrumbValues);	
		
		$sub_dir_url = XenForo_Link::buildIntegerAndTitleUrlComponent($sub_directory['node_id'], $sub_directory['title']);
		$full_sub_url = XenForo_Link::buildPublicLink('full:directory/'.$sub_dir_url);
		
			
			
        $viewParams = array(
        	'sub_dir_url' => $sub_dir_url,
        	'full_sub_url' => $full_sub_url,
        	'breadCrumbs' => $breadCrumbs,
        	'perms' => $this->_getDirPermsModel()->getPermissions(),
			'displaySubFilterDropDown'	=> $displaySubFilterDropDown, 
        	'prefix_id' => $prefix_id,
			'prefixIds' => $prefixIds,
			'prefixes' => $prepare_prefixes,
        
        	'allowDirSubCatSideBar'=> $options->allowDirSubCatSideBar,
			'claimableAnonymous' => $options->claimableAnonymous,
			'details' => $directory_details,
		// for forum list
			'nodeList' => $nodeList,
			'forum' => $forum,
			'canPostThread' => $forumModel->canPostThreadInForum($forum),
			'canSearch' => $visitor->canSearch(),
			'inlineModOptions' => $inlineModOptions,
			'threads' => $threads,
			'stickyThreads' => $stickyThreads,

			'order' => $order,
			'orderDirection' => $orderDirection,
			'orderParams' => $orderParams,

			'pageNavParams' => $pageNavParams,
			'page' => $page,
			
			'threadStartOffset' => ($page - 1) * $threadsPerPage + 1,
			'threadEndOffset' => ($page - 1) * $threadsPerPage + count($threads) ,
			'threadsPerPage' => $threadsPerPage,
			'totalThreads' => $totalThreads,
			'showPostedNotice' => $this->_input->filterSingle('posted', XenForo_Input::UINT),
			
			
		// for directory
		
        	'topLisingCount' => $options->topLisingCount,	
			'subDirLisingCount' => $options->subDirLisingCount,
			'topCountIncChild' => $options->topCountIncChild, 
			'category_list' => $category_list,
			'sub_directory' => $sub_directory,
			'root_directories' => $root_directories,
			
			'recentReviews' => $dirModel->getRecentReviews(),
			'recentBusinessListings' => $dirModel->getRecentBusinessListings(),
			'DisplayRecentBusinessListings' => $options->DisplayRecentBusinessListings,
			'DisplayDirectoryStatistics' => $options->DisplayDirectoryStatistics,
			'DisplayRecentBusinessReviews' => $options->DisplayRecentBusinessReviews,
			'DisplayDirectoryRSidebar' => $options->DisplayDirectoryRSidebar,
			'DisplaySubDirectoryRSidebar' => $options->DisplaySubDirectoryRSidebar
			
		);
		
		//var_dump($viewParams['sub_directory']);
		
       return $this->responseView('', 'sfdirectory_sub_index', $viewParams);
	}	
		
	
	

	
	
	
	
	
	
	public function actionNew(){	
   //  display a list of all the threads in the directory forum (just a mimic of the standard forum view, update template to look different)

	// get the directory forum defined in the ACP	
		$visitor = XenForo_Visitor::getInstance();
		$options = XenForo_Application::get('options');
		$directoryForums = $options->directoryForum;
		$directoryForum = $directoryForums[0];
		$threadsPerPage = $options->discussionsPerPage;

        $forumId = $directoryForum;
		$forumName = "";

		$this->_assertCanViewDirectory();
		if (!$forumId && !$forumName)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
				XenForo_Link::buildPublicLink('index')
			);
		}

		$visitor = XenForo_Visitor::getInstance();

		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forumFetchOptions = array('readUserId' => $visitor['user_id']);
		$forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName, $forumFetchOptions);

		$forumId = $forum['node_id'];

		$threadModel = $this->_getThreadModel();
		$forumModel = $this->_getForumModel();

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$threadsPerPage = XenForo_Application::get('options')->discussionsPerPage;

	//	$this->canonicalizeRequestUrl(
	//			XenForo_Link::buildPublicLink('forums', $forum, array('page' => $page))
	//	);

		$defaultOrder = 'last_post_date';
		$defaultOrderDirection = 'desc';

		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));

		$displayConditions = array();

		$prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);
		if ($prefixId)
		{
			$displayConditions['prefix_id'] = $prefixId;
		}

		// fetch all thread info
		$threadFetchConditions = $displayConditions + $threadModel->getPermissionBasedThreadFetchConditions($forum)
			+ array('sticky' => 0);
		$threadFetchOptions = array(
			'perPage' => $threadsPerPage,
			'page' => $page,

			'join' => XenForo_Model_Thread::FETCH_USER,
			'readUserId' => $visitor['user_id'],
			'postCountUserId' => $visitor['user_id'],

			'order' => $order,
			'orderDirection' => $orderDirection
		);
		if (!empty($threadFetchConditions['deleted']))
		{
			$threadFetchOptions['join'] |= XenForo_Model_Thread::FETCH_DELETION_LOG;
		}

		
		$directoryModel = $this->getModelFromCache('KomuKuJVC_Model_DirectoryNode'); 
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir'); 
		
	//	$totalThreads = $threadModel->countThreadsInForum($forumId, $threadFetchConditions);
	//	$this->canonicalizePageNumber($page, $threadsPerPage, $totalThreads, 'forums', $forum);
	//	$threads = $directoryModel->getThreadsInForum($forumId, $threadFetchConditions, $threadFetchOptions);
		
		
		$totalThreads = $threadModel->countThreadsInForum($forumId, $threadFetchConditions);

		$this->canonicalizePageNumber($page, $threadsPerPage, $totalThreads, 'forums', $forum);

		$threads = $threadModel->getThreadsInForum($forumId, $threadFetchConditions, $threadFetchOptions);

		if ($page == 1)
		{
			$stickyThreadFetchOptions = $threadFetchOptions;
			unset($stickyThreadFetchOptions['perPage'], $stickyThreadFetchOptions['page']);

			$stickyThreads = $threadModel->getStickyThreadsInForum($forumId, $threadFetchConditions, $stickyThreadFetchOptions);
			foreach (array_keys($stickyThreads) AS $stickyThreadId)
			{
				unset($threads[$stickyThreadId]);
			}
		}
		else
		{
			$stickyThreads = array();
		}

		// prepare all threads for the thread list
		$inlineModOptions = array();
		$permissions = $visitor->getNodePermissions($forumId);

		foreach ($threads AS &$thread)
		{
			$threadModOptions = $threadModel->addInlineModOptionToThread($thread, $forum, $permissions);
			$inlineModOptions += $threadModOptions;

			$thread = $threadModel->prepareThread($thread, $forum, $permissions);
		}
		foreach ($stickyThreads AS &$thread)
		{
			$threadModOptions = $threadModel->addInlineModOptionToThread($thread, $forum, $permissions);
			$inlineModOptions += $threadModOptions;

			$thread = $threadModel->prepareThread($thread, $forum, $permissions);
		}
		unset($thread);

		// if we've read everything on the first page of a normal sort order, probably need to mark as read
		if ($visitor['user_id'] && $page == 1 && !$displayConditions
			&& $order == 'last_post_date' && $orderDirection == 'desc'
			&& $forum['forum_read_date'] < $forum['last_post_date']
		)
		{
			$hasNew = false;
			foreach ($threads AS $thread)
			{
				if ($thread['isNew'])
				{
					$hasNew = true;
					break;
				}
			}


		}

		// get the ordering params set for the header links
		$orderParams = array();
		foreach (array('title', 'post_date', 'reply_count', 'view_count', 'last_post_date') AS $field)
		{
			$orderParams[$field]['prefix_id'] = ($prefixId ? $prefixId : false);
			$orderParams[$field]['order'] = ($field != $defaultOrder ? $field : false);
			if ($order == $field)
			{
				$orderParams[$field]['direction'] = ($orderDirection == 'desc' ? 'asc' : 'desc');
			}
		}
		
		
		// nodeBreadCrumbs will always be the same for the "new" directory area:
		$new_listings =  new  XenForo_Phrase('new_listings');	
		$nodeBreadCrumbs = array ( 

			array(
				"href"=> "index.php?directory/new",
				"value"=> $new_listings,
				"node_id"=>"" 
				)
				
			);	
		

		$viewParams = array(
		    'claimableAnonymous' => $options->claimableAnonymous,
			'nodeList' => $this->_getNodeModel()->getNodeDataForListDisplay($forum, 0),
			'forum' => $forum,
			'nodeBreadCrumbs' => $nodeBreadCrumbs,

			'canPostThread' => $forumModel->canPostThreadInForum($forum),
			'canSearch' => $visitor->canSearch(),

			'inlineModOptions' => $inlineModOptions,
			'threads' => $threads,
			'stickyThreads' => $stickyThreads,

		//	'ignoredNames' => $this->_getIgnoredContentUserNames($threads) + $this->_getIgnoredContentUserNames($stickyThreads),

			'order' => $order,
			'orderDirection' => $orderDirection,
			'orderParams' => $orderParams,
			'displayConditions' => $displayConditions,

			'pageNavParams' => array(
				'prefix_id' => ($prefixId ? $prefixId : false),
				'order' => ($order != $defaultOrder ? $order : false),
				'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false)
			),
			'page' => $page,
			'threadStartOffset' => ($page - 1) * $threadsPerPage + 1,
			'threadEndOffset' => ($page - 1) * $threadsPerPage + count($threads) ,
			'threadsPerPage' => $threadsPerPage,
			'totalThreads' => $totalThreads,

			'showPostedNotice' => $this->_input->filterSingle('posted', XenForo_Input::UINT)
		);
		
		
		
		
		
		foreach($viewParams['threads'] AS $thread){
			$thisURL = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'], $thread['title'], true);
			$viewParams['threads'][$thread['thread_id']]['url'] =  $thisURL;
			}		

			$responseView = $this->responseView('XenForo_ViewPublic_Forum_View', 'sfdir_new_view', $viewParams);
	

		return $responseView;
	}
	
	
  	
		
	/**
	 * Inserts a new thread into this forum, also adds the attributes needed for this directory to a seperate table
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionAddListing()
	{
		// we are only ever adding this to one forum, and that forum is defined from the ACP, 
		// get the fourm id and forum name from there
		$options = XenForo_Application::get('options');
		
		$directoryForums = $options->directoryForum;
		$directoryForum = $directoryForums[0];
		$forumId = $directoryForum;
		$forumName = "";
		
		
		$this->_assertPostOnly();
		$this->_assertCanSubmitListing();

		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName);

		$forumId = $forum['node_id'];

		$this->_assertCanPostThreadInForum($forum);

		if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
		{
			return $this->responseCaptchaFailed();
		}

		$visitor = XenForo_Visitor::getInstance();
 		
		$input = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'attachment_hash' => XenForo_Input::STRING,

			'watch_thread_state' => XenForo_Input::UINT,
			'watch_thread' => XenForo_Input::UINT,
			'watch_thread_email' => XenForo_Input::UINT,

			'_set' => array(XenForo_Input::UINT, 'array' => true),
			'discussion_open' => XenForo_Input::UINT,
			'sticky' => XenForo_Input::UINT,
			
			'prefix_id' => XenForo_Input::UINT,
			
			'poll' => XenForo_Input::ARRAY_SIMPLE, // filtered below
		));
		
		
		$directoryInput = $this->_input->filter(array(		    
			'directory_category' => XenForo_Input::UINT,				'telephone' => XenForo_Input::STRING,
			'address_line_1' =>  XenForo_Input::STRING,					'address_line_2' =>  XenForo_Input::STRING,
			'town_city' =>  XenForo_Input::STRING,						'postcode' =>  XenForo_Input::STRING,		
			'website_url' =>  XenForo_Input::STRING,					'website_anchor_text' =>  XenForo_Input::STRING,
			'deeplink1_url' =>  XenForo_Input::STRING,					'deeplink1_anchor_text' =>  XenForo_Input::STRING,
			'deeplink2_url' =>  XenForo_Input::STRING,					'deeplink2_anchor_text' =>  XenForo_Input::STRING,
			'deeplink3_url' =>  XenForo_Input::STRING,					'deeplink3_anchor_text' =>  XenForo_Input::STRING,
			'logo_image_url' =>  XenForo_Input::STRING,					'youtube_url' =>  XenForo_Input::STRING,
			'customfielda1' =>  XenForo_Input::STRING,					'customfielda2' =>  XenForo_Input::STRING,
			'customfielda3' =>  XenForo_Input::STRING,					'customfielda4' =>  XenForo_Input::STRING,
			'customfieldb1' =>  XenForo_Input::STRING,					'customfieldb2' =>  XenForo_Input::STRING,
			'customfieldb3' =>  XenForo_Input::STRING,					'customfieldb4' =>  XenForo_Input::STRING,
			
			
							
		));
		
		
		// Must have selected a category:
		if($directoryInput['directory_category'] == ""){return $this->responseError('Please enter a valid category');}
		
		
		// I may want to personaly rip anything i dont trust out of these, since I will be using to create links and javascript for the google map	
		// pick up the thread id later on

		
		$input['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		$input['message'] = XenForo_Helper_String::autoLinkBbCode($input['message']);



		// note: assumes that the message dw will pick up the username issues
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		$writer->set('user_id', $visitor['user_id']);
		$writer->set('username', $visitor['username']);
		$writer->set('title', $input['title']);
		$writer->set('node_id', $forumId);
		$writer->set('prefix_id', $input['prefix_id']);
		
		$writer->setExtraData(XenForo_DataWriter_Discussion_Thread::DATA_FORUM, $forum);
		
		// discussion state changes instead of first message state
		$writer->set('discussion_state', $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState(array(), $forum));

		// discussion open state - moderator permission required
		if (!empty($input['_set']['discussion_open']) && $this->_getForumModel()->canLockUnlockThreadInForum($forum))
		{
			$writer->set('discussion_open', $input['discussion_open']);
		}

		// discussion sticky state - moderator permission required
		if (!empty($input['_set']['sticky']) && $this->_getForumModel()->canStickUnstickThreadInForum($forum))
		{
			$writer->set('sticky', $input['sticky']);
		}

		$postWriter = $writer->getFirstMessageDw();
		$postWriter->set('message', $input['message']);
		$postWriter->setExtraData(XenForo_DataWriter_DiscussionMessage::DATA_ATTACHMENT_HASH, $input['attachment_hash']);

		$writer->preSave();




		
		$writer->save();

		$thread = $writer->getMergedData();



		$this->_getThreadWatchModel()->setVisitorThreadWatchStateFromInput($thread['thread_id'], $input);



		if (!$this->_getThreadModel()->canViewThread($thread, $forum))
		{
			$return = XenForo_Link::buildPublicLink('forums', $forum, array('posted' => 1));
		}
		else
		{
			$retrutnURL = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'], $thread['title'], true);
			$return = XenForo_Link::buildPublicLink('reviews/'.$retrutnURL);
		}

		// at this point, we've saved the thread, we can now save the related directory information that maps to this thread (thread map)
		// updated this method 26/12/11 to use dataWriter (avoided manually striping unsafe chars and now allows multibyte chars)
		$directoryInput['thread_id'] = $thread['thread_id'];
		$directory_category = $this->strip($directoryInput['directory_category']);
		$ThreadMapWriter = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		$ThreadMapWriter->bulkSet($directoryInput);		
		// prevent xss:
		$ThreadMapWriter->set('website_url', $this->isURL($directoryInput['website_url']));
		$ThreadMapWriter->set('deeplink1_url', $this->isURL($directoryInput['deeplink1_url']));
		$ThreadMapWriter->set('deeplink2_url', $this->isURL($directoryInput['deeplink2_url']));
		$ThreadMapWriter->set('deeplink3_url', $this->isURL($directoryInput['deeplink3_url']));
		$ThreadMapWriter->set('logo_image_url', $this->isURL($directoryInput['logo_image_url']));
		$ThreadMapWriter->set('youtube_url', $this->isURL($directoryInput['youtube_url']));						
		$ThreadMapWriter->preSave();
		if (!$ThreadMapWriter->hasErrors())
		{
			$this->assertNotFlooding('post');
		}
		$ThreadMapWriter->save();		
			
		// we also need to update the count for the number of listings added to this directory: 
		// a lot of issues here, sub sub children weren't being included in the count
		// when adding a listing, if another listing was added as "non-primary-category" it was getting dropped from count
		// Do with model and get it right! (or at least better for now)
		
		/*	
		$db = XenForo_Application::get('db'); 			
		$db->query(
				"UPDATE kmk_jobvacan_dir SET discussion_count = (select count(directory_category) from kmk_jobvacan_thread_map where directory_category ='".$directory_category."' ) WHERE node_id = '".$directory_category."' "
		);
		*/
		
		// if this isnt a top level directory, you also need to count the children directories
		$this->_getThreadMapModel()->rebuildCategoryCountsById($directory_category);
		
				
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			//var_dump($input),
			$return,
			new XenForo_Phrase('your_thread_has_been_posted')
		);
		
		
	}
	
	


	public function strip($param){ 
		// for these fields, dont trust anything other than these chars 	<= what was I thinking!
		$data=preg_replace("/[^A-Za-z0-9 \/\+=\?@:&,._-]/","",$param);
		return $data;  		
	}
	
	public function isURL($param){
	// rip out the http://  strip nasty chars, then re-add the htpp:// 		<= what was I thinking!
		$https = false;
		$param = str_replace("http://","",$param);
		if(strrpos($param, "https://") > -1){$https = true;$param = str_replace("https://","",$param);}
		$data=preg_replace("/[^A-Za-z0-9 \/\+=\?@:&,._-]/","",$param);
		if($data != "" && $https == false){$data = "http://".$data;}
		if($https == true){$data = "https://".$data;}
		return $data;  
	}
	
		

		

	public function subval_sort($a,$subkey) {
		foreach($a as $k=>$v) {$b[$k] = strtolower($v[$subkey]);}
		if(!isset($b)){return $a;}
		asort($b);
		foreach($b as $key=>$val) {$c[] = $a[$key];}
		return $c;
	}


	protected function _getThreadMapModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ThreadMap');
	}
	
	protected function _getDirPermsModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_Perms');
	}
	
	protected function _assertCanSubmitListing()	
	{	
		if (!$this->_getDirPermsModel()->canSubmitListing($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
				
	}	
	
	protected function _assertCanViewDirectory()	
	{
		if (!$this->_getDirPermsModel()->canViewDirectory($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}				
	}
	
}

