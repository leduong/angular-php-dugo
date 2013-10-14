<?php
class Session {
	public static function start($name='session') {
		if(!empty ($_SESSION))
			return FALSE;
		$_SESSION=cookie::get($name);
		return TRUE;
	}
	public static function save($name='session') {
		return cookie::set($name,$_SESSION);
	}
	public static function destroy($name='session') {
		cookie::set($name,'');
		unset ($_COOKIE[$name],$_SESSION);
	}
	public static function token($token=NULL) {
		if(!empty ($_SESSION))
			return (func_num_args()?(!empty($_SESSION['token'])&&$token===$_SESSION['token']):($_SESSION['token']=token()));
	}
}