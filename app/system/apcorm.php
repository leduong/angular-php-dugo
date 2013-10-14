<?php
class APCORM extends ORM {
	public static function cache_set($k,$v) {if(function_exists('apc_store')) apc_store(DOMAIN.LANGUAGE.$k,$v,static::$cache);}
	public static function cache_get($k) {if(function_exists('cache_get'))return apc_fetch(DOMAIN.LANGUAGE.$k);}
	public static function cache_delete($k) {if(function_exists('apc_delete'))return apc_delete(DOMAIN.LANGUAGE.$k);}
	public static function cache_exists($k) {if(function_exists('apc_exists'))return apc_exists(DOMAIN.LANGUAGE.$k);}
}