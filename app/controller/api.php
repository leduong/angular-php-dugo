<?php
class Controller_Api extends Controller
{
	public function index(){

	}

	public static function check($idu=0){
		$c = unserialize(cookie::get('user'));
		return (controller_admin_index::checklogin()||(is_array($c)&&($idu == $c['idu'])));
	}
}