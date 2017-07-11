<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Setup
{
    private static $_instance;

    protected $_db;

    public static final function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    protected function _getDb()
    {
        if ($this->_db === null)
        {
            $this->_db = XenForo_Application::get('db');
        }

        return $this->_db;
    }

    public static function install($existingAddOn, $addOnData)
    {
        $startVersion = 1;
        $endVersion = $addOnData['version_id'];

        if ($existingAddOn)
        {
            $startVersion = $existingAddOn['version_id'] + 1;
        }

        $install = self::getInstance();

        for ($i = $startVersion; $i <= $endVersion; $i++)
        {
            $method = '_installVersion' . $i;

            if (method_exists($install, $method) === false)
            {
                continue;
            }

            $install->$method();
        }
    }
    
    public static function unInstall($addOnData)
    {
        $startVersionId = $addOnData['version_id'];
        $endVersionId = 1;
        
        $unInstall = self::getInstance();
        
        for ($i = $startVersionId; $i >= $endVersionId; $i--)
        {
            $method = '_unInstallVersion' . $i;
            if (method_exists($unInstall, $method) === false)
            {
                continue;
            }
        
            $unInstall->$method();
        }
    }

    protected function _installVersion1()
    {
        $db = $this->_getDb();
        
        if(!$db->fetchRow("SHOW COLUMNS FROM kmk_user WHERE Field = 'thread_count'"))
        {
            $db->query("ALTER TABLE `kmk_user`
                    ADD COLUMN `thread_count` INT UNSIGNED NOT NULL DEFAULT 0");
        }
        
        $db->query("UPDATE kmk_user AS user
                    SET thread_count = (
                    	SELECT COUNT(*)
                    	FROM kmk_thread AS thread
                        LEFT JOIN kmk_forum AS forum ON (forum.node_id = thread.node_id)
                    	WHERE thread.user_id = user.user_id
                    	AND thread.discussion_state = 'visible'
                        AND forum.count_messages = 1
                    )");
       
    }
    
    protected function _installVersion12()
    {
        $db = $this->_getDb();
    
        $db->query("UPDATE kmk_option
                    SET edit_format_params = 'trophy_points={xen:phrase trophy_points}\nmessage_count={xen:phrase messages}\nlike_count={xen:phrase likes}\nthread_count={xen:phrase threads}'
                    WHERE option_id = 'userTitleLadderField';");
         
    }
    
    protected function _installVersion100()
    {
        $db = $this->_getDb();
    
        $db->query("UPDATE kmk_user AS user
                    SET thread_count = (
                    	SELECT COUNT(*)
                    	FROM kmk_thread AS thread
                        LEFT JOIN kmk_forum AS forum ON (forum.node_id = thread.node_id)
                    	WHERE thread.user_id = user.user_id
                    	AND thread.discussion_state = 'visible'
                        AND forum.count_messages = 1
                    )");
         
    }
    
    protected function _unInstallVersion12()
    {
        $db = $this->_getDb();
    
        $db->query("UPDATE kmk_option 
                    SET edit_format_params = 'trophy_points={xen:phrase trophy_points}\nmessage_count={xen:phrase messages}\nlike_count={xen:phrase likes}',
                    option_value = 'trophy_points'
                    WHERE option_id = 'userTitleLadderField';");
    }
    
    protected function _unInstallVersion1()
    {
        $db = $this->_getDb();
    
        $db->query("ALTER TABLE `kmk_user`
                    DROP COLUMN `thread_count`");
    }

}