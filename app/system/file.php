<?php
class file {
		public $allowed_files='gif|jpg|jpeg|png|txt|zip|rar|tar|gz|mov|flv|mpg|mpeg|mp4|wmv|avi|mp3|wav|ogg';
		static function upload($f,$d,$o=FALSE,$s=FALSE) {
			if(self::error($f) OR !extract(self::parse_filename($f['name'])) OR !$name||($s&&$f['size']>$s))
				return 0;
			dir::usable($d);
			$n=$o?"$name.$ext":self::unique_filename($d,$name,$ext);
			if(self::move($f,$d.$n))
				return $n;
		}
		function images($x) {
			$r = array();
			if(is_dir($x)) {
				$h=opendir($x);while($f=readdir($h)) if(is_file("$x/$f")&&self::is_image($f))$r[]=$f;closedir($h);
			}
			return usort($r,"strcasecmp");
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
			return in_array($ext,explode('|',self::$allowed_files));
		}
		static function unique_filename($d,$f,$e) {$x=1;while(file_exists("$d$f-$x.$e")) {$x++;}return "$f-$x.$e";}
		static function move($f,$d) {return move_uploaded_file($f['tmp_name'],$d);}
        static function file_size($f) {$u=array(' B',' KB',' MB',' GB',' TB');$s=is_file($f)?filesize($f):0;for ($i=0; $s>1024; $i++)$s/=1024;return round($s,2).$u[$i];}
		static function ext($f) {$p=pathinfo($f);return strtolower($p['extension']);}
		static function is_image($f){return in_array(self::ext($f),array("jpg","jpeg","gif","bmp","png"));}
		static function safe_upload($f){return in_array(self::ext($f),array("gif","jpg","jpeg","png","pdf","txt","doc","xls","ppt","rar","rtf","zip","7z","swf","mov","rm","wmv","wma","mp3"));}
}