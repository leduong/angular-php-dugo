<?php
class Controller_Api extends Controller
{
	public function index(){
		if(AJAX_REQUEST){
			if(POST){

			}
		}
		exit;
	}
	public function follow(){
		if(AJAX_REQUEST){
			$city = $group = $topic = $agent = array();
			if ($u = unserialize(cookie::get('user'))){
				$tags = Model_Follows::fetch(array('by' => $u['idu']));
			} else {
				if ($ar = Model_City::fetch(array(),3)) foreach ($ar as $a) $city[]  = $a->to_array();
				if ($ar = Model_Group::fetch(array(),3))foreach ($ar as $a) $group[] = $a->to_array();
				if ($ar = Model_Topic::fetch(array(),3))foreach ($ar as $a) $topic[] = $a->to_array();
				if ($ar = Model_User::fetch(array(),3)) foreach ($ar as $a){
					$user = array();
					foreach($a->to_array() as $k => $v) if ($k != 'password') $user[$k] = $v;
					$agent[] = $user;
				}
			}
			Response::json(array(
				'city' => $city,
				'group' => $group,
				'topic' => $topic,
				'agent' => $agent)
			);
		}
		exit;
	}
	public function stats(){
		if(AJAX_REQUEST){
			$users = Model_User::count(array('agent' => 1));
			$messages = Model_Messages::count();
			Response::json(array('stats' => array('users' => $users, 'messages' => $messages)));
		}
		exit;
	}
	public static function check($idu=0){
		$c = unserialize(cookie::get('user'));
		return (controller_admin_index::checklogin()||(is_array($c)&&($idu == $c['idu'])));
	}
}