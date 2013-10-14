<?php

// Default controller
$config['index'] = 'welcome/index';
// Default 404 controller
$config['404'] = 'welcome/index';
// Enable debug mode?
$config['debug'] = 0;
// Current theme
$config['theme'] = 'nhadat';
// Load init file?
$config['init'] = 1;
// Path to log directory
$config['log_path'] = 'system/log/';
// Default language file
$config['language'] = 'vi';

// Disabled modules
$config['disabled_modules'] = array('unittest');

/**
 * URL Routing
 *
 * Regex can also be used to define routes
 */
$config['routes'] = array(
	'contact' => 'contact/index',
	'sitemap' => 'sitemap/index',
	'tag\/' => 'tag/index',
	//'page/name' => 'error/404' // Or hide pages
);

/**
 * System Events
 */
$config['events'] = array(
	//'post_controller' => 'Class::method',
);

/**
 * Cookie Handling
 *
 * To insure your cookies are secure, please choose a long, random key!
 * @link http://php.net/setcookie
 */
$config['cookie'] = array(
	'key' => 'key',
	'expires' => time()+(60*60*24*365), // 365 x24 hour cookie
	'path' => '/',
	'domain' => '',
	'secure' => '',
	'httponly' => '',
);

/**
 * Database
 */
$config['database'] = array(
	'sqlite' => array(
		'dns' => "sqlite:".ROOT_PATH."system/db/test.sqlite",
	),
	'mysql' => array(
		'dns' => "mysql:dbname=nhadat;host=localhost;port=3306",
		'username' => 'nhadat',
		'password' => 'nhadatnhadat',
		'params' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
	),
);
/**
 * API Keys and Secrets
 *
 * Insert you API keys and other secrets here.
 * Use for Akismet, ReCaptcha, Facebook, and more!
 */

//$config['-----_api_key'] = '...';

return $config;