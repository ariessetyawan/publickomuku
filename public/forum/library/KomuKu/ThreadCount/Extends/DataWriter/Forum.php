<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Extends_DataWriter_Forum extends XFCP_KomuKu_ThreadCount_Extends_DataWriter_Forum
{

    public function updateCountersAfterDiscussionSave(XenForo_DataWriter_Discussion $discussionDw, $forceInsert = false)
	{
        parent::updateCountersAfterDiscussionSave($discussionDw, $forceInsert);
		
        if ($discussionDw->get('node_id') != $discussionDw->getExisting('node_id') && $discussionDw->get('discussion_state') == 'visible' && $discussionDw->getExisting('discussion_state') == 'visible')
        {
            $userId = $discussionDw->get('user_id');
        
            if($userId)
            {
        
                $currentForum = XenForo_DataWriter::create('XenForo_DataWriter_Forum', XenForo_DataWriter::ERROR_ARRAY);
                $targetForum = XenForo_DataWriter::create('XenForo_DataWriter_Forum', XenForo_DataWriter::ERROR_ARRAY);
                $targetForum->setExistingData($discussionDw->get('node_id'));
                $currentForum->setExistingData($discussionDw->getExisting('node_id'));
        
                if($currentForum->get('count_messages') && !$targetForum->get('count_messages'))
                {
                    $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_ARRAY);
                    $userDw->setExistingData($userId);
                    $userDw->set('thread_count',$userDw->get('thread_count') - 1);
                    $userDw->save();
                }
                else if (!$currentForum->get('count_messages') && $targetForum->get('count_messages'))
                {
                    $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_ARRAY);
                    $userDw->setExistingData($userId);
                    $userDw->set('thread_count',$userDw->get('thread_count') + 1);
                    $userDw->save();
                }
        
            }
        
        }
        
		else if ($discussionDw->get('discussion_state') == 'visible'
			&& ($discussionDw->getExisting('discussion_state') != 'visible' || $forceInsert)
		)
		{
		    $userId = $discussionDw->get('user_id');
		    
		    if($userId)
		    {
		        $currentForum = XenForo_DataWriter::create('XenForo_DataWriter_Forum', XenForo_DataWriter::ERROR_ARRAY);
		        $currentForum->setExistingData($discussionDw->get('node_id'));
		        
		        if($currentForum->get('count_messages'))
		        {
    		        $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_ARRAY);
    		        $userDw->setExistingData($userId);
    		        $userDw->set('thread_count',$userDw->get('thread_count') + 1);
    		        $userDw->save();
		        }
		    }
		}
		else if ($discussionDw->getExisting('discussion_state') == 'visible' && $discussionDw->get('discussion_state') != 'visible')
		{
		    $userId = $discussionDw->get('user_id');
		    
		    if($userId)
		    {
		        $currentForum = XenForo_DataWriter::create('XenForo_DataWriter_Forum', XenForo_DataWriter::ERROR_ARRAY);
		        $currentForum->setExistingData($discussionDw->get('node_id'));
		        
		        if($currentForum->get('count_messages'))
		        {
    		        $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_ARRAY);
    		        $userDw->setExistingData($userId);
    		        $userDw->set('thread_count',$userDw->get('thread_count') - 1);
    		        $userDw->save();
		        }
		    }
		}

	}
	
	public function updateCountersAfterDiscussionDelete(XenForo_DataWriter_Discussion $discussionDw)
	{
	    parent::updateCountersAfterDiscussionSave($discussionDw);
	    
	    if ($discussionDw->get('discussion_state') == 'visible' && $discussionDw->getExisting('discussion_state') == 'visible')
	    {
	        $userId = $discussionDw->get('user_id');
	        
	        if($userId)
	        {
                $currentForum = XenForo_DataWriter::create('XenForo_DataWriter_Forum', XenForo_DataWriter::ERROR_ARRAY);
		        $currentForum->setExistingData($discussionDw->get('node_id'));
		        
		        if($currentForum->get('count_messages'))
		        {
    		        $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_ARRAY);
    		        $userDw->setExistingData($userId);
    		        $userDw->set('thread_count',$userDw->get('thread_count') - 1);
    		        $userDw->save();
		        }
	        }
	    }
	}

}