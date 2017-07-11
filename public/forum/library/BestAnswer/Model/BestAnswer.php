<?php

class BestAnswer_Model_BestAnswer extends XenForo_Model
{
	const CHOSEN_BY_THREAD_CREATOR = 1;
	const CHOSEN_BY_COMMUNITY = 2;
	
	public function canMarkAsBestAnswer(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		if (!$viewingUser['user_id'])
		{
			return false;
		}
		
		if ($post['message_state'] != 'visible')
		{
			return false;
		}
		
		if ($post['position'] == 0)
		{
			return false;
		}
		
		if (!in_array($forum['node_id'], XenForo_Application::get('options')->bestAnswerEnabledForums))
		{
			return false;
		}
		
		$enabledPrefixes = array_keys(XenForo_Application::get('options')->bestAnswerEnabledPrefixes);
		if (!empty($enabledPrefixes) && !in_array($thread['prefix_id'], $enabledPrefixes))
		{
			return false;
		}
		
		if (!XenForo_Permission::hasContentPermission($nodePermissions, 'markPostAsBestAnswer'))
		{
			return false;
		}
		
		if (
			XenForo_Application::get('options')->bestAnswerWhoChooses == self::CHOSEN_BY_THREAD_CREATOR
			&& !XenForo_Permission::hasContentPermission($nodePermissions, 'markAnyPostAsBestAnswer')
			&& $thread['user_id'] != $viewingUser['user_id']
		)
		{
			return false;
		}
		
		if (
			XenForo_Application::get('options')->bestAnswerWhoChooses == self::CHOSEN_BY_COMMUNITY
			&& $post['user_id'] == $viewingUser['user_id']
			&& $thread['user_id'] != $post['user_id']
			&& !XenForo_Permission::hasContentPermission($nodePermissions, 'markAnyPostAsBestAnswer')
		)
		{
			return false;
		}
		
		return true;
	}
	
	public function markAsBestAnswer($thread, $post)
	{
		$visitor = XenForo_Visitor::getInstance()->toArray();
		
		if (XenForo_Application::get('options')->bestAnswerWhoChooses == self::CHOSEN_BY_THREAD_CREATOR)
		{
			/* @var $threadDw XenForo_DataWriter_Discussion_Thread */
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
			$threadDw->setExistingData($thread['thread_id']);
			$threadDw->set('best_answer_id', $post['post_id']);
			$threadDw->save();
			
			$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$userDw->setExistingData($post['user_id']);
			$userDw->set('best_answer_count', $userDw->get('best_answer_count') + 1);
			$userDw->save();
			
			if ($thread['best_answer_id'])
			{
				$prevBestAnswer = $this->getModelFromCache('XenForo_Model_Post')->getPostById($thread['best_answer_id']);
				
				$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
				$userDw->setExistingData($prevBestAnswer['user_id']);
				$userDw->set('best_answer_count', $userDw->get('best_answer_count') - 1);
				$userDw->save();
				
				$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('post', $prevBestAnswer['post_id'], null, 'best_answer');
			}
			
			if ($visitor['user_id'] != $post['user_id'])
			{
				XenForo_Model_Alert::alert($post['user_id'], $visitor['user_id'], $visitor['username'], 'post', $post['post_id'], 'best_answer');
			}
		}
		else
		{
			$this->_getDb()->query('
				INSERT INTO kmk_best_answer_vote
					(thread_id, post_id, user_id, vote_date, power)
				VALUES
					(?, ?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE
					post_id = VALUES(post_id),
					vote_date = VALUES(vote_date)
			', array($thread['thread_id'], $post['post_id'], $visitor['user_id'], XenForo_Application::$time, max(1, XenForo_Visitor::getInstance()->hasNodePermission($thread['node_id'], 'bestAnswerVotingPower'))));
			
			$bestAnswers = $this->recalculateBestAnswerForThread($thread);
			
			if ($bestAnswers && $bestAnswers[0]['post_id'] != $thread['best_answer_id'])
			{
				XenForo_Model_Alert::alert($bestAnswers[0]['user_id'], $visitor['user_id'], $visitor['username'], 'post', $bestAnswers[0]['post_id'], 'best_answer');
			}
		}
	}
	
	public function unmarkAsBestAnswer($thread, $post)
	{
		$visitor = XenForo_Visitor::getInstance()->toArray();
		
		if (XenForo_Application::get('options')->bestAnswerWhoChooses == self::CHOSEN_BY_THREAD_CREATOR)
		{
			/* @var $threadDw XenForo_DataWriter_Discussion_Thread */
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
			$threadDw->setExistingData($thread['thread_id']);
			$threadDw->set('best_answer_id', 0);
			$threadDw->save();
		
			$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$userDw->setExistingData($post['user_id']);
			$userDw->set('best_answer_count', $userDw->get('best_answer_count') - 1);
			$userDw->save();
		
			$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('post', $post['post_id'], null, 'best_answer');
		}
		else
		{
			$this->_getDb()->query('
				DELETE FROM kmk_best_answer_vote
				WHERE
					thread_id = ?
					AND user_id = ?
			', array($thread['thread_id'], $visitor['user_id']));
			
			$this->recalculateBestAnswerForThread($thread);
		}
	}
	
	public function postMarkedAsBestAnswer($post, $thread)
	{
		if (
		(XenForo_Application::get('options')->bestAnswerWhoChooses == BestAnswer_Model_BestAnswer::CHOSEN_BY_THREAD_CREATOR && $post['post_id'] == $thread['best_answer_id'])
		|| (XenForo_Application::get('options')->bestAnswerWhoChooses == BestAnswer_Model_BestAnswer::CHOSEN_BY_COMMUNITY && $post['post_id'] == @$thread['user_best_answer_id'])
		)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * Returns the thread's best answers.
	 */
	public function recalculateBestAnswerForThread($thread)
	{
		if (XenForo_Application::get('options')->bestAnswerWhoChooses == self::CHOSEN_BY_COMMUNITY)
		{
			$visitor = XenForo_Visitor::getInstance()->toArray();
			
			$this->_getDb()->query('
				UPDATE kmk_post
				SET best_answer_points = 0
				WHERE
					thread_id = ?
			', array($thread['thread_id']));
			
			$bestAnswers = $this->_getDb()->fetchAll('
				SELECT vote.post_id, post.user_id, SUM(vote.power) AS points, COUNT(vote.vote_id) AS num_votes
				FROM kmk_best_answer_vote AS vote
				INNER JOIN kmk_post AS post
					ON (post.post_id = vote.post_id)
				WHERE vote.thread_id = ? AND post.message_state = \'visible\'
				GROUP BY vote.post_id
				HAVING points >= ?
				ORDER BY points DESC, num_votes DESC, post.post_date ASC 
				LIMIT ?
			', array($thread['thread_id'], XenForo_Application::getOptions()->bestAnswerMinimumVotes, (XenForo_Application::getOptions()->bestAnswerAlternativeAnswersNumber + 1)));
			
			if ($bestAnswers)
			{
				$bestAnswer = $bestAnswers[0];
				$fullBestAnswers = $bestAnswers;
				unset($bestAnswers[0]); // $bestAnswers now contains the alternative answers, if such exist
				
				/* @var $threadDw XenForo_DataWriter_Discussion_Thread */
				$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
				$threadDw->setExistingData($thread['thread_id']);
				$threadDw->set('best_answer_id', $bestAnswer['post_id']);
				
				/* @var $threadDw XenForo_DataWriter_DiscussionMessage_Post */
				$postDw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
				$postDw->setExistingData($bestAnswer['post_id']);
				$postDw->set('best_answer_points', $bestAnswer['points']);
				$postDw->save();
				
				$alternativeAnswers = array();
				foreach ($bestAnswers AS $answer)
				{
					$alternativeAnswers[] = $answer['post_id'];
					
					/* @var $threadDw XenForo_DataWriter_DiscussionMessage_Post */
					$postDw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
					$postDw->setExistingData($answer['post_id']);
					$postDw->set('best_answer_points', $answer['points']);
					$postDw->save();
				}
				
				$threadDw->set('alternative_best_answers', implode(",", $alternativeAnswers));
				
				$threadDw->save();
				
				$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
				$userDw->setExistingData($bestAnswer['user_id']);
				$userDw->set('best_answer_count', $userDw->get('best_answer_count') + 1);
				try
				{
					$userDw->save();
				}
				catch (XenForo_Exception $ex)
				{
					
				}
				
				if ($thread['best_answer_id'])
				{
					$prevBestAnswer = $this->getModelFromCache('XenForo_Model_Post')->getPostById($thread['best_answer_id']);
				
					$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
					$userDw->setExistingData($prevBestAnswer['user_id']);
					$userDw->set('best_answer_count', $userDw->get('best_answer_count') - 1);
					try
					{
						$userDw->save();
					}
					catch (XenForo_Exception $ex)
					{
						
					}
				
					//$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('post', $prevBestAnswer['post_id'], null, 'best_answer');
				}
					
				if ($visitor['user_id'] != $bestAnswer['user_id'])
				{
					//XenForo_Model_Alert::alert($bestAnswer['user_id'], $visitor['user_id'], $visitor['username'], 'post', $bestAnswer['post_id'], 'best_answer');
				}
				
				return $fullBestAnswers;
			}
			else if ($thread['best_answer_id'])
			{
				/* @var $threadDw XenForo_DataWriter_Discussion_Thread */
				$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
				$threadDw->setExistingData($thread['thread_id']);
				$threadDw->set('best_answer_id', 0);
				$threadDw->set('alternative_best_answers', '');
				$threadDw->save();
				
				$prevBestAnswer = $this->getModelFromCache('XenForo_Model_Post')->getPostById($thread['best_answer_id']);
				
				$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
				$userDw->setExistingData($prevBestAnswer['user_id']);
				$userDw->set('best_answer_count', $userDw->get('best_answer_count') - 1);
				try
				{
					$userDw->save();
				}
				catch (XenForo_Exception $ex)
				{
					
				}
				
				$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('post', $prevBestAnswer['post_id'], null, 'best_answer');
			}
			
			return array();
		}
	}
	
	/**
	 * Rebuilds the thread's prefix according to whether it's answered or not.
	 */
	public function rebuildThreadPrefix($thread)
	{
		$answeredPrefix = XenForo_Application::getOptions()->bestAnswerAnsweredPrefix;
		
		if ($thread['best_answer_id'] && $answeredPrefix['type'] == 'custom' && $thread['prefix_id'] != $answeredPrefix['custom'])
		{
			/* @var $threadDw XenForo_DataWriter_Discussion_Thread */
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
			$threadDw->setExistingData($thread['thread_id']);
			$threadDw->set('unanswered_prefix_id', $thread['prefix_id']);
			$threadDw->set('prefix_id', $answeredPrefix['custom']);
			$threadDw->save();
		}
	}
	
	public function getBestAnswersByUserId($userId, $limit, $lastPostId = 0)
	{
		/* @var $postModel XenForo_Model_Post */
		$postModel = $this->getModelFromCache('XenForo_Model_Post');
		
		$bind = array($userId);
		
		if ($lastPostId)
		{
			$bind[] = $lastPostId;
		}
		
		$bind[] = $limit;
		
		$posts = $this->fetchAllKeyed('
				SELECT
					post.*,
					thread.*, thread.user_id AS thread_user_id, thread.username AS thread_username, thread.post_date AS thread_post_date,
					post.user_id, post.username, post.post_date,
					node.title AS node_title,
					user.*, IF(user.username IS NULL, post.username, user.username) AS username
				FROM kmk_post AS post
				LEFT JOIN kmk_thread AS thread
					ON (thread.thread_id = post.thread_id)
				LEFT JOIN kmk_user AS user
					ON (user.user_id = post.user_id)
				LEFT JOIN kmk_node AS node
					ON (node.node_id = thread.node_id)
				WHERE post.user_id = ?
					AND post.post_id = thread.best_answer_id
					' . ($lastPostId ? 'AND post.post_id < ?' : '') . '
				ORDER BY post.post_id DESC
				LIMIT ?
			', 'post_id', $bind);
		
		foreach ($posts AS $postId => &$post)
		{
			if (!$postModel->canViewPostAndContainer($post, $post, $post))
			{
				unset($posts[$postId]);
				continue;
			}
			
			$post['forum']['title'] = $post['node_title'];
		}
		
		return $posts;
	}
}