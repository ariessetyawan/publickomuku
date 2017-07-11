<?php

// extend so that threads within the directory forum are redirected to reviews

class KomuKuJVC_Listeners_Threads 
{
	// string $class - the name of the class to be created
    // array &$extend - a modifiable list of classes that wish to extend the class. See below.		
	public static function loadClassRoutePrefix($class, array &$extend)
    {
        if ($class == 'XenForo_Route_Prefix_Threads')
        {        
            $extend[] = 'KomuKuJVC_Route_Prefix_Threads';          
        }
	// override buildLink when thread id is within the forum "directory forum" << take from options	


        
    }	
	
}