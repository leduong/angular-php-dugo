<?php
class Error {
	public static $found=FALSE;
	public static function header() {
		headers_sent() OR header('HTTP/1.0 500 Internal Server Error');
	}
	public static function fatal() {
		if($e=error_get_last())
			Error::exception(new ErrorException($e['message'],$e['type'],0,$e['file'],$e['line']));
	}
	public static function handler($c,$e,$f=0,$l=0) {
		if((error_reporting()&$c)===0)
			return TRUE;
		self::$found=1;
		self::header();
		$v=new View('error','system');
		$v->error=$e;
		$v->title=lang($c);
		print $v;
		log_message("[$c] $e [$f] ($l)");
		return TRUE;
	}
	public static function exception(Exception $e) {
		self::$found=1;
		$m="{$e->getMessage()} [{$e->getFile()}] ({$e->getLine()})";
		try {
			log_message($m);
			self::header();
			$v=new View('exception','system');
			$v->exception=$e;
			print $v;
		}
		catch (Exception $e) {
			print $m;
		}
		exit (1);
	}
	public static function source($f,$n,$p=5) {
		$l=array_slice(file($f),$n-$p-1,$p*2+1,1);
		$o='';
		foreach($l as $i => $r)
			$o.='<b>'.sprintf('%'.strlen($n+$p).'d',$i+1).'</b> '.($i+1==$n?'<em>'.h($r).'</em>':h($r));
		return $o;
	}
	public static function backtrace($o,$l=5) {
		$t=array_slice(debug_backtrace(),$o,$l);
		foreach($t as $i =>&$v) {
			if(!isset ($v['file'])) {
				unset ($t[$i]);
				continue;
			}
			$v['source']=self::source($v['file'],$v['line']);
		}
		return $t;
	}
}