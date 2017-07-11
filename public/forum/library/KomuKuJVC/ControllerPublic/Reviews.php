<?php

/**
 * Controller for handling actions on Reviews. (Extends Thread)
 *
 */
class KomuKuJVC_ControllerPublic_Reviews extends XenForo_ControllerPublic_Thread
{
	/**
	 * Displays a Review Page.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIndex()
	{
		$this->_assertCanViewReviews();
		
		$options = XenForo_Application::get('options');
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$visitor = XenForo_Visitor::getInstance();	
		$ftpHelper = $this->getHelper('ForumThreadPost');			
		// get the parent thread
		$dirModel = $this->_getDirModel();
		$threadResponse = parent::actionIndex();
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, 
			'permissionCombinationId' => 1
		));
		// if no permissions / redirect	
		if (!$threadResponse instanceof XenForo_ControllerResponse_View)
		{
			return $threadResponse;
		}


		
		$forum = $threadResponse->params['forum']; 	
		$thread = $threadResponse->params['thread']; 
		$posts = $threadResponse->params['posts']; 
		$page = $threadResponse->params['page']; 
		
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		
		// get the directory related params for this thread (get from model)
		$directorylisting = $this->getModelFromCache('KomuKuJVC_Model_ThreadMap')->getThreadMapById($threadId);
		// for the address, strip out commas and unknowns (users have a tendancy to put commas where they dont belong)
		$remove = array(" ",";","&",",","'",'"');  // dont want this chars to interfere with the google map URL
		$postcode = str_replace($remove, "+", $directorylisting['postcode']);
		$address_line_1 = str_replace($remove, "+", $directorylisting['address_line_1']);
		$address_line_2 = str_replace($remove, "+", $directorylisting['address_line_2']);
		$town_city = str_replace($remove, "+", $directorylisting['town_city']);
		
		$is_claimable = $directorylisting['is_claimable'];
		
		// I've found that if users use a house name in the first line of the address, then the map isn't great, 
		// However, if we ignore the 1st line if a 2nd line of the address is defined, this works fine (in almost all cases I've seen)
		// the postcode alone is often enough to get a street map view, but the post code + 2nd adress line seems to work fine

				$listingaddress =  
						//   $address_line_1.
						//   '+'.
						   $address_line_2.
						   '+'.
						   $town_city.
						   '+'.$postcode.
						   '&amp;hq='.$address_line_1.'+'.$address_line_2.	
						   '&amp;hnear='.$town_city."+".$postcode.
						   '&amp;source=s_q&amp;hl=en&amp;geocode=&amp;';
		
				
		if (strlen($address_line_2) < 5) // why 5? 
		{
		$listingaddress = $address_line_1.'+'.$listingaddress;
		}
		
		$directoryNodeModel = $this->_getDirectoryNodeModel();
				
			$thisURL = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'], $thread['title'], true);
			$thread['url'] =  $thisURL;

      /*
       for youtube vids, assume users will either put in the following:
       
       http://www.youtube.com/watch?v=TswOLHUQFPk
       http://youtu.be/TswOLHUQFPk
       http://www.youtube.com/embed/TswOLHUQFPk
       or TswOLHUQFPk
       
       we want to covert these to:
       <iframe width="420" height="315" src="http://www.youtube.com/embed/TswOLHUQFPk" frameborder="0" allowfullscreen></iframe>
       
       so just strip the first part of the above 3, and strip non alapha:
      */	
		$youtube = str_replace("http://www.youtube.com/watch?v=","", $directorylisting['youtube_url']);
		$youtube = str_replace("http://youtu.be/","", $youtube);
		$youtube = str_replace("http://www.youtube.com/embed/","", $youtube);
		$youtube = preg_replace("/[^0-9A-Za-z_-]/","", $youtube);	
		
		
		// this directory listing id: $directorylisting['directory_category']

		$directorylistingTitle = $directoryNodeModel->getNodeTitleById($directorylisting['directory_category']);
		$directorylistingURL =  XenForo_Link::buildIntegerAndTitleUrlComponent($directorylisting['directory_category'],$directorylistingTitle);
		
		$directory_phrase =  new  XenForo_Phrase('directory');

		$categories = array(
			array(
				'title' => $directorylistingTitle,
				'url' => $directorylistingURL
				),
			array(
				'title' => $directorylisting['cat2title'],
				'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($directorylisting['cat2'],$directorylisting['cat2title'])
				),	
			array(
				'title' => $directorylisting['cat3title'],
				'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($directorylisting['cat3'],$directorylisting['cat3title'])
				),
			array(
				'title' => $directorylisting['cat4title'],
				'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($directorylisting['cat4'],$directorylisting['cat4title'])
				),	
			array(
				'title' => $directorylisting['cat5title'],
				'url' => XenForo_Link::buildIntegerAndTitleUrlComponent($directorylisting['cat5'],$directorylisting['cat5title'])
				)								
			);		 		
		
		$directory_phrase =  new  XenForo_Phrase('directory');
		$breadCrumbValues = array();
		$breadCrumbValues[$directory_phrase->__toString()] = XenForo_Link::buildPublicLink('directory');
		$parent_nodes = $dirModel->getAllParentsForChildId($directorylisting['directory_category'], $allDirs);
		foreach($parent_nodes as $parent_node)
		{
			$breadCrumbValues[$parent_node['title']] = $parent_node['url'];
		}
		$breadCrumbValues[$directorylistingTitle] = XenForo_Link::buildPublicLink('directory', array('node_id' => $directorylisting['directory_category'], 'title' => $directorylistingTitle));
		$breadCrumbs = $dirModel->buidBreadCrumb($breadCrumbValues);
			
		
		$viewParams = $this->_getDefaultViewParams($forum, $thread, $posts, $page, 
		array(
			'perms' => $this->_getDirPermsModel()->getPermissions(),
			'canEditListingDetails' => $this->_getDirPermsModel()->canEditListingDetails($post, $thread, $forum, $errorPhraseKey),
			'is_claimable' => $is_claimable,
			'claimableContactLink' => $options->claimableContactLink,
			'claimableAnonymous' => $options->claimableAnonymous,
			'allowClaimable' => $options->allowClaimable,
			'allowGoogleMap' => $options->allowGoogleMap,
			'allowDeepLinks' => $options->allowDeepLinks,
			'allowLogo' => $options->allowLogo,
			'allowYoutube' => $options->allowYoutube,
			'allowContactLink' => $options->allowContactLink,
			'allowTelephone' => $options->allowTelephone,
			'allowSiteURL' => $options->allowSiteURL,
			'allowLocation' => $options->allowLocation,
			'category1' => $directorylistingTitle,
			'categories' => $categories,
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

			'deletedPosts' => $threadResponse->params['deletedPosts'],
			'moderatedPosts' => $threadResponse->params['moderatedPosts'],
			'inlineModOptions' => $threadResponse->params['inlineModOptions'],
			'firstPost' => reset($posts),
			'lastPost' => end($posts),
			'unreadLink' => $threadResponse->params['unreadLink'],
			'poll' => $threadResponse->params['poll'],
			'attachmentParams' => $threadResponse->params['attachmentParams'],
			'attachmentConstraints' => $threadResponse->params['attachmentConstraints'],

			'showPostedNotice' => $this->_input->filterSingle('posted', XenForo_Input::UINT),
			'breadCrumbs' => $breadCrumbs,
			'directorylisting' => $directorylisting,
			'listingaddress' => $listingaddress,
			'youtube' => $youtube,
		));

		

		return $this->responseView('KomuKuJVC_ControllerPublic_Reviews_View', 'sfreview_view', $viewParams);
	}

	
	
		



	
	
	

	

		
	
	

	/**
	 * Deletes an existing thread.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$directorylisting = $this->getModelFromCache('KomuKuJVC_Model_ThreadMap')->getThreadMapById($threadId);
 		$directory_category = $directorylisting['directory_category']; 
		
        
        $ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

		$threadModel = $this->_getThreadModel();
		$threadMapModel = $this->_getThreadMapModel();
		
		
		$thisURL = XenForo_Link::buildIntegerAndTitleUrlComponent($thread['thread_id'], $thread['title'], true);
		$thread['url'] =  $thisURL;
		
		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		$this->_assertCanDeleteThread($thread, $forum, $deleteType);

		if ($this->isConfirmedPost()) // delete the thread
		{
			$options = array(
				'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
			);

			$threadModel->deleteThread($threadId, $deleteType, $options);
			// delete the referenace to the thread from  kmk_jobvacan_thread_map
			$threadMapModel->deleteThreadMap($threadId, $deleteType, $options);
			// we also need to update the count for the number of listings added to this directory
			$threadMapModel->rebuildCategoryCountsById($threadId, $deleteType, $options);
			
			XenForo_Helper_Cookie::clearIdFromCookie($threadId, 'inlinemod_threads');

			

			
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('directory')
			);
		
		
		
		}
		else // show a custom delete confirmation dialog (to allow deletion of directory catagories)
		{
			return $this->responseView(
				'XenForo_ViewPublic_Thread_Delete',
				'sfreview_delete',
				array(
					'directory_category' => $directory_category,
					'threadId' => $threadId,
					'thread' => $thread,
					'forum' => $forum,
					'breadCrumbs' => $breadCrumbs,

					'canHardDelete' => $threadModel->canDeleteThread($thread, $forum, 'hard'),
				)
			);
		}
	}

	


	   
	   
	public function actionMapDirections(){

		$ftpHelper = $this->getHelper('ForumThreadPost');
	    $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);	   
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);	

	   	//  get the directory related params for this thread
		//	$db = XenForo_Application::get('db');  
		//	$directorylisting = $db->fetchRow("select * from kmk_jobvacan_thread_map where thread_id ='".$threadId."' LIMIT 1");
		$dirModel = $this->getModelFromCache('KomuKuJVC_Model_Dir');
		$directoryNodeModel = $this->_getDirectoryNodeModel();
		$directorylisting = $this->getModelFromCache('KomuKuJVC_Model_ThreadMap')->getThreadMapById($threadId);
 		$directory_category = $directorylisting['directory_category'];
 		$directorylistingTitle = $directoryNodeModel->getNodeTitleById($directorylisting['directory_category']);
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, 
			'permissionCombinationId' => 1
		));
		
		// for the address, strip out commas and unknowns (users have a tendancy to put commas where they dont belong, also dont trust this user content)
		// .. shite, I need to scan this at some point 	  
		$postcode = preg_replace("/[^0-9A-Za-z ]/","", $directorylisting['postcode']);
		$postcode = str_replace(" ","+", $postcode);
		$address_line_1 = preg_replace("/[^0-9A-Za-z ]/","", $directorylisting['address_line_1']);
		$address_line_1 = str_replace(" ","+", $address_line_1);
		$address_line_2 = preg_replace("/[^0-9A-Za-z ]/","", $directorylisting['address_line_2']);
		$address_line_2 = str_replace(" ","+", $address_line_2);
		$town_city = preg_replace("/[^0-9A-Za-z ]/","", $directorylisting['town_city']);
		$town_city = str_replace(" ","+", $town_city);
				$listingaddress =  
						//   $address_line_1.
						//   '+'.
						   $address_line_2.
						   '+'.
						   $town_city.
						   '+'.$postcode.
						   '&amp;hq='.$address_line_1.'+'.$address_line_2.	
						   '&amp;hnear='.$town_city."+".$postcode.
						   '&amp;source=s_q&amp;hl=en&amp;geocode=&amp;';
		
				
		if (strlen($address_line_2) < 5) // why 5? 
		{
		$listingaddress = $address_line_1.'+'.$listingaddress;
		}
	   
		
		$directory_phrase =  new  XenForo_Phrase('directory');
		$breadCrumbValues = array();
		$breadCrumbValues[$directory_phrase->__toString()] = XenForo_Link::buildPublicLink('directory');
		$parent_nodes = $dirModel->getAllParentsForChildId($directorylisting['directory_category'], $allDirs);
		foreach($parent_nodes as $parent_node)
		{
			$breadCrumbValues[$parent_node['title']] = $parent_node['url'];
		}
		$breadCrumbValues[$directorylistingTitle] = XenForo_Link::buildPublicLink('directory', array('node_id' => $directorylisting['directory_category'], 'title' => $directorylistingTitle));
		$breadCrumbValues[$thread['title']] = XenForo_Link::buildPublicLink('reviews', $thread);
	
		//$breadCrumbValues[$thread[]] = $parent_node['url'];
		
		$breadCrumbs = $dirModel->buidBreadCrumb($breadCrumbValues);
		
		
	   	$viewParams = array(
	   		'breadCrumbs' => $breadCrumbs,
	   		'thread' => $thread,
			'listingaddress' => $listingaddress,
		);   
	   return $this->responseView('KomuKuJVC_ControllerPublic_Map_View', 'sfdir_map_large', $viewParams);
	   }	
	   
	   	
	   
	public function actionModifyCategoriesDialogue(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();

		// need a list of all the directory categories
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
			
		// at this point we have all the directory categories (allDirs) 
	    // get the flatened category list prepared for select boxes (parents contain child, child titles have an extra dash, 1D array)
		$flat_cat_list = $dirModel->getFlatCatList($allDirs);
	    
		$selected_cats = $threadMapModel->getAllCategoriesByThreadId($threadId);
		// we now have 5 category ids: {'cat1'=>1, 'cat2'=>0, 'cat3'=>0, 'cat4'=>0, 'cat5'=>0}

		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			

			
		$viewParams = array(	
			'threadId' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL,
			'forum' => $forum,	
			'flat_cat_list' => $flat_cat_list,
			'selected_cats' => $selected_cats,
		);
		

		
		$responseView = $this->responseView('', 'sfdir_modify_categories', $viewParams);
		return $responseView;
	}	   
	   

	
	
	public function actionModifyYoutubeDialogue(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();

		// need a list of all the directory categories
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
			
		// get the current youtube url, if it exists
	    $threadMap = $threadMapModel->getThreadMapById($threadId);
	    $youtube_url = $threadMap['youtube_url'];

		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			

			
		$viewParams = array(	
			'threadId' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL,
			'forum' => $forum,
			'youtube_url' =>$youtube_url,	
		);
		

		
		$responseView = $this->responseView('', 'sfdir_modify_youtube', $viewParams);
		return $responseView;
	}
	
	
	
	
	public function actionModifyYoutube(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
				
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();
		$directoryNodeModel = $this->_getDirectoryNodeModel();

		$extraInput = $this->_input->filter(array(
			'threadURL' => XenForo_Input::STRING,
			'thread_id'  => XenForo_Input::UINT,
		));
		
		$youtube_url = $this->isURL($this->_input->filterSingle('youtube_url', XenForo_Input::STRING));		
		$threadMap = $threadMapModel->getThreadMapById($threadId);		
		$writer = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		$writer->setExistingData($extraInput['thread_id']);	
		$writer->bulkSet($threadMap);						
		$writer->set('youtube_url', $youtube_url);	
		//$writer->preSave();	
		$writer->save();

		$threadURL = $extraInput['threadURL'];     
		return $this->responseRedirect(
		XenForo_ControllerResponse_Redirect::SUCCESS, 
		XenForo_Link::buildPublicLink('reviews/'.$threadURL)
		);			
	}
	
	
	
	public function actionModifyLogoDialogue(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();

		// need a list of all the directory categories
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
			
		// get the current youtube url, if it exists
	    $threadMap = $threadMapModel->getThreadMapById($threadId);
	    $logo_image_url = $threadMap['logo_image_url'];

		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			

			
		$viewParams = array(	
			'threadId' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL,
			'forum' => $forum,
			'logo_image_url' =>$logo_image_url,	
		);
		

		
		$responseView = $this->responseView('', 'sfdir_modify_logo', $viewParams);
		return $responseView;
	}
	
	public function actionModifyLogo(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		 
				
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();
		$directoryNodeModel = $this->_getDirectoryNodeModel();

		$extraInput = $this->_input->filter(array(
			'threadURL' => XenForo_Input::STRING,
			'thread_id'  => XenForo_Input::UINT,
		));
		
		$logo_image_url = $this->isURL($this->_input->filterSingle('logo_image_url', XenForo_Input::STRING));		
		$threadMap = $threadMapModel->getThreadMapById($threadId);		
		$writer = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		$writer->setExistingData($extraInput['thread_id']);	
		$writer->bulkSet($threadMap);						
		$writer->set('logo_image_url', $logo_image_url);	
		//$writer->preSave();	
		$writer->save();

		$threadURL = $extraInput['threadURL'];     
		return $this->responseRedirect(
		XenForo_ControllerResponse_Redirect::SUCCESS, 
		XenForo_Link::buildPublicLink('reviews/'.$threadURL)
		);			
	}
		
	
	
	   
	public function actionModifyCategories(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
				
		// get options and models	
		$dirModel = $this->_getDirModel();
		$threadMapModel = $this->_getThreadMapModel();
		$directoryNodeModel = $this->_getDirectoryNodeModel();

		$extraInput = $this->_input->filter(array(
			'threadURL' => XenForo_Input::STRING,
			'thread_id'  => XenForo_Input::UINT,
		));
			
		$cat1 = $this->_input->filterSingle('cat1', XenForo_Input::UINT);
		$cat2 = $this->_input->filterSingle('cat2', XenForo_Input::UINT);
		$cat3 = $this->_input->filterSingle('cat3', XenForo_Input::UINT);
		$cat4 = $this->_input->filterSingle('cat4', XenForo_Input::UINT);
		$cat5 = $this->_input->filterSingle('cat5', XenForo_Input::UINT);
		$oldCat1 = $this->_input->filterSingle('oldCat1', XenForo_Input::UINT);
		$oldCat2 = $this->_input->filterSingle('oldCat2', XenForo_Input::UINT);
		$oldCat3 = $this->_input->filterSingle('oldCat3', XenForo_Input::UINT);
		$oldCat4 = $this->_input->filterSingle('oldCat4', XenForo_Input::UINT);
		$oldCat5 = $this->_input->filterSingle('oldCat5', XenForo_Input::UINT);
		

		// checks for duplicate categories, put in presave datawriter:	
		if(($cat1 == 0)){
			return $this->responseError('Please make sure the main category is set');
			}
		if(($cat1 == $cat2) || ($cat1 == $cat3) || ($cat1 == $cat4) || ($cat1 == $cat5)){
			return $this->responseError('Please do not use duplicate categories (category 1)');
			}
		if(($cat2 != 0) && (($cat2 == $cat3) || ($cat2 == $cat4) || ($cat2 == $cat5))){
			return $this->responseError('Please do not use duplicate categories (category 2)');
			}	
		if(($cat3 != 0) && (($cat3 == $cat4) || ($cat3 == $cat5))){
			return $this->responseError('Please do not use duplicate categories (category 3)');
			}		
		if(($cat4 != 0) && (($cat4 == $cat5))){
			return $this->responseError('Please do not use duplicate categories (category 4)');
			}	
		
		$cat1title = $directoryNodeModel->getNodeTitleById($cat1);
		$cat2title = $directoryNodeModel->getNodeTitleById($cat2);
		$cat3title = $directoryNodeModel->getNodeTitleById($cat3);
		$cat4title = $directoryNodeModel->getNodeTitleById($cat4);
		$cat5title = $directoryNodeModel->getNodeTitleById($cat5);
		
		$threadMap = $threadMapModel->getThreadMapById($threadId);		
		$writer = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		// its possible, we're trying to update an orphanded threadmap (a thread that doesnt yet have a category)
		// if so, we need to insert one
		if($threadMap){		
		$writer->setExistingData($extraInput['thread_id']);
		$writer->bulkSet($threadMap);
		}	
		else {
		$writer->set('thread_id', $extraInput['thread_id']);
		}
								
		$writer->set('directory_category', $cat1);
		$writer->set('cat1title', $cat1title);	
		$writer->set('cat2', $cat2);
		$writer->set('cat2title', $cat2title);
		$writer->set('cat3', $cat3);
		$writer->set('cat3title', $cat3title);
		$writer->set('cat4',$cat4);
		$writer->set('cat4title', $cat4title);
		$writer->set('cat5', $cat5);
		$writer->set('cat5title', $cat5title);
				
		//$writer->preSave();
		
		$writer->save();
		
		// find all the categories that have changed so we only need to rebuild the counts for these
		$change_category_ids = array();
		
		if($cat1 != $oldCat1) {
			$change_category_ids[] = $cat1;
			$change_category_ids[] = $oldCat1;
			}	
		if($cat2 != $oldCat2) {
			$change_category_ids[] = $cat2;
			$change_category_ids[] = $oldCat2;
			}	
		if($cat3 != $oldCat3) {
			$change_category_ids[] = $cat3;
			$change_category_ids[] = $oldCat3;
			}				
		if($cat4 != $oldCat4) {
			$change_category_ids[] = $cat4;
			$change_category_ids[] = $oldCat4;
			}	
		if($cat1 != $oldCat5) {
			$change_category_ids[] = $cat5;
			$change_category_ids[] = $oldCat5;
			}
			
		$threadMapModel->rebuildCategoryCountsByIds($change_category_ids);
		
		$threadURL = $extraInput['threadURL'];     
		return $this->responseRedirect(
		XenForo_ControllerResponse_Redirect::SUCCESS, 
		XenForo_Link::buildPublicLink('reviews/'.$threadURL)
		);		
	
	}   
	


		
		
	public function actionDirUpdateDialogue(){
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

		
		
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		
		
		// get options and models	
		$options = XenForo_Application::get('options');
		$dirModel = $this->_getDirModel();
		$directoryModel = $this->_getDirectoryNodeModel();
		$threadMapModel = $this->_getThreadMapModel();
		
		// need a list of all the directory categories
		$allDirs = $dirModel->getDirs(array(), array(
			'readUserId' => 1, // we're getting all the dirs, so just use admin
			'permissionCombinationId' => 1
		));
		// at this point we have all the directory categories (allDirs) 
	    // get the flatened category list prepared for select boxes (parents contain child, child titles have an extra dash, 1D array)
		$flat_cat_list = $dirModel->getFlatCatList($allDirs);
		
	    
	    // at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $threadMapModel->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);
		
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		$ftpHelper = $this->getHelper('ForumThreadPost');	
		$forum = $directoryModel->getNodeById($forumId);	
		$forumId = $forum['node_id'];

		// we need to get all of the options that have already been set for this thread... 
		// then put them in the relevant fields
		// use the same methods we use to view the review:
		
		// get the directory related params for this thread
		$directorylisting = $threadMapModel->getThreadMapById($threadId);
		
		$viewParams = array(
			'allowGoogleMap' => $options->allowGoogleMap,						'allowDeepLinks' => $options->allowDeepLinks,												
			'allowContactLink' => $options->allowContactLink,					'allowTelephone' => $options->allowTelephone,
			'allowSiteURL' => $options->allowSiteURL,							'allowLocation' => $options->allowLocation,
			'threadId' => $threadId,											'threadURL' => $threadURL,
			'forum' => $forum,													
			'displayCustomA' => $options->displayCustomA,						'customCategoryTitleA' => $options->customCategoryTitleA,
			'customFieldA1' => $options->customFieldA1,							'customFieldA2' => $options->customFieldA2,
			'customFieldA3' => $options->customFieldA3,							'customFieldA4' => $options->customFieldA4,
			'displayCustomB' => $options->displayCustomB,						'customCategoryTitleB' => $options->customCategoryTitleB,
			'customFieldB1' => $options->customFieldB1,							'customFieldB2' => $options->customFieldB2,
			'customFieldB3' => $options->customFieldB3,							'customFieldB4' => $options->customFieldB4,
			'directorylisting' => $directorylisting,							'captcha' => XenForo_Captcha_Abstract::createDefault(),
		);
		return $this->responseView('', 'sfdir_update', $viewParams);	
	}   
	 
	
	
	public function actionDirUpdate(){		
		// check user privalages for this dialogue
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditListingDetails($post, $thread, $forum);
		
		
		// get options and models	
		$threadMapModel = $this->_getThreadMapModel();	
		
		// updated this method 26/12/11 to use dataWriter (avoided manually striping unsafe chars and now allows multibyte chars)
		$input = $this->_input->filter(array(
			'telephone' => XenForo_Input::STRING,
			'address_line_1' => XenForo_Input::STRING, 			'address_line_2' => XenForo_Input::STRING,
			'town_city' => XenForo_Input::STRING, 				'postcode' => XenForo_Input::STRING,
			'website_url' => XenForo_Input::STRING, 			'website_anchor_text' => XenForo_Input::STRING,
			'deeplink1_url' => XenForo_Input::STRING, 			'deeplink1_anchor_text' => XenForo_Input::STRING,
			'deeplink2_url' => XenForo_Input::STRING, 			'deeplink2_anchor_text' => XenForo_Input::STRING,
			'deeplink3_url' => XenForo_Input::STRING, 			'deeplink3_anchor_text' => XenForo_Input::STRING,
			'customfielda1' =>  XenForo_Input::STRING, 			'customfielda2' =>  XenForo_Input::STRING,
			'customfielda3' =>  XenForo_Input::STRING, 			'customfielda4' =>  XenForo_Input::STRING,
			'customfieldb1' =>  XenForo_Input::STRING, 			'customfieldb2' =>  XenForo_Input::STRING,
			'customfieldb3' =>  XenForo_Input::STRING, 			'customfieldb4' =>  XenForo_Input::STRING,
		));
		
		$extraInput = $this->_input->filter(array(
			'threadURL' => XenForo_Input::STRING,
			'thread_id'  => XenForo_Input::UINT,
		));
		
		$input['website_url'] = $this->isURL($input['website_url']);
		$input['deeplink1_url'] = $this->isURL($input['deeplink1_url']);
		$input['deeplink2_url'] = $this->isURL($input['deeplink2_url']);
		$input['deeplink3_url'] = $this->isURL($input['deeplink3_url']);
			
		$threadMap = $threadMapModel->getThreadMapById($threadId);
			
		$writer = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ThreadMap');
		//	$writer->set('thread_id', $input['node_id']); <= this would cause an primary key error on updating!
		//  setExistingData: sets the existing data. This causes the system to do an update instead of an insert.
		$writer->setExistingData($extraInput['thread_id']);	
		$writer->bulkSet($threadMap);
		$writer->bulkSet($input);		
		
				
		$writer->preSave();
			
		$writer->save();
		$threadURL = $extraInput['threadURL'];     
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('reviews/'.$threadURL));		
	}
	
		   
	   
	public function actionClaimableDialogue(){
		// setting the listing to claimable
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);


		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanSetClaimable();
		
		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			
			
		$viewParams = array(	
			'threadid' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL
		);
		
			
		$responseView = $this->responseView('', 'sfdir_claimable', $viewParams);
		return $responseView;
	}
	
	
	public function actionClaimListingDialogue(){
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		
		// need to assert that this thread is in the "claimable" status
		
		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			
			
		$viewParams = array(	
			'threadid' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL
		);
		
			
		$responseView = $this->responseView('', 'sfdir_claim_listing', $viewParams);
		return $responseView;
	}
	
	
	
	public function actionClaimModerationEvent(){
		$this->_assertRegistrationRequired();
		$this->_assertCanClaimListing();
		
		$threadid = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
	
		$claimant_proof = $this->_input->filterSingle('proof', XenForo_Input::STRING);
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadid);
		$threadURL =XenForo_Link::buildIntegerAndTitleUrlComponent($threadid, $threadTitle, true);		
			
		 
		$visitor = XenForo_Visitor::getInstance();
		$claimant_id = $visitor['user_id'];	    $claimant = $visitor['username'];
		$claimant_email = $visitor['email'];	    $claimant_ip = $_SERVER['REMOTE_ADDR'];
	

		$threadFetchOptions = array('readUserId' => $visitor['user_id']);
		$forumFetchOptions = array('readUserId' => $visitor['user_id']);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadid, $threadFetchOptions, $forumFetchOptions);
		$origanal_owner = $thread['username'];
		///

		$listingClaim = $this->_getListingClaimModel()->getListingClaimById($threadid);		
		$writer = XenForo_DataWriter::create('KomuKuJVC_DataWriter_ListingClaim');
		// insert or update data about this claim:
		if($listingClaim){		
			$writer->setExistingData($threadid);	
		}	
		else {
			$writer->set('thread_id', $threadid);
		}
		$writer->set('thread_url', $threadURL);
		$writer->set('origanal_owner', $origanal_owner);
		$writer->set('claimant', $claimant);
		$writer->set('claimant_email', $claimant_email);
		$writer->set('claimant_id', $claimant_id);
		$writer->set('claimant_ip', $claimant_ip);
		$writer->set('claimant_proof', $claimant_proof);
		$writer->save();
		        
		$db = XenForo_Application::get('db');      	    	    
		// also, set this back to is_claimable=0 (we dont want more than one perone claiming this), but also we dont want to show the anoymous poster, so set to 3
		$db->query("UPDATE kmk_jobvacan_thread_map SET is_claimable='3' where thread_id ='".$threadid."'");
		    
		// trgger a moderation event
	    $this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue('listingclaim', $threadid, XenForo_Application::$time);
	    	
	    // return back to review
	    return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(),
				'Claim has been sent to moderation queue'
			);
	    	
	   } 
	
	
	public function actionUnclaimableDialogue(){
		$this->_assertRegistrationRequired();  	
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
	
		
		
		// at this point, we have a node_id, but no name... would like to send back node_name.node_id (url) in viewParam 
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			
					
		$viewParams = array(	
			'threadid' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL
		);
		
			
		$responseView = $this->responseView('', 'sfdir_unclaimable', $viewParams);
		return $responseView;
	}
	
	
	
	
	public function actionSetClaimable(){
		$this->_assertRegistrationRequired();
		$this->_assertCanSetClaimable();
		//$this->_assertCanEditPost($post, $thread, $forum);
		
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		
		
		
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);					
		$viewParams = array(		
			'threadid' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL
		);	
		//update this review to "claimable"
		$db = XenForo_Application::get('db'); 
		$db->query("UPDATE kmk_jobvacan_thread_map SET is_claimable='1' where thread_id ='".$threadId."'");
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			//$this->getDynamicRedirect(),
			XenForo_Link::buildPublicLink('reviews/'.$threadURL),
			'Listing set to Claimable'
		);
	}

	public function actionUnsetClaimable(){		
		$this->_assertRegistrationRequired();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		// only the thread owner or admins/mods should be alow to modify listing details
		// = anyone who has permission to edit the 1st post
		// $this->_assertCanEditThread($thread, $forum);
		// _assertThreadStarter / needed / /* To Do */
		// _assertCanEditListing / needed (thread starter / mod / admin)  /* To Do */
		$firstPostId = $thread['first_post_id'];		
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($firstPostId);
		$this->_assertCanEditPost($post, $thread, $forum);
		
		
		$threadTitle = $this->_getThreadMapModel()->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);					
		$viewParams = array(		
			'threadid' => $threadId,
			'threadTitle' => $threadTitle, 
			'threadURL' => $threadURL
		);	
		//update this review to "claimable"
		
		
		
		$db = XenForo_Application::get('db'); 
		$db->query("UPDATE kmk_jobvacan_thread_map SET is_claimable='0' where thread_id ='".$threadId."'");
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			//$this->getDynamicRedirect(),
			XenForo_Link::buildPublicLink('reviews/'.$threadURL),
			'Claimable status unset for this Listing'
		);
	}   
	   
	   
	   
	  
	   
	public function isURL($param){
	// rip out the http://  strip nasty chars, then re-add the htpp://
		$https = false;
		$param = str_replace("","",$param);
		if(strrpos($param, "") > -1){$https = true;$param = str_replace("https://","",$param);}
		$data=preg_replace("/[^A-Za-z0-9 \/\+=\?@:&,._-]/","",$param);
		if($data != "" && $https == false){$data = "".$data;}
		if($https == true){$data = "".$data;}
		return $data;  
	}   
	   

	
	protected function _assertCanEditPost(array $post, array $thread, array $forum)
	{
		if (!$this->_getPostModel()->canEditPost($post, $thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
	}	
	
	/* To Do */
	protected function _assertCanEditListing(array $thread, array $forum)
	{
		if (!$this->_getThreadMapModel()->canEditListing($thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
	}
			   
	protected function _getListingClaimModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ListingClaim');
	}   
	  	   
	protected function _getDirModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_Dir');
	}	   
	   
	protected function _getDirectoryNodeModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_DirectoryNode');
	}	   

	protected function _getThreadMapModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_ThreadMap');
	}

	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}	   
	
	protected function _getDirPermsModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_Perms');
	}	

	
		
	


	
	protected function _assertCanViewReviews()	
	{
		if (!$this->_getDirPermsModel()->canViewReviews($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}			
	}	
	
	
	protected function _assertCanSetClaimable()	
	{	
		if (!$this->_getDirPermsModel()->canSetClaimable($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}				
	}		
		
	protected function _assertCanClaimListing()	
	{	
		if (!$this->_getDirPermsModel()->canClaimListing($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}				
	}	

	
	protected function _assertCanEditListingDetails(array $post, array $thread, array $forum)
	{
		if (!$this->_getDirPermsModel()->canEditListingDetails($post, $thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
	}	
		
				

	   	   
}