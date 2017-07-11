<?php
  
class PostComments_NewsFeedHandler_Comment extends XenForo_NewsFeedHandler_DiscussionMessage_Post
{
	protected function _prepareNewsFeedItemAfterAction(array $item, $content, array $viewingUser)
	{
		$item = parent::_prepareNewsFeedItemAfterAction($item, $content, $viewingUser);

		$comment = $this->_setFieldFromExtraData($item);

		$item['content']['comment_id'] = $comment['insert']['comment_id'];
		$item['content']['comment'] = $comment['insert']['comment'];

		return $item;
	}

	protected function _setFieldFromExtraData(array $item)
	{
		$item[$item['action']] = unserialize($item['extra_data']);

		unset($item['extra_data']);

		return $item;
	}
}