<?php

class KomuKu_NodesGrid_Listener_Proxy
{
	public static function load_class($class, array &$extend)
	{
		$extend[] = 'KomuKu_NodesGrid_'.$class;
	}
}

?>