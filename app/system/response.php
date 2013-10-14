<?php
class Response {
  static private $headers=array();
  static private $output;
  static private $level=0;
  static private $http_status_codes = array(
	100 => "Continue",
	101 => "Switching Protocols",
	102 => "Processing",
	200 => "OK",
	201 => "Created",
	202 => "Accepted",
	203 => "Non-Authoritative Information",
	204 => "No Content",
	205 => "Reset Content",
	206 => "Partial Content",
	207 => "Multi-Status",
	300 => "Multiple Choices",
	301 => "Moved Permanently",
	302 => "Found",
	303 => "See Other",
	304 => "Not Modified",
	305 => "Use Proxy",
	306 => "(Unused)",
	307 => "Temporary Redirect",
	308 => "Permanent Redirect",
	400 => "Bad Request",
	401 => "Unauthorized",
	402 => "Payment Required",
	403 => "Forbidden",
	404 => "Not Found",
	405 => "Method Not Allowed",
	406 => "Not Acceptable",
	407 => "Proxy Authentication Required",
	408 => "Request Timeout",
	409 => "Conflict",
	410 => "Gone",
	411 => "Length Required",
	412 => "Precondition Failed",
	413 => "Request Entity Too Large",
	414 => "Request-URI Too Long",
	415 => "Unsupported Media Type",
	416 => "Requested Range Not Satisfiable",
	417 => "Expectation Failed",
	418 => "I'm a teapot",
	419 => "Authentication Timeout",
	420 => "Enhance Your Calm",
	422 => "Unprocessable Entity",
	423 => "Locked",
	424 => "Failed Dependency",
	424 => "Method Failure",
	425 => "Unordered Collection",
	426 => "Upgrade Required",
	428 => "Precondition Required",
	429 => "Too Many Requests",
	431 => "Request Header Fields Too Large",
	444 => "No Response",
	449 => "Retry With",
	450 => "Blocked by Windows Parental Controls",
	451 => "Unavailable For Legal Reasons",
	494 => "Request Header Too Large",
	495 => "Cert Error",
	496 => "No Cert",
	497 => "HTTP to HTTPS",
	499 => "Client Closed Request",
	500 => "Internal Server Error",
	501 => "Not Implemented",
	502 => "Bad Gateway",
	503 => "Service Unavailable",
	504 => "Gateway Timeout",
	505 => "HTTP Version Not Supported",
	506 => "Variant Also Negotiates",
	507 => "Insufficient Storage",
	508 => "Loop Detected",
	509 => "Bandwidth Limit Exceeded",
	510 => "Not Extended",
	511 => "Network Authentication Required",
	598 => "Network read timeout error",
	599 => "Network connect timeout error"
  );

  static public function addHeader($header) {
  	if ((int)$header > 0){
  		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		$header = $protocol . ' ' . $header . ' ' . self::$http_status_codes[$header];
  	}
	self::$headers[]=$header;
  }

  static public function redirect($url) {
	header('Location: '.$url);
	exit;
  }

  static public function json($data,$header=200){
	self::$output = json_encode($data);
	if ($header!=200) self::addHeader($header);
	self::$headers[]="Content-Type: application/json; charset=utf-8;";
	self::out();
  }

  public function setOutput($output,$level=0) {
	$this->output=$output;
	$this->level=$level;
  }

  static private function compress($data,$level=0) {
	if(isset ($_SERVER['HTTP_ACCEPT_ENCODING'])&&(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')!==false)) {
	  $encoding='gzip';
	}
	if(isset ($_SERVER['HTTP_ACCEPT_ENCODING'])&&(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'x-gzip')!==false)) {
	  $encoding='x-gzip';
	}
	if(!isset ($encoding)) {
	  return $data;
	}
	if(!extension_loaded('zlib')||ini_get('zlib.output_compression')) {
	  return $data;
	}
	if(headers_sent()) {
	  return $data;
	}
	if(connection_status()) {
	  return $data;
	}
	$this->addHeader('Content-Encoding: '.$encoding);
	return gzencode($data,(int) $level);
  }

  static public function out() {
	if(self::$level) {
	  $ouput=$this->compress(self::$output,self::$level);
	}
	else{
	  $ouput=self::$output;
	}
	if(!headers_sent()) {
	  foreach(self::$headers as $header) {
		header($header,true);
	  }
	}
	echo $ouput;
  }
}
?>