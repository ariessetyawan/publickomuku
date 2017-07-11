<?php

class PostComments_Model_Comment extends XenForo_Model
{
	const FETCH_USER_POSTER = 0x01;
	const FETCH_USER_RECEIVER = 0x02;

    // Get comment by id
	public function getCommentById($id)
	{
		return $this->_getDb()->fetchRow('
			SELECT comment.*, user.*, post.user_id AS post_user_id, post.username AS post_username
			FROM `kmk_post_comments` AS comment
			LEFT JOIN kmk_user AS user
			ON (user.user_id = comment.user_id)
			LEFT JOIN kmk_post AS post
			ON (post.post_id = comment.content_id)
			WHERE comment.comment_id = ?
		', $id);
	}

	// Get comments for post id
	public function getCommentsForPostId($postId)
	{
		return $this->fetchAllKeyed('
			SELECT comment.*, user.*
			FROM `kmk_post_comments` AS comment
			LEFT JOIN kmk_user AS user 
			ON (user.user_id = comment.user_id)
			WHERE comment.content_id = ' . $this->_getDb()->quote($postId) . '
			ORDER BY comment.comment_date DESC
		', 'comment_id');
	}

	// Get users who posted on a comment
	public function getUsersForPostId($postId, $author)
	{
		$query = $this->_getDb()->fetchAll('
			SELECT `user_id`, `username`
			FROM `kmk_post_comments`
			WHERE `content_id` = ?
			AND `user_id` != ' . $this->_getDb()->quote($author) . '
		', $postId);

		if (!$query)
		{
			return false;
		}

		$query = array_map("unserialize", array_unique(array_map("serialize", $query)));

		return $query;
	}

	// Get comments for posts id
	public function getCommentsForPostIds(array $posts)
	{
		$postIds = array();

		foreach ($posts as $post)
		{
			$postIds[] = $post['post_id'];
		}

		return $this->fetchAllKeyed('
			SELECT comment.*, user.*
			FROM `kmk_post_comments` AS comment
			INNER JOIN kmk_user AS user 
			ON (comment.user_id = user.user_id)
			WHERE comment.content_id IN (' . $this->_getDb()->quote($postIds) . ')
			ORDER BY comment.comment_date DESC
		', 'comment_id');
	}

	// Get all comments
	public function getAllComments()
	{
		return $this->fetchAllKeyed('
			SELECT comment.*, user.*
			FROM `kmk_post_comments` AS comment
			FROM kmk_comment AS comment
			LEFT JOIN kmk_user AS user 
			ON (user.user_id = comment.user_id)
		', 'comment_id');
	}

	// Count all comments
	public function countComments(array $user)
    {
	    //Count all views for a profile
        return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM `kmk_post_comments`
			WHERE user_id = ?
		', $user['user_id']);
    }

	// Check interval for commenting
	public function commentFloodCheck($user = null)
	{
		$visitor = XenForo_Visitor::getInstance();
		$options = XenForo_Application::get('options');

		if ($options->comments_floodtime == 0)
		{
			return false;
		}

		$this->standardizeViewingUserReference($user);

		$query = $this->_getDb()->fetchOne('
				SELECT `comment_date`
				FROM `kmk_post_comments`
				WHERE `user_id` = ?
				ORDER BY `comment_id` DESC LIMIT 1
			', array($visitor->getUserId())
		);

		if (empty($query))
		{
			return false;
		}

		if (time() - $query < $options->comments_floodtime)
		{
			return $options->comments_floodtime - (time() - $query);
		}

		return false;
	}

	// Prepare the comments permissions
	public function prepareComment($comment, array $user = null)
	{
		$this->standardizeViewingUserReference($user);

		$comment['canDelete'] = $this->canDeleteComment($comment, $user);
		$comment['canEdit'] = $this->canEditComment($comment, $user);
		$comment['canReport'] = $this->canReportComment($comment, $user);

		return $comment;
	}

	// Can post comments
	public function canPostComment(array $user = null)
	{
		$this->standardizeViewingUserReference($user);

		return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'post');		
	}
	
	// Can edit comments
	public function canEditComment(array $comment, array $user = null)
	{
		$this->standardizeViewingUserReference($user);

        // Edit own comments only
		if ($user['user_id'] == $comment['user_id'])
		{
			return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'editOwn');
		}
		else // edit comments by everyone
		{
			return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'edit');
		}
	}

	// Can delete comments
	public function canDeleteComment(array $comment, array $user = null)
	{
		$this->standardizeViewingUserReference($user);

        // Delete own comments only
		if ($user['user_id'] == $comment['user_id'])
		{
			return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'deleteOwn');
		}
		else // delete comments by everyone
		{
			return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'delete');
		}
	}

	// Can report comments
	public function canReportComment(array $comment, array $user = null)
	{
		$this->standardizeViewingUserReference($user);

		return XenForo_Permission::hasPermission($user['permissions'], 'comments', 'report');
	}

	// Prevent abuse of the post comments system by setting up a group(s) and forum(s)daily limit. Staff is excluded
	public function dailyLimit(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null) 
	{
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

		$dailygids = XenForo_Permission::hasPermission($viewingUser['permissions'], 'comments', 'daily_comments');
		// Daily group(s) limit
		if ($dailygids > 0) 
		{
			$query = $this->countComments(array('user_id' => $viewingUser['user_id'], 'comment_date' => array('>', XenForo_Application::$time - 86400)));

			if ($query >= $dailygids)  
			{
				$errorPhraseKey = array('daily_comments_reached', 'username' => $viewingUser['username'], 'dailygids' => $dailygids);
	            return false;
			}
		}

		// Daily forum(s) limit. Staff is excluded
		$dailyfids = $forum['comment_count'];
		$dailyposts = $post['comment_count'];

		if ($dailyfids > 0) 
		{
			$dailyposts = $this->countComments(array('user_id' => $viewingUser['user_id'], 'comment_date' => array('>', XenForo_Application::$time - 86400)));
			
			if ($dailyposts >= $dailyfids AND !$viewingUser['is_admin'] AND !$viewingUser['is_moderator'] AND !$viewingUser['is_staff'])
			{
				$errorPhraseKey = array('daily_comments_fids_reached', 'username' => $viewingUser['username'], 'dailyfids' => $dailyfids);
	            return false;
			}
		}

		return true;
	}
}