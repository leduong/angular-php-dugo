<?php
class XML {
	public static function from($o,$r='data',$x=NULL,$u='element',$d="<?xml version='1.0' encoding='utf-8'?>") {
		is_null($x)&&$x=simplexml_load_string("$d<$r/>");
		foreach((array) $o as $k => $v) {
			is_numeric($k)&&$k=$u;
			if(is_scalar($v))
				$x->addChild($k,h($v));
			else{
				$v=(array) $v;
				$n=array_diff_key($v,array_keys(array_keys($v)))?$x->addChild($k):$x;
				to_xml($v,$k,$n);
			}
		}
		return $x;
	}
}