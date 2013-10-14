<?php
class Cipher {
	public static $base='abcdefghijklmnopqrstuvwxzyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_~';
	public static function encrypt($text,$key,$algo=MCRYPT_RIJNDAEL_256,$mode=MCRYPT_MODE_CBC) {
		$text=mcrypt_encrypt($algo,hash('sha256',$key,TRUE),$text,$mode,$iv=mcrypt_create_iv(mcrypt_get_iv_size($algo,$mode),MCRYPT_RAND)).$iv;
		return hash('sha256',$key.$text).$text;
	}
	public static function decrypt($text,$key,$algo=MCRYPT_RIJNDAEL_256,$mode=MCRYPT_MODE_CBC) {
		$h=substr($text,0,64);
		$t=substr($text,64);
		if(hash('sha256',$key.$t)!=$h)
			return;
		$iv=substr($t,-mcrypt_get_iv_size($algo,$mode));
		return rtrim(mcrypt_decrypt($algo,hash('sha256',$key,TRUE),substr($t,0,-strlen($iv)),$mode,$iv),"\x0");
	}
	public static function key_from_id($id) {
		$k='';
		while($id>64) {
			$k=self::$base[fmod($id,65)].$k;
			$id=floor($id/65);
		}
		return self::$base{$id}.$k;
	}
	public static function id_from_key($key) {
		$id=0;
		$key=str_split($key);
		$c=count($key);
		foreach($key as $k => $v)
			$id+=pow(65,($c-$k-1))*strpos(self::$base,$v);
		return $id;
	}
}