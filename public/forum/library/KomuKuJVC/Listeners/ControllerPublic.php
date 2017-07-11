<?php

class KomuKuJVC_Listeners_ControllerPublic
{
    public static function loadClassListener($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerPublic_Forum')
        {        
            $extend[] = 'KomuKuJVC_ControllerPublic_Forum';          
        }
        
    
 


        
    }
}