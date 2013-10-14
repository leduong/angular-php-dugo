<?php
require ('init.php');
session_start();
//Init Language
$language=(isset($_COOKIE['lang']))?$_COOKIE['lang']:config('language');
if (!is_file(SP."system/lang/$language".EXT)) $language = config('language');
define('LANGUAGE',$language);

// Init database
$sql = config('database');
// $sql['sqlite'] or $sql['mysql']
$db = new DB($sql['mysql']);
registry('db', $db);
ORM::$db = $db;

// Get the current URL (defaulting to the index)
$url=((url()?explode('/',str_replace('.html','',url())):array())+explode('/',config('index')));
if (count($url)>0){
	$i=0;
	foreach($url as $u) {
		$i++;
		if(preg_match("/^([a-z_])+$/i",$u)) if($i<count($url)) $_GET["$u"]=$url[$i];
	}
}

// Get the controller and page
list($controller,$module)=array_slice($url,0,2);
$params=array_slice($url,2);
// Routes allow custom URL
foreach(config('routes') as $regex => $path) {
	if(preg_match("/^$regex/",url())) {
		list($controller,$module)=explode('/',$path);
		$params=$url;
		break;
	}
}

// Register events
foreach(config('events') as $event => $class) {event($event,'',$class);}

// Disabled, non-word (unsafe), and missing controllers are not allowed
if($module&&is_file(APP.'controller/'.$controller.'/'.$module.'.php')){
	$ctrl='controller_'.$controller.'_'.$module;$method=get($module);
}
elseif(is_file(APP.'controller/'.$controller.'/index.php')){
	$method=get($controller);$ctrl='controller_'.$controller.'_index';
}
elseif(is_file(APP.'controller/'.$controller.'.php')){
	$method=get($controller);$ctrl='controller_'.$controller;
}
else{$ctrl='controller_'.str_replace('/','_',config('404'));}

if(in_array($module,config('disabled_modules')) OR preg_match('/\W/',$controller.$module)) {$ctrl='controller_'.str_replace('/','_',config('404'));}

$method = (isset($method)&&preg_match("/^([a-z_])+$/i",$method)&&(!in_array($method,array("sort","tag","page","keyword"))))?$method:'index';
$_GET['controller'] = $controller;
event('pre_controller');
// Load and run action
$ctrl = new $ctrl();
call_user_func_array(array($ctrl,$method),$params);
// Render output
$ctrl->render();
event('post_controller',$ctrl);
// End