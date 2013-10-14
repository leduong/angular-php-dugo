<?php
class cURL {
	public static function delete($url,array $params=array(),array $options=array()) {
		return self::request($url,$params,($options+array(CURLOPT_CUSTOMREQUEST => 'DELETE')));
	}
	public static function get($url,array $params=array(),array $options=array()) {
		if($params) {
			$url.=((stripos($url,'?')!==false)?'&':'?').http_build_query($params,'','&');
		}
		return self::request($url,array(),$options);
	}
	public static function post($url,array $params=array(),array $options=array()) {
		return self::request($url,$params,($options+array(CURLOPT_POST => 1)));
	}
	protected static function request($url,array $params=array(),array $options=array()) {
		$ch=curl_init($url);
		self::setopt($ch,$params,$options);
		$o=new stdClass;
		$o->response=curl_exec($ch);
		$o->error_code=curl_errno($ch);
		$o->error=curl_error($ch);
		$o->info=curl_getinfo($ch);
		curl_close($ch);
		return $o;
	}
	protected static function setopt($ch,array $params=array(),array $options=array()) {
		curl_setopt_array($ch,($options+array(45 => 1, 42 => 0, 52 => 1, 58 => 1, 78 => 120, 13 => 15, 19913 => 1, 10018 => "Mozilla/5.0 (Windows NT 6.1; rv:5.0.1) Gecko/20100101 Firefox/5.0.1", 10015 => http_build_query($params,'','&'))));
	}
	public static function headers(array $headers=array()) {
		$h=array();
		foreach($headers as $k => $v)
			$h[]="$k: $v";
		return $h;
	}
}