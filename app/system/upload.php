<?php
class Upload {
	static function file($f,$d,$o=FALSE,$s=FALSE) {
		if(self::error($f) OR !extract(self::parse_filename($f['name'])) OR !self::allowed_file($ext) OR !$name||($s&&$f['size']>$s))
			return 0;
		dir::usable($d);
		$n=$o?"$name.$ext":self::unique_filename($d,$name,$ext);
		if(self::move($f,$d.$n))
			return $d.$n;
	}
	static function error($f) {
		if(!isset ($f['tmp_name'],$f['name'],$f['error'],$f['size']) OR $f['error']!=UPLOAD_ERR_OK)
			return TRUE;
	}
	static function parse_filename($f) {
		$p=pathinfo($f);
		return ((isset ($p['filename'],$p['extension'])&&$n=string::sanitize_filename($p['filename']))?array('name' => $n,'ext' => strtolower($p['extension'])):array('name' => '','ext' => ''));
	}
	static function allowed_file($ext) {
		return in_array($ext,explode('|','gif|jpg|jpeg|png'));
	}
	static function unique_filename($d,$f,$e) {
		$i=1;
		if (!file_exists("$d$f.$e")) {return "$f.$e";}
		else while(file_exists("$d$f-$i.$e")) {$i++;}return "$f-$i.$e";
	}
	static function move($f,$d) {
		return move_uploaded_file($f['tmp_name'],$d);
	}
}
?>