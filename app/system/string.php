<?php
class string {
	public static function to_ascii($string) {
	  $pattern=array("a" => "á|à|ạ|ả|ã|Á|À|Ạ|Ả|Ã|ă|ắ|ằ|ặ|ẳ|ẵ|Ă|Ắ|Ằ|Ặ|Ẳ|Ẵ|â|ấ|ầ|ậ|ẩ|ẫ|Â|Ấ|Ầ|Ậ|Ẩ|Ẫ","o" => "ó|ò|ọ|ỏ|õ|Ó|Ò|Ọ|Ỏ|Õ|ô|ố|ồ|ộ|ổ|ỗ|Ô|Ố|Ồ|Ộ|Ổ|Ỗ|ơ|ớ|ờ|ợ|ở|ỡ|Ơ|Ớ|Ờ|Ợ|Ở|Ỡ","e" => "é|è|ẹ|ẻ|ẽ|É|È|Ẹ|Ẻ|Ẽ|ê|ế|ề|ệ|ể|ễ|Ê|Ế|Ề|Ệ|Ể|Ễ","u" => "ú|ù|ụ|ủ|ũ|Ú|Ù|Ụ|Ủ|Ũ|ư|ứ|ừ|ự|ử|ữ|Ư|Ứ|Ừ|Ự|Ử|Ữ","i" => "í|ì|ị|ỉ|ĩ|Í|Ì|Ị|Ỉ|Ĩ","y" => "ý|ỳ|ỵ|ỷ|ỹ|Ý|Ỳ|Ỵ|Ỷ|Ỹ","d" => "đ|Đ",);
	  while(list($key,$value)=each($pattern)) {
	    $string=preg_replace('/'.$value.'/i',$key,$string);
	  }
	  return $string;
	}
	public static function slug($string) {
		return self::sanitize_url(self::sanitize_url($string));
	}
	public static function sanitize($s,$spaces=TRUE) {
		$s=preg_replace(array('/[^\w\-\. ]+/u','/\s\s+/','/\.\.+/','/--+/','/__+/'),array(' ',' ','.','-','_'),$s);
		if(!$spaces)
			$s=preg_replace('/--+/','-',str_replace(' ','-',$s));
		return trim($s,'-._ ');
	}
	public static function sanitize_url($string) {
		return urlencode(mb_strtolower(self::sanitize(self::to_ascii($string),FALSE)));
	}
	public static function sanitize_filename($string) {
		return self::sanitize($string,FALSE);
	}
	public static function random_characters($length,$only_letters=FALSE) {
		$s='';
		for($i=0; $i<$length; $i++)
			$s.=($only_letters?chr(mt_rand(33,126)):chr(mt_rand(65,90)));
		return $s;
	}
	public static function prep_url($url='') {
		if($url=='http://' OR $url=='')
			return;
		if(mb_substr($url,0,7)!='http://'&&mb_substr($url,0,8)!='https://')
			$url="http://$url";
		return $url;
	}
	public static function split_text($text,$start='<code>',$end='</code>') {
		$t=explode($start,$text);
		$o[]=$t[0];
		$n=count($t);
		for($i=1; $i<$n;++$i) {
			$x=explode($end,$t[$i]);
			$i[]=$x[0];
			$o[]=$x[1];
		}
		return array($i,$o);
	}
	public static function split($text,$string='"',$escape='\\\\') {
		return preg_split("/(?:[^$escape])$string/u",$text);
	}
	public static function join($inside=NULL,$outside=NULL,$pre='"',$post='"') {
		if(empty ($inside)||empty ($outside))
			return $outside;
		$text='';
		$num_tokens=count($outside);
		for($i=0; $i<$num_tokens;++$i) {
			$text.=$outside[$i];
			if(isset ($inside[$i])) {
				$text.=$pre.$inside[$i].$post;
			}
		}
		return $text;
	}
}