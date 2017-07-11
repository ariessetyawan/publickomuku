<?php

class BestAnswer_DataWriter_Discussion_Thread extends XFCP_BestAnswer_DataWriter_Discussion_Thread
{
	protected function _getCommonFields()
	{
		$response = parent::_getCommonFields();
		
		$response['kmk_thread']['best_answer_id'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$response['kmk_thread']['unanswered_prefix_id'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$response['kmk_thread']['alternative_best_answers'] = array('type' => self::TYPE_STRING, 'default' => '');
		
		return $response;
	}
	
	protected function _delete()
	{
		parent::_delete();
		
		if ($this->get('best_answer_id'))
		{
			$bestAnswer = $this->_getPostModel()->getPostById($this->get('best_answer_id'));
			$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$userDw->setExistingData($bestAnswer['user_id']);
			$userDw->set('best_answer_count', $userDw->get('best_answer_count') - 1);
			$userDw->save();
		}
	}
	
	public function rebuildDiscussionCounters($replyCount = false, $firstPostId = false, $lastPostId = false)
	{
		parent::rebuildDiscussionCounters();
		
		$bestAnswerModel = $this->getModelFromCache('BestAnswer_Model_BestAnswer');
		
		$bestAnswerModel->recalculateBestAnswerForThread($this->_existingData['kmk_thread']);
		$bestAnswerModel->rebuildThreadPrefix($this->_existingData['kmk_thread']);
	}
	
	protected function _discussionPreSave()
	{
		parent::_discussionPreSave();
		
		if ($this->get('best_answer_id') && $this->isChanged('node_id'))
		{
			if (!in_array($this->get('node_id'), XenForo_Application::get('options')->bestAnswerEnabledForums))
			{
				$this->set('best_answer_id', 0);
			}
		}
		
		if ($this->isChanged('best_answer_id'))
		{
			$answeredPrefix = XenForo_Application::getOptions()->bestAnswerAnsweredPrefix;
			if ($answeredPrefix['type'] == 'custom')
			{
				if ($this->get('best_answer_id'))
				{
					$this->set('unanswered_prefix_id', $this->get('prefix_id'));
					$this->set('prefix_id', $answeredPrefix['custom']);
				}
				else
				{
					$this->set('prefix_id', $this->get('unanswered_prefix_id'));
					$this->set('unanswered_prefix_id', 0);
				}
			}
		}
	}
}