<?php

/**
 * Controller for post-related actions.
 *
 * @package XenForo_Post
 */
class KomuKuJVC_ControllerPublic_ReviewPost extends XenForo_ControllerPublic_Post
{
	
	// extend to make sure response redirects to review page rather than thread page
	


	/**
	 * Gets the redirect to a particular post in the specified thread.
	 *
	 * @param array $post
	 * @param array $thread
	 * @param constant $redirectType
	 *
	 * @return XenForo_ControllerResponse_Redirect
	 */
	public function getPostSpecificRedirect(array $post, array $thread,
		$redirectType = XenForo_ControllerResponse_Redirect::SUCCESS
	)
	{
		$page = floor($post['position'] / XenForo_Application::get('options')->messagesPerPage) + 1;
		//return $this->responseRedirect($redirectType,XenForo_Link::buildPublicLink('reviews', $thread, array('page' => $page)) . '#post1-' . $post['post_id']);		
		$threadId = $thread['thread_id'];
		$threadTitle = $this->getModelFromCache('KomuKuJVC_Model_ThreadMap')->getThreadTitleFromId($threadId);
		$threadURL = XenForo_Link::buildIntegerAndTitleUrlComponent($threadId, $threadTitle, true);			
		return $this->responseRedirect($redirectType,XenForo_Link::buildPublicLink('reviews/'.$threadURL));
	}

	/**
	 * Displays a form to edit an existing post.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEdit()
	{
		$response = parent::actionEdit();	
		return $this->responseView('XenForo_ViewPublic_Post_Edit', 'sfreviewpost_edit', $response->params);
	}




	/**
	 * To Do, actionDelete if thread deleted, remove threadMap and re-build counts
	 *
	*/





}