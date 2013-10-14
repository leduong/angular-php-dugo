<?php
class cookie {
	public static function get($k,$c=NULL) {
		$c=$c?:config('cookie');
		if(isset ($_COOKIE[$k])&&($v=$_COOKIE[$k]))
			if($v=json_decode(Cipher::decrypt($v,$c['key'])))
				if($v[0]<$c['expires'])
					return is_scalar($v[1])?$v[1]:(array) $v[1];
	}
	public static function set($k,$v,$c=NULL) {
		extract($c?:config('cookie'));
		empty ($key)&&trigger_error(lang('cookie_no_key'));
		setcookie($k,($v?Cipher::encrypt(json_encode(array(time(),$v)),$key):''),$expires,$path,$domain,$secure,$httponly);
	}
}