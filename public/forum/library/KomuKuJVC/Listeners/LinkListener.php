<?php

class KomuKuJVC_Listeners_LinkListener
{
    public static function loadClassListener($class, $extend)
    {
       
  //  var_dump($class);
    
        if ($class == 'XenForo_Link')
        {        
            $extend[] = 'KomuKuJVC_Link';          
        }    


        
    }
}