<?php

class KomuKuJVC_Model_Perms extends XenForo_Model
{
	public function getPermissions(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);			
		if(!array_key_exists('permissions', $viewingUser))
		{
			$viewingUser['permissions'] = XenForo_Permission::unserializePermissions($viewingUser['global_permission_cache']);
		}
		$perms['canViewDirectory'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canViewDirectory') ? true : false);
		$perms['canViewReviews'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canViewReviews') ? true : false);
		$perms['canSubmitListing'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canSubmitListing') ? true : false);
		$perms['canClaimListing'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canClaimListing') ? true : false);
		$perms['canSetClaimable'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canSetClaimable') ? true : false);
		$perms['canEditOwnListingDetails'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canEditOwnListingDetails') ? true : false);
		$perms['canEditAnyListingDetails'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canEditAnyListingDetails') ? true : false);		  	
		return $perms;
	}
	
	
	
	
	public function canSetClaimable(&$errorPhraseKey = '', array $viewingUser = null){
	
		$this->standardizeViewingUserReference($viewingUser);
	
		$perms['canSetClaimable'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canSetClaimable') ? true : false);

		if (!$perms['canSetClaimable'])
		{
			$errorPhraseKey = 'can_set_claimable_no_perm';
			return false;
		}	
		
		if ($perms['canSetClaimable']){
			return true;	
		}
		
		return false;
		
	}
	
	
	public function canClaimListing(&$errorPhraseKey = '', array $viewingUser = null){
	
		$this->standardizeViewingUserReference($viewingUser);
			
		$perms['canClaimListing'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canClaimListing') ? true : false);

		if (!$perms['canClaimListing'])
		{
			$errorPhraseKey = 'can_claim_listing_no_perm';
			return false;
		}	
		
		if ($perms['canClaimListing']){
			return true;	
		}
		
		return false;
	}	
	
	
	public function canSubmitListing(&$errorPhraseKey = '', array $viewingUser = null){
	
		$this->standardizeViewingUserReference($viewingUser);
		
		
		$perms['canSubmitListing'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canSubmitListing') ? true : false);

		if (!$perms['canSubmitListing'])
		{
			$errorPhraseKey = 'can_submit_listing_no_perm';
			return false;
		}	
		
		if ($perms['canSubmitListing']){
			return true;	
		}
		
		return false;
	}
	
		
	public function canViewReviews(&$errorPhraseKey = '', array $viewingUser = null){
	
		$this->standardizeViewingUserReference($viewingUser);
		
		
		$perms['canViewReviews'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canViewReviews') ? true : false);

		if (!$perms['canViewReviews'])
		{
			$errorPhraseKey = 'can_view_reviews_no_perm';
			return false;
		}	
		
		if ($perms['canViewReviews']){
			return true;	
		}
		
		
		return false;
				
	}		
	
	
	public function canViewDirectory(&$errorPhraseKey = '', array $viewingUser = null){
	
		$this->standardizeViewingUserReference($viewingUser);
		

		
		$perms['canViewDirectory'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canViewDirectory') ? true : false);

		if (!$perms['canViewDirectory'])
		{
			$errorPhraseKey = 'can_view_directory_no_perm';
			return false;
		}	
		
		if ($perms['canViewDirectory']){
			return true;	
		}
		
		
		return false;
				
	}	
	


	
	
	
	
	
	public function canEditListingDetails(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

		$perms['canEditOwnListingDetails'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canEditOwnListingDetails') ? true : false);
		$perms['canEditAnyListingDetails'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'KomuKuJVC', 'canEditAnyListingDetails') ? true : false);
		
		// check that they can edit any listing details = true, 
		// check if they own this thread, if so, check they can edit their own listing details
		if ($perms['canEditAnyListingDetails']){
			return true;	
		}

		if ($post['user_id'] == $viewingUser['user_id'] && $perms['canEditOwnListingDetails'])
		{
			return true;
		}

		$errorPhraseKey = 'can_edit_listing_details_no_perm';
		return false;
	}
	
	
	
	
	
	
		
	
	
}