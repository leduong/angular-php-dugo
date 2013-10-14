<?php
/*
CREATE TABLE `language` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(255) NOT NULL,
  `en` varchar(255) NOT NULL,
  `vi` varchar(255),
  `zh` varchar(255),
  `ko` varchar(255),
  `ja` varchar(255),
   UNIQUE (name)
)

MySQL


CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `en` varchar(255) DEFAULT NULL,
  `vi` varchar(255) DEFAULT NULL,
  `zh` varchar(255) DEFAULT NULL,
  `ko` varchar(255) DEFAULT NULL,
  `ja` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
class Model_Language extends APCORM{public static $t = 'language';}
class Lang {
	protected static $l;

	public static
	function load($c=NULL) {
		$c = ($c&&is_file(SP."system/lang/$c".EXT))?$c:self::choose();
		if(is_file(SP."system/lang/$c".EXT)) self::$l = require (SP."system/lang/$c".EXT);
		if ($langs = Model_Language::fetch()) foreach ($langs as $lang) {
			$lang = $lang->to_array();
			if(isset($lang)) self::$l[$lang['name']] = ($lang[$c])?$lang[$c]:$lang['en'];
		}
	}

	public static
	function accepted() {
		static $a;
		if($a) return $a;
		foreach(explode(',',server('HTTP_ACCEPT_LANGUAGE')) as $v) $a[]=substr($v,0,2);
		return $a;
	}

	public static
	function get($k,$m='system') {
		isset (self::$l) OR self::load();
		return (isset(self::$l[$k]))?self::$l[$k]:self::set($k);
	}

	public static
	function set($k) {
		if ($check = Model_Language::fetch(array('name' => $k),1)){
			$lang = $check[0];
		}else{
			$lang = new Model_Language();
			$lang->name = $k;
			$lang->en = ucwords(str_replace('_',' ',$k));
			$lang->save();
		}
		return self::$l[$k] = $lang->en;
	}

	public static
	function choose($m='system') {
		$p=SP.$m.'/lang/';
		if(isset($_COOKIE['lang'])&&($c=$_COOKIE['lang'])&&is_file($p.$c.EXT)) return $c;
		foreach(self::accepted() as $c) if(is_file($p.$c.EXT)) return $c;
		return (isset($_COOKIE['lang']))?$_COOKIE['lang']:config('language');
	}
}