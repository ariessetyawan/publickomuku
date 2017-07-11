<?php

class BestAnswer_DataWriter_DiscussionMessage_Post extends XFCP_BestAnswer_DataWriter_DiscussionMessage_Post
{
	protected function _getCommonFields()
	{
		$response = parent::_getCommonFields();
	
		$response['kmk_post']['best_answer_points'] = array('type' => self::TYPE_UINT, 'default' => 0);
	
		return $response;
	}
	
	protected function _messagePostDelete()
	{
		parent::_messagePostDelete();
		
		$thread = $this->getDiscussionData();
		$this->getModelFromCache('BestAnswer_Model_BestAnswer')->recalculateBestAnswerForThread($thread);
	}
	
	protected function _messagePostSave()
	{
		parent::_messagePostSave();
		
		if ($this->isChanged('message_state'))
		{
			$thread = $this->getDiscussionData();
			$this->getModelFromCache('BestAnswer_Model_BestAnswer')->recalculateBestAnswerForThread($thread);
		}
	}
}