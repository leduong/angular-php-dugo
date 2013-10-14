<?php
class dir {
	static function load($d,$r=FALSE) {
		$i=new RecursiveDirectoryIterator($d);
		return ($r?new RecursiveIteratorIterator($i,RecursiveIteratorIterator::SELF_FIRST):$i);
	}
	static function contents($d,$r=FALSE,$only=FALSE) {
		$d=self::load($d,$r);
		if(!$only)
			return $d;
		$only='is'.$only;
		$r=array();
		foreach($d as $f)
			if($f->$only())
				$r[]=$f;
			return $r;
	}
	static function usable($d,$chmod='0777') {
		if(!is_dir($d)&&!mkdir($d,$chmod,TRUE))
			return FALSE;
		if(!is_writable($d)&&!chmod($d,$chmod))
			return FALSE;
		return TRUE;
	}
}