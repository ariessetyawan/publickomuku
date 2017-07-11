<?php

/**
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Listener
{
    public static function load_class_controller($class, array &$extend)
    {
        
        switch ($class) {
            case "XenForo_ControllerPublic_Member":
                $extend[] = 'KomuKu_ThreadCount_Extends_ControllerPublic_Member';
                break;
            
            case "XenForo_ControllerPublic_Forum":
                $extend[] = 'KomuKu_ThreadCount_Extends_ControllerPublic_Forum';
                break;
                
            case "XenForo_ControllerAdmin_User":
                $extend[] = 'KomuKu_ThreadCount_Extends_ControllerAdmin_User';
                break;
        }
      
    }
    
    public static function load_class_model($class, array &$extend)
    {
        
        switch ($class) {
            case "XenForo_Model_User":
                $extend[] = 'KomuKu_ThreadCount_Extends_Model_User';
                break;
        }
      
    }
    
    public static function load_class_datawriter($class, array &$extend)
    {
    
        switch ($class) {
            case "XenForo_DataWriter_User":
                $extend[] = 'KomuKu_ThreadCount_Extends_DataWriter_User';
                break;
                
            case "XenForo_DataWriter_Forum":
                $extend[] = 'KomuKu_ThreadCount_Extends_DataWriter_Forum';
                break;
        }
    
    }
    
    public static function criteria_user($rule, array $data, array $user, &$returnValue)
    {
        switch ($rule)
        {
            case 'threads_created':
                if (isset($user['thread_count']) && ($user['thread_count'] > $data['threads'] OR $user['thread_count'] == $data['threads']))
                {
                    $returnValue = true;
                }
                else
                {
                    $returnValue = false;
                }
                break;
    
            case 'threads_maximum':
                if (isset($user['thread_count']) && $user['thread_count'] < $data['threads'])
                {
                    $returnValue = true;
                }
                else
                {
                    $returnValue = false;
                }
                break;
        }
    }
    
}
?>