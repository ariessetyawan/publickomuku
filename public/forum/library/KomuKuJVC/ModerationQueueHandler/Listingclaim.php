<?php

class KomuKuJVC_ModerationQueueHandler_Listingclaim extends XenForo_ModerationQueueHandler_Abstract
{
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		// the claim is just acting on a thread (review), so get the thread model
		
		/* @var $threadModel XenForo_Model_Thread */
		$threadModel = XenForo_Model::create('XenForo_Model_Thread');
		$threads = $threadModel->getThreadsByIds($contentIds, array(
			'join' => XenForo_Model_Thread::FETCH_FORUM | XenForo_Model_Thread::FETCH_FIRSTPOST,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));

		// we need to display more information about this claim than just the thread meassage/id/user
		// infact, we need the message that the user postsed for proof, we need the orignal owner and the new owner, also display users email address!
		// admins / mods can then decied if this information is enough to claim the listing
		
		// splat all of this out into the message (and replace the thread message)
		/*
		Listing URL: 
		Original Owner Username:
		Claimant User Name:
		Claimant email address:
		Claimant IP:
		
		Claimant Proof:
		
		*/
		// This is extremely hacky, but works for now

		
		$db = XenForo_Application::get('db');  
		$listingclaims = $db->fetchAll('select * from kmk_jobvacan_listingclaim');
		
		// we now have 2 arrays that should match at thread_id, so lets merge them
		
		// use thread_id as array key for messages:
		
		$messages = array();
		foreach($listingclaims AS $listingclaim){
			$id = $listingclaim['thread_id'];
			$messages[$id] =
			"\n listing url: index.php?reviews/".$listingclaim['thread_url'].
			"\n original owner: ".$listingclaim['origanal_owner'].
			"\n claimers username: ".$listingclaim['claimant']. 	
			"\n claimers email: ".$listingclaim['claimant_email']. 	
			"\n claimers ip: ".$listingclaim['claimant_ip'].	
			"\n claimers proof: ".$listingclaim['claimant_proof'];
					
		}		
 
		
		$output = array();
		foreach ($threads AS $thread)
		{
			
			// only mention, if there exists data for this, avoid errors if incomplete transer / data was not inserted into kmk_jobvacan_listingclaim, 
			// but was inserted into kmk_moderation_queue, JUST CHECK $messages[$thread['thread_id'] EXISTS
			if(isset($messages[$thread['thread_id']])){

				
				$thread['permissions'] = XenForo_Permission::unserializePermissions($thread['node_permission_cache']);
	
				$canManage = true;
				if (!$threadModel->canViewThreadAndContainer(
					$thread, $thread, $null, $thread['permissions'], $viewingUser
				))
				{
					$canManage = false;
				}
				else if (!XenForo_Permission::hasContentPermission($thread['permissions'], 'editAnyPost')
					|| !XenForo_Permission::hasContentPermission($thread['permissions'], 'deleteAnyThread')
				)
				{
					$canManage = false;
				}
	
				if ($canManage)
				{
					$output[$thread['thread_id']] = array(
						'message' => $messages[$thread['thread_id']],
						//$thread['message'],
						'user' => array(
							'user_id' => $thread['user_id'],
							'username' => $thread['username']
						),
						'title' => $thread['title'],
						'link' => XenForo_Link::buildPublicLink('directory/claimlisting', $thread),
						'contentTypeTitle' => new XenForo_Phrase('Listing Claim'),
						'titleEdit' => true
					);
				}
			}
			
		}

		return $output;
	}

	public function approveModerationQueueEntry($contentId, $message, $title)
	{
		$db = XenForo_Application::get('db');
		// update the thread user to the new thread user:
		
		// since the new user isnt a parameter for the approveModerationQueueEntry, and there doesnt seem to be a way to tunnel it in
		// 1st we need to look for the request with this contentid (new table)
		// (there can be only one, if it's rejected it should be removed, if approved removed, if left.. leave it in..
		// more than one claim requests per listing shouldn't be made, so the claim button should only be pressed once..and then disapear until the mod rejects/accepts it
		// on acception, the review should be updated to is_claimable=0 and the thread owner needs to be updated
		
		$listingclaims = $db->fetchRow('select * from kmk_jobvacan_listingclaim where thread_id = "'.$contentId.'"');
		$claim_username = $listingclaims['claimant'];
		$claim_user_id = $listingclaims['claimant_id'];
		// update the thread owner, also update last poster to avoid exposing anonymous users
		$db->query('UPDATE kmk_thread SET username="'.$claim_username.'", user_id="'.$claim_user_id.'", last_post_username="'.$claim_username.'", last_post_user_id="'.$claim_user_id.'" WHERE thread_id="'.$contentId.'"');
					
		// now update the 1st post_id for this thread
		$db->query('UPDATE kmk_post SET username="'.$claim_username.'", user_id="'.$claim_user_id.'" WHERE post_id = (select first_post_id from kmk_thread WHERE thread_id="'.$contentId.'")');
		
		// now set this to unclaimable (others shouldn't be able to claim it)
		$db->query("UPDATE kmk_jobvacan_thread_map SET is_claimable='0' where thread_id ='".$contentId."'");
		
		$queueModel = XenForo_Model::create('XenForo_Model_ModerationQueue');
		
		// create a model for this at some point: 
		$db->query("delete from kmk_jobvacan_listingclaim where thread_id = '".$contentId."'");
		return $queueModel->deleteFromModerationQueue('listingclaim', $contentId);
	}

	public function deleteModerationQueueEntry($contentId)
	{
		$queueModel = XenForo_Model::create('XenForo_Model_ModerationQueue');

		// create a model for this at some point: 
		$db = XenForo_Application::get('db');
		$db->query("delete from kmk_jobvacan_listingclaim where thread_id = '".$contentId."'");
		
		// this claim has been rejected, so set this back to claimable so others can claim it
		$db->query("UPDATE kmk_jobvacan_thread_map SET is_claimable='1' where thread_id ='".$contentId."'");
		
		return $queueModel->deleteFromModerationQueue('listingclaim', $contentId);
	}
}