<?php

class PostComments_ControllerPublic_Thread extends XFCP_PostComments_ControllerPublic_Thread
{
	public function actionIndex()
	{
		// Define some variables
		$parent = parent::actionIndex();
		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$visitor = XenForo_Visitor::getInstance();

		// Checks if it's a redirect link and to avoid any further processing
		if (array_key_exists('redirectType', $parent))
		{
			return $parent;
		}

		// Can post comments
		$canPostComment = XenForo_Permission::hasPermission($visitor['permissions'], 'comments', 'post');

		$parent->params['canPostComment'] = $canPostComment;

		// Disallow comment posting in closed threads. Staff is excluded
		if ($parent->params['thread']['discussion_open'] == 0 AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff']) 
		{
			$parent->params['canPostComment'] = false;
		}

		// Exclude forum(s) from using the post comments
		$options = XenForo_Application::get('options');
		$excludefids = $options->excludefids;

		$nodeId = $parent->params['thread']['node_id'];

		if (isset($parent->params['posts']) && !in_array($nodeId, $excludefids))
		{
			$comments = $model->getCommentsForPostIds($parent->params['posts']);

			foreach ($parent->params['posts'] AS &$post)
			{
				if ($post['comment_count'])
				{
					if (array_key_exists('comment_count', $post))
					{
						foreach ($comments as &$comment)
						{
							if ($comment['content_id'] == $post['post_id'])
							{
								$post['comments'][] = $comment;
							}
						}

						foreach ($post['comments'] as &$comment)
						{
							if ($model->canEditComment($comment))
							{
								$comment['canEdit'] = true;
							}

							if ($model->canDeleteComment($comment))
							{
								$comment['canDelete'] = true;
							}

							if ($model->canReportComment($comment))
							{
								$comment['canReport'] = true;
							}

							// Disallow comment editing/deleting in closed threads. Staff is excluded
							if ($parent->params['thread']['discussion_open'] == 0 AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff']) 
							{
								$comment['canEdit'] = false;
								$comment['canDelete'] = false;
								$comment['canReport'] = false;
							}
						}
					}
				}
			}
		}

		return $parent;
	}
}