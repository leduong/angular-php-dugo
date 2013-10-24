<?php
class Controller_Admin_Index extends Controller
{
	public function index()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('index','admin');
	}
	public static function checklogin(){
		$a = unserialize(cookie::get('auth')); $d = array('username' => $a['username']);
		return (is_array($a)&&($c = Model_User::count($a))&&($b = Model_Admin::count($d))&&($c==$b));
	}
	public function login()
	{
		$this->content = new View('login','admin');
		$this->content->error = NULL;
		$r = array(
			'username' => 'required|string|min_length[4]|max_length[64]',
			'password' => 'required|string|min_length[6]');
		$v = new Validation();
		if($v->run($r))
		{
			$a = array('username' => post('username'), 'password' => md5(post('password')));
			if (is_array($a)&&($c = Model_User::count($a))&&($c==1)){
				cookie::set('auth',serialize($a));
				redirect(HTTP_SERVER."/admin/");
				return;
			} else {$this->content->error = lang('login_false');}
		}
	}
	public function logout()
	{
		cookie::set('auth',NULL);
		redirect(HTTP_SERVER);
	}
}
