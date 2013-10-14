<?php
// Default Time Zone
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Default Locale
#setlocale(LC_ALL,'en_US.utf-8');
// iconv encoding
//iconv_set_encoding("internal_encoding","UTF-8");
// multibyte encoding
//mb_internal_encoding('UTF-8');
// System Start Time
define('START_TIME',microtime(true));
// System Start Memory
define('START_MEMORY_USAGE',memory_get_usage());
// Extension of all PHP files
define('EXT','.php');
define('TPL','.html');
// Absolute path to the system folder
define('ROOT_PATH',realpath(dirname(__FILE__)).'/');
define('SP',ROOT_PATH);
define('APP',ROOT_PATH);
define('THEME',ROOT_PATH.'themes/');
define('UPLOAD',dirname(ROOT_PATH).'/uploads/');
define('IMAGE',ROOT_PATH.'images/');
//Is this an AJAX request?
define('AJAX_REQUEST',strtolower(server('HTTP_X_REQUESTED_WITH'))==='xmlhttprequest');
define('POST',($_SERVER['REQUEST_METHOD'] == "POST"));
define('GET',($_SERVER['REQUEST_METHOD'] == "GET"));
// What is the current domain?
define('DOMAIN',h(server('SERVER_NAME')?server('HTTP_HOST'):server('SERVER_NAME')));
define('HTTP_SERVER',(server('HTTPS')=='on'?'https://':'http://').DOMAIN);

function __autoload($class) {
	$cls = str_replace('_','/',mb_strtolower($class));
	if (file_exists(APP."class/$cls".EXT)){
		require(APP."class/$cls".EXT);
	}
	else{
		$path = (strpos($class,'_')===FALSE?'system/':'');
		require(ROOT_PATH.$path.$cls.EXT);
	}
}
function benchmark() {
	static $t,$m;
	$a=array((microtime(true)-$t),(memory_get_usage()-$m));
	$t=microtime(true);
	$m=memory_get_usage();
	return $a;
}
function registry($k,$v=null) {
	static $o;
	return (func_num_args()>1?$o[$k]=$v:(isset ($o[$k])?$o[$k]:NULL));
}
function message($type=NULL,$v=NULL) {
	static $m=array();
	$h='';
	if($v)
		$m[$type][]=$v;
	elseif($type) {
		if(isset ($m[$type]))
			foreach($m[$type] as $v)
				$h.="<div class=\"$type\">$v</div>";
	}
	else
		foreach($m as $t => $d)
			foreach($d as $v)
				$h.="<div class=\"$t\">$v</div>";
	return $h;
}
function event($k,$v=NULL,$callback=NULL) {
	static $e;
	if($callback!==NULL)
		if($callback)
			$e[$k][]=$callback;
		else
			unset ($e[$k]);
	elseif(isset ($e[$k]))
		foreach($e[$k] as $f)
			$v=call_user_func($f,$v);
	return $v;
}
function config($k,$m='system') {
	static $c;
	$c[$m]=empty($c[$m])?require (ROOT_PATH.($m!='system'?"$m/":'').'config'.EXT):$c[$m];
	return ($k?$c[$m][$k]:$c[$m]);
}
function url($k=NULL,$d=NULL) {
	static $s;
	if(!$s) {
		foreach(array('REQUEST_URI','PATH_INFO','ORIG_PATH_INFO') as $v) {
			preg_match('/^\/[\w\-~\/\.+%]{1,600}/',server($v),$p);
			if(!empty ($p)) {
				$s=explode('/',trim($p[0],'/'));
				break;
			}
		}
	}
	if($s)
		return ($k!==NULL?(isset ($s[$k])?$s[$k]:$d):implode('/',$s));
}
function dump() {
	$s='';
	foreach(func_get_args() as $v) {
		$s.='<pre>'.h($v===NULL?'NULL':(is_scalar($v)?$v:print_r($v,1)))."</pre>\n";
	}
	return $s;
}
function v(&$v,$d=NULL) {
	return isset ($v)?$v:$d;
}
function input(){
	return json_decode(file_get_contents("php://input"));
}
function post($k,$d=NULL,$s=FALSE) {
	if(isset ($_POST[$k]))
		return $s?str($_POST[$k],$d):$_POST[$k];
	return $d;
}
function get($k,$d=NULL,$s=FALSE) {
	if(isset ($_GET[$k]))
		return $s?str($_GET[$k],$d):$_GET[$k];
	return $d;
}
function server($k,$d=NULL) {
	return isset ($_SERVER[$k])?$_SERVER[$k]:$d;
}
function session($k,$d=NULL) {
	return isset ($_SESSION[$k])?$_SESSION[$k]:$d;
}
function token() {
	return md5(str_shuffle(chr(mt_rand(32,126)).uniqid().microtime(TRUE)));
}
function log_message($m) {
	if(!$fp=@ fopen(ROOT_PATH.config('log_path').date('Y-m-d').'.log','a'))
		return 0;
	$m=date('H:i:s ').h(server('REMOTE_ADDR'))." $m\n";
	flock($fp,LOCK_EX);
	fwrite($fp,$m);
	flock($fp,LOCK_UN);
	fclose($fp);
	return 1;
}
function redirect($u='',$c=302,$m='location') {
	header($m=='refresh'?"Refresh:5;url=$u":"Location: $u",TRUE,$c);
}
function int($int,$min=NULL,$max=NULL) {
	$i=is_numeric($int)?(int) $int:$min;
	if($min!==NULL&&$i<$min)
		$i=$min;
	if($max!==NULL&&$i>$max)
		$i=$max;
	return $i;
}
function str($str,$default='') {return (is_scalar($str)?(string) $str:$default);}
function encode($string,$to='UTF-8',$from='UTF-8') {
	return $to==='UTF-8'&&is_ascii($string)?$string:@ iconv($from,$to.'//TRANSLIT//IGNORE',$string);
}
function is_ascii($string) {
	return !preg_match('/[^\x00-\x7F]/S',$string);
}
function base64_url_encode($string=NULL) {
	return strtr(base64_encode($string),'+/=','-_~');
}
function base64_url_decode($string=NULL) {
	return base64_decode(strtr($string,'-_~','+/='));
}
function h($data) {
	return htmlspecialchars($data,ENT_QUOTES,'utf-8');
}

function cleanhtml($data){
	$data = preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $data);
	$data = preg_replace('/\<img(.*?) src=\"(.*?)\" (.*?)\>/is', '[img]$2[/img]', $data);
	$data = strip_tags($data,'<b><u><i><p><div><br><img>');
	$data = preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/", '', $data);//emptytag
	$data = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $data);
	$data = str_replace(array('<div>', '</div>','&nbsp;'), array('<p>', '</p>',' '), $data);
	$data = preg_replace('/\[img\](.*?)\[\/img\]/is', '<p style="text-align: center;"><img src="$1" alt="" /></p>', $data);
	$data = preg_replace('~>\s+<~', '><', $data);
	return $data;
}

function bad_bot($ip,$key,$threat_level=20,$max_age=30) {
	if($ip=='127.0.0.1') return;
	$ip=implode('.',array_reverse(explode('.',$ip)));
	if($ip=gethostbyname("$key.$ip.dnsbl.httpbl.org")) {
		$ip=explode('.',$ip);
		return $ip[0]==127&&$ip[3]&&$ip[2]>=$threat_level&&$ip[1]<=$max_age;
	}
}
function site_url($uri=NULL) {
	return (strpos($uri,'://')===FALSE?DOMAIN.'/':'').$uri;
}
function theme_url($uri=NULL) {
	return trim(ltrim(THEME,ROOT_PATH).config('theme').'/'.$uri,'/');
}
function lang($k,$m='system') { return lang::get($k,$m);}
function T($k,$m='system') {echo lang::get($k,$m);}
class Controller {
	public $template = 'layout';
	public $appsite = array();
	public $intro = FALSE;
	public function __construct() {
		$this->appsite = array();
		if($s = new Model_Settings(1)) $this->appsite = (array)$s->d;
		if(isset($_COOKIE['auth'])&&config('debug')) {
			set_error_handler(array('error','handler'));
			register_shutdown_function(array('error','fatal'));
			set_exception_handler(array('error','exception'));
		}
	}
	public function show_404() {
		headers_sent()||header('HTTP/1.0 404 Page Not Found');
		$this->content = new View('404');
		$this->content->appsite = $this->appsite;
	}
	public function render() {
		headers_sent()||header('Content-Type: text/html; charset=utf-8');
		$v=new View($this->template);
		$v->set((array) $this);
		print $v;
		$v=0;
		//if(isset($_COOKIE['auth'])&&config('debug')) print new View('debug','system');
		if(config('debug')) print new View('debug','system');
	}
}

class View {
	public function __construct($f,$m='') {
		$m = (isset($_COOKIE['auth'])||isset($_GET['admin']))?'admin':$m;
		$m = ($m)?$m:config('theme');
		if(file_exists(THEME.$m."/".$f.EXT)) {
	      $this->__f=THEME.$m."/".$f.EXT;
	    }
	    elseif(file_exists(THEME."/system/".$f.EXT)){
	      $this->__f=THEME."/system/".$f.EXT;
	    }
		else {
	      $this->__f=THEME.$m."/html/".$f.TPL;
	    }
	}
	public function set($a) {
		foreach($a as $k => $v)$this->$k=$v;
	}
	public function __toString() {
		ob_start();
		extract((array) $this);
		require $__f;
		return ob_get_clean();
	}
}