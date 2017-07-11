<?php

class PostComments_ReportHandler_PostComment extends XenForo_ReportHandler_Abstract
{
	/**
	 * Gets report details from raw array of content (eg, a post record).
	 *
	 * @see XenForo_ReportHandler_Abstract::getReportDetailsFromContent()
	 */
	public function getReportDetailsFromContent(array $content)
	{
		$commentModel = XenForo_Model::create('PostComments_Model_Comment');

		$comment = $commentModel->getCommentById($content['comment_id']);

		if (!$comment)
		{
			return array(false, false, false);
		}

		return array(
			$content['content_id'],
			$content['user_id'],
			array(
				'post_user_id' => $comment['post_user_id'],
				'post_username' => $comment['post_username'],
				'comment_user_id' => $comment['user_id'],
				'comment_username' => $comment['username'],

				'message' => $comment['comment']
			)
		);
	}

	/**
	 * Gets the visible reports of this content type for the viewing user.
	 *
	 * @see XenForo_ReportHandler_Abstract:getVisibleReportsForUser()
	 */
	public function getVisibleReportsForUser(array $reports, array $viewingUser)
	{
		$reportsByUser = array();
		foreach ($reports AS $reportId => $report)
		{
			$info = unserialize($report['content_info']);
			$reportsByUser[$info['comment_user_id']][] = $reportId;
		}

		$users = XenForo_Model::create('XenForo_Model_User')->getUsersByIds(array_keys($reportsByUser), array(
			'join' => XenForo_Model_User::FETCH_USER_PRIVACY,
			'followingUserId' => $viewingUser['user_id']
		));

		$userProfileModel = XenForo_Model::create('XenForo_Model_UserProfile');

		foreach ($reportsByUser AS $userId => $userReports)
		{
			$remove = false;

			if (!isset($users[$userId]))
			{
				$remove = true;
			}
			else if (!$userProfileModel->canViewFullUserProfile($users[$userId], $null, $viewingUser))
			{
				$remove = true;
			}
			else if (!XenForo_Permission::hasPermission($viewingUser['permissions'], 'comments', 'edit')
				&& !XenForo_Permission::hasPermission($viewingUser['permissions'], 'comments', 'delete')
			)
			{
				$remove = true;
			}

			if ($remove)
			{
				foreach ($userReports AS $reportId)
				{
					unset($reports[$reportId]);
				}
			}
		}

		return $reports;
	}

	/**
	 * Gets the title of the specified content.
	 *
	 * @see XenForo_ReportHandler_Abstract:getContentTitle()
	 */
	public function getContentTitle(array $report, array $contentInfo)
	{
		return new XenForo_Phrase('post_comment_for_x', array('username' => $contentInfo['comment_username']));
	}

	/**
	 * Gets the link to the specified content.
	 *
	 * @see XenForo_ReportHandler_Abstract::getContentLink()
	 */
	public function getContentLink(array $report, array $contentInfo)
	{
		return XenForo_Link::buildPublicLink('posts', array('post_id' => $report['content_id']));
	}

	/**
	 * A callback that is called when viewing the full report.
	 *
	 * @see XenForo_ReportHandler_Abstract::viewCallback()
	 */
	public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
	{
		return $view->createTemplateObject('report_post_comment_content', array(
			'report' => $report,
			'content' => $contentInfo
		));
	}
}