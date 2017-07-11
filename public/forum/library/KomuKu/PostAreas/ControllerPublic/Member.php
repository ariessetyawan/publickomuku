<?php

/**
 * @author Thomas Braunberger  
 */

class KomuKu_PostAreas_ControllerPublic_Member extends XFCP_KomuKu_PostAreas_ControllerPublic_Member
{
    /**
     * Adds the post areas to the view parameters of actionMember()     
     */

    public function actionMember()
    {
        $parent = parent::actionMember();        
        $visitor = XenForo_Visitor::getInstance();
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT); 
        $limit = XenForo_Application::getOptions()->PostAreas_MaxForums;
        
        $postCounts = $this->_getPostCounts($userId);                                          
        $threadCounts = $this->_getThreadCounts($userId);
                        
        // add threads counts to the posts conuts
        foreach ($postCounts as &$postEntry)
        {
            $postEntry['thread_count'] = '';
            
            foreach ($threadCounts as $threadEntry)
            {
                if ($threadEntry['node_id'] == $postEntry['node_id'])
                {
                    $postEntry['thread_count'] = $threadEntry['thread_count'];
                }
            }            
        }

        // will be used to determine if the „Threads“ column is shown
        $parent->params['hasCreatedAThread'] = $threadCounts ? true : false;          
        
        // add the post areas entries for which the user has view permissions to the view params         
        foreach ($postCounts as $key => $entry)
        {      
            if ($visitor->hasNodePermission($entry["node_id"],"view"))
            {
                $parent->params['postAreas'][$key] = $entry;
            }                    
        }
                
        if ($limit && isset($parent->params['postAreas']))
        {            
            $postAreas = $parent->params['postAreas'];
            $parent->params['postAreas'] = array_slice($postAreas, 0, $limit);
        }
                        
        return $parent;                
    }    
    
    
    
    
    /**
      * Returns an array of arrays. Each array contains the node id, forum name
      *  and the user's post count of that forum (zero counts aren't included). 
      * The arrays are sorted by post counts in descending order.
      * 
      * @param string $userId 
      * @return array 
     */    
    
    protected function _getPostCounts($userId)
    {
        
        /* Todo: add limit option */
        
        $db = XenForo_Application::getDb();
        
        $stmt = 'SELECT kmk_node.node_id, kmk_node.title, COUNT(post_id) AS post_count 
                 FROM kmk_post 
                   JOIN kmk_thread ON kmk_post.thread_id = kmk_thread.thread_id 
                   JOIN kmk_node ON kmk_thread.node_id = kmk_node.node_id
                 WHERE kmk_post.user_id = ? AND 
                       kmk_post.message_state = \'visible\' AND
                       kmk_thread.discussion_state =\'visible\' AND
                       kmk_node.node_type_id = \'Forum\'                       
                 GROUP BY kmk_node.title
                 ORDER BY post_count DESC';              
                
        $sql = $db->query($stmt, array($userId));
        
        return $sql->fetchAll();    
    }
    
    
    /**
     * 
     * @param type $userId
     * @return array
     */
    protected function _getThreadCounts($userId)
    {
        $db = XenForo_Application::getDb();
        
        $stmt = 'SELECT kmk_thread.node_id, COUNT(kmk_thread.thread_id) AS thread_count
                 FROM kmk_thread
                 WHERE kmk_thread.user_id = ? AND 
                       kmk_thread.discussion_state =\'visible\'
                 GROUP BY kmk_thread.node_id
                 ORDER BY thread_count DESC';
        
        $sql = $db->query($stmt, array($userId));
        
        return $sql->fetchAll();                  
    }
    
    
}