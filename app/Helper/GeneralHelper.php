<?php
namespace App\Helper;
class GeneralHelper{
	static function metaGeneral($metadescription,$keywords,$cache,$lokasi,$area,$title){
		return '
		<title>'.$title.'</title>
		<meta itemprop="name" content="KomuKu - E2C Forum, Elegant Forum, Enjoyable Forum, Classy Forum | KOMUKU">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" >
		<meta charset="utf-8"/>
		<meta http-equiv="Pragma" content="'.$cache.'" />
		<meta http-equiv="Expires" content="-1" />
		<meta http-equiv="Cache-Control" content="'.$cache.'" />
		<meta name="keywords" content="'.$keywords.'" />
		<meta name="description" content="'.$metadescription.'">
		<meta name="author" property="author" content="KomuKu" />
		<meta http-equiv="content-language" content="id, en">
		<meta name="webcrawlers" content="all" />
		<meta name="rating" content="general" />
		<meta name="spiders" content="all" />
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="copyright" CONTENT="&copy; 2017 - '.date('Y').' KomuKu Family All Right Reserved">
		<meta content="'.$lokasi.'" name="'.$area.'"/>
		<meta content="Forum" name="KomuKu - KomunitasKu"/>
		';
	}
	static function metaFacebook($url,$type,$titile,$description,$image){
		return '
		<meta property="og:url"                content="'.$url.'" />
		<meta property="og:type"               content="'.$type.'" />
		<meta property="og:title"              content="'.$titile.'" />
		<meta property="og:description"        content="'.$description.'" />
		<meta property="og:image"              content="'.$image.'" />
		';
	}
	static function makeSlug($text){
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
		if (empty($text)) {
			return 'n-a';
		}
	  return $text;
	}
	static function cryptoJsAesDecrypt($passphrase, $jsonString){
		$jsondata = json_decode($jsonString, true);
		try {
			$salt = hex2bin($jsondata["s"]);
			$iv  = hex2bin($jsondata["iv"]);
		} catch(Exception $e) { return null; }
		$ct = base64_decode($jsondata["ct"]);
		$concatedPassphrase = $passphrase.$salt;
		$md5 = array();
		$md5[0] = md5($concatedPassphrase, true);
		$result = $md5[0];
		for ($i = 1; $i < 3; $i++) {
			$md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
			$result .= $md5[$i];
		}
		$key = substr($result, 0, 32);
		$data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
		return json_decode($data, true);
	}
	static function cryptoJsAesEncrypt($passphrase, $value){
		$salt = openssl_random_pseudo_bytes(8);
		$salted = '';
		$dx = '';
		while (strlen($salted) < 48) {
			$dx = md5($dx.$passphrase.$salt, true);
			$salted .= $dx;
		}
		$key = substr($salted, 0, 32);
		$iv  = substr($salted, 32,16);
		$encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
		$data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
		return json_encode($data);
	}
	
	static function joincsssemua($array_files, $destination_dir, $dest_file_name,$apakahdia){
		$findcss = array('!/\*.*?\*/!s','/\n\s*\n/','/[\n\r \t]/','/ +/','/ ?([,:;{}]) ?/','/;}/');
		$replcss = array('',"\n",' ',' ','$1','}');
		$findjs = array('!/\*.*?\*/!s',"/\n\s+/","/\n(\s*\n)+/","!\n//.*?\n!s","/\n\}(.+?)\n/","/;\n/");
		$replacejs = array('',"\n","\n","\n","}\\1\n",';');
		if(!is_file($destination_dir . $dest_file_name)){
			$content = "";
			foreach ($array_files as $file){
				$content .= file_get_contents($file);
			}
			($apakahdia == "css") ? $contentminify = preg_replace($findcss, $replcss, $content) : $contentminify = preg_replace($findjs, $replacejs, $content);
			$new_file = fopen($destination_dir . $dest_file_name, "w" );
			fwrite($new_file , $contentminify);
			fclose($new_file);
			($apakahdia == "css") ? $data = '<link href="'. $destination_dir . $dest_file_name.'" rel="stylesheet" type="text/css">' : $data = '<script src="'. $destination_dir . $dest_file_name.'"></script>';
			return $data;
		}else{
			($apakahdia == "css") ? $data = '<link href="'. $destination_dir . $dest_file_name.'" rel="stylesheet" type="text/css">' : $data = '<script src="'. $destination_dir . $dest_file_name.'"></script>';
			return $data;
		}
	}
	static function mb_stripos_all($haystack, $needle) {
		$s = 0;
		$i = 0;
		while(is_integer($i)) {
		$i = mb_stripos($haystack, $needle, $s);
			if(is_integer($i)) {
			  $aStrPos[] = $i;
			  $s = $i + mb_strlen($needle);
			}
		}
		if(isset($aStrPos)) {
			return $aStrPos;
		} else {
			return false;
		}
	}

	static function apply_highlight($a_json, $parts) {
		$p = count($parts);
		$rows = count($a_json);
		for($row = 0; $row < $rows; $row++) {
			$patterns = array();
			$replacements = array();
			foreach($parts as $value) {
				preg_match_all('/'.$value.'(?![^<]*>)/i', $a_json[$row]["label"], $matches);
				foreach ($matches[0] as $value2) {
					$patterns[] = '/'.$value2.'(?![^<]*>)/';
					$replacements[] = '<span class="hl_results">' . $value2 . '</span>';
				}
			}
		$a_json[$row]["label"] =  preg_replace($patterns, $replacements, $a_json[$row]["label"]);
		}
	return $a_json;
	}
	static function kirimemail($template, $title, $subject, $emailCS, $emailMember, $dataEmail){
		\Mail::send($template, $dataEmail , function ($message) use($emailCS, $emailMember, $subject)
		{
			$message->from($emailCS, 'HumBer - KomuKu');
			$message->to($emailMember)->subject($subject);
		});
	}
	static function tanggal_indo($tanggal, $cetak_hari = false){
	$hari = array ( 1 =>    'Senin',
				'Selasa',
				'Rabu',
				'Kamis',
				'Jumat',
				'Sabtu',
				'Minggu'
			);
			
	$bulan = array (1 =>   'Januari',
				'Februari',
				'Maret',
				'April',
				'Mei',
				'Juni',
				'Juli',
				'Agustus',
				'September',
				'Oktober',
				'November',
				'Desember'
			);
	$split 	  = explode('-', $tanggal);
	$tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
	if ($cetak_hari) {
		$num = date('N', strtotime($tanggal));
		return $hari[$num] . ', ' . $tgl_indo;
	}
	return $tgl_indo;
	}
}