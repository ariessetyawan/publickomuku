<?php
  
class KomuKu_InactiveThreads_CronEntry_Inactive
{
	public static function runInactiveThreadsCron()
	{
		$threadModel = XenForo_Model::create('XenForo_Model_Thread');
		
		$db = XenForo_Application::getDb();
		$options = XenForo_Application::get('options');

		$forum = $options->inactiveForums;

		if(implode(",", $forum) == 0)
		{
			$forums = '';
		}
		else
		{
			$forums = ' AND node_id IN ('.implode(",", $forum).')';
		}

		$close = $options->inactiveClose;
		$delete = $options->inactiveDelete;
		$move = $options->inactiveMove;

	    if($close['enabled'])
		{
	    	$db->update('kmk_thread', array('discussion_open' => 0), 'last_post_date < ' . (XenForo_Application::$time - $close['days']*60*60*24) . $forums);
	    }

	    if($delete['enabled'])
		{
			$threadIds = $db->fetchCol('SELECT thread_id FROM kmk_thread WHERE last_post_date < ' . (XenForo_Application::$time - $delete['days']*60*60*24)  . $forums);

			$hardDelete = $delete['hardDelete'];
			$deleteType = ($hardDelete ? 'hard' : 'soft');
			$options = array();

			foreach ($threadIds as &$threadId) 
			{
				$threadModel->deleteThread($threadId, $deleteType, $options);
			}
	    }
	    
	    if(isset($move['enabled']) && $move['archive'])
		{
			$threadIds = $db->fetchCol('SELECT thread_id FROM kmk_thread WHERE last_post_date < ' . (XenForo_Application::$time - $move['days']*60*60*24)  . $forums);

			foreach ($threadIds as &$threadId) 
			{
			    $threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
				$threadDw->setExistingData($threadId);
				$threadDw->set('node_id', $move['archive']);
				$threadDw->save();
			}
	    }
	}
}