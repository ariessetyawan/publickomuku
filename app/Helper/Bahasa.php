<?php

class Bahasa{
	public static function langOption(){
		$path = base_path().'/resources/lang';
		$lang = scandir($path);
		$t = array();
		foreach($lang as $isinya){
		if($isinya === '.' || $isinya === '..'){ continue; }
		if(is_dir($path.$isinya)){
			$fp = file_get_contents($path.$value.'/info.json');
			$fp = json_decode($fp,true);
			$t[] = $fp;
		}
		}
		return $t;
	}
}