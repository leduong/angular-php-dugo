<?php
final class Request {
  public $get=array();
  public $post=array();
  public $cookie=array();
  public $files=array();
  public $server=array();
  public $input;
  public $uri='';
  public function __construct() {
    $_GET=$this->clean($_GET);
    $_POST=$this->clean($_POST);
    $_COOKIE=$this->clean($_COOKIE);
    $_FILES=$this->clean($_FILES);
    $_SERVER=$this->clean($_SERVER);
    $this->get=$_GET;
    $this->post=$_POST;
    $this->cookie=$_COOKIE;
    $this->files=$_FILES;
    $this->server=$_SERVER;
    $this->input=json_decode(file_get_contents("php://input"));

    $path=parse_url(HTTP_SERVER);
    $path=($path['path']!='/')?$path['path']:'';
    $url=str_replace($path,'',str_replace('index.php','',$this->server['REQUEST_URI']));
    $this->uri=trim($url,'/');
    $i=0;
    $segments=explode('/',$this->uri);
    foreach($segments as $segment) {
      $i++;
      if(preg_match("/^([a-z_])+$/i",$segment)) {
        if($i<count($segments))
          $this->get[$segment]=$segments[$i];
      }
    }
  }
  public function clean($data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        unset ($data[$key]);
        $data[$this->clean($key)]=$this->clean($value);
      }
    }
    else{
      $data=htmlspecialchars($data,ENT_COMPAT,'UTF-8');
    }
    return $data;
  }
}