<?php

/* 
 * Listens for XenForo_ControllerPublic_Member
 */

class KomuKu_PostAreas_Listener_LoadClassController
{
    public static function extendMember($class, array &$extend)
    {
        if($class == 'XenForo_ControllerPublic_Member')
        {
            $extend[] = 'KomuKu_PostAreas_ControllerPublic_Member';
        }        
    }
}