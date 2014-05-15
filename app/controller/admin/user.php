<?php
function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

class Controller_Admin_User extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/user/lists');
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			if($s>1){
				$c = new Model_User($s);
				$c->delete();
			}
		}
		redirect(HTTP_SERVER.'/admin/user/lists');
	}
	public function verified()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$where  = array("disable" => '0', "verified" => "1");
		$total  = Model_User::count($where);
		$users = Model_User::fetch($where);
		$ar = array();
		$ar[] = array(
			"ID",
			"Name",
			"Email",
			"Phone",
			"Dia danh",
			"Rao dang",
			"Trao doi"
			);
		foreach ($users as $a) {
			$ar[] = array(
				$a->idu,
				$a->first_name . ' ' . $a->last_name,
				$a->email,
				$a->phone,
				Model_Group::count(array('by' => $a->idu)),
				Model_Messages::count(array('uid' => $a->idu, 'type' => 'realestate')),
				Model_Messages::count(array('uid' => $a->idu, 'type' => 'status'))
			);
		}

		$now = date("D, d M Y H:i:s");
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");

	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");

	    // disposition / encoding on response body
	    header("Content-Disposition: attachment;filename=verified.csv");
	    header("Content-Transfer-Encoding: binary");
	    echo array2csv($ar);
	    die();
	}
	public function unverified()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$where  = array("disable" => '0', "verified" => "0");
		$total  = Model_User::count($where);
		$users = Model_User::fetch($where);
		$ar = array();
		$ar[] = array(
			"ID",
			"Name",
			"Email",
			"Phone",
			"Dia danh",
			"Rao dang",
			"Trao doi"
			);
		foreach ($users as $a) {
			$ar[] = array(
				$a->idu,
				$a->first_name . ' ' . $a->last_name,
				$a->email,
				$a->phone,
				0,
				0,
				0
			);
		}

		$now = date("D, d M Y H:i:s");
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");

	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");

	    // disposition / encoding on response body
	    header("Content-Disposition: attachment;filename=unverified.csv");
	    header("Content-Transfer-Encoding: binary");
	    echo array2csv($ar);
	    die();
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$sort   = array('idu' => 'DESC');
		$where  = array("disable" => '0');
		$total  = Model_User::count($where);

		$this->content = new View('user');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination($total,HTTP_SERVER."/admin/user/lists/page/[[page]].html",$page,$limit, true);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination'
		);
		$this->content->users = Model_User::fetch($where,$limit,$offset,$sort);
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('user_create');
		$this->content->message = NULL;
		$rules = array();
		$fields = array(
			'username'   => array('div' => array('class' => 'control-group')),
			'email'      => array('div' => array('class' => 'control-group')),
			'password'   => array('div' => array('class' => 'control-group')),
			'first_name' => array('div' => array('class' => 'control-group')),
			'last_name'  => array('div' => array('class' => 'control-group')),
			'phone'      => array('div' => array('class' => 'control-group')),
			'website'    => array('div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('register'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$validation = new Validation();

		if($validation->run($rules)){
			$email = trim(strtolower(post('email')));
			$username = string::slug(post('username'));
			$phone = numify(post('phone'));
			$array = array();
			if ($email) $array[]    = "email = '$email'";
			if ($phone) $array[]    = "phone = '$phone'";
			if ($username) $array[] = "username = '$username'";
			$where = implode("OR", $array);
			$count = Model_User::count(array($where,"disable" => "0"));
			if($count>0){
				$this->content->message = lang('already_registered');
			}else{
				$u = new Model_User();
				$u->username   = $username;
				$u->email      = post('email');
				$u->password   = md5(post('password'));
				$u->first_name = post('first_name');
				$u->last_name  = post('last_name');
				$u->phone      = post('phone');
				$u->website    = post('website');
				$u->save();

				$this->content->message = lang('successfully_registered');
				$this->content->form = NULL;
				/*
				$message = sprintf(lang('mail_registered'), post('full_name'), post('email'), post('password'), DOMAIN);
				$mail = new Mail();
				$mail->setTo(post('email'));
				$mail->setFrom($this->appsite['email']);
				$mail->setSender(sprintf('Website %s', DOMAIN));
				$mail->setSubject(sprintf(lang('mail_registered_subject'), DOMAIN));
				$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));*/
				//$mail->send();
				unset($_POST);
				return;
			}
		}
		else
		{

		}
		$form = new Form($validation, array('id' => 'register', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('user_form');
		$this->content->message = NULL;

		$rules = array();
		$validation = new Validation();

		if($validation->run($rules)){
			$u             = new Model_User(post('key'));
			$u->email      = post('email');
			$u->phone      = post('phone');
			$u->website    = post('website');
			$u->username   = string::slug(post('username'));
			$u->last_name  = post('last_name');
			$u->first_name = post('first_name');
			if (!empty(post('password'))) {$u->password = md5(post('password'));};

			$u->save();

			$this->content->message = lang('successfully_update');
			$this->content->form = NULL;
			/*
			$message = sprintf(lang('mail_registered'), post('full_name'), post('email'), post('password'), DOMAIN);
			$mail = new Mail();
			$mail->setTo(post('email'));
			$mail->setFrom($this->appsite['email']);
			$mail->setSender(sprintf('Website %s', DOMAIN));
			$mail->setSubject(sprintf(lang('mail_registered_subject'), DOMAIN));
			$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));*/
			//$mail->send();
			unset($_POST);
			return;
		}
		$c = new Model_User(get('edit'));
		$fields = array(
			'key'        => array('type' => 'hidden', 'value' => $c->idu, 'div' => array('class' => 'control-group')),
			'username'   => array('value' => $c->username, 'div' => array('class' => 'control-group')),
			'email'      => array('value' => $c->email, 'div' => array('class' => 'control-group')),
			'password'   => array('type' => 'password', 'description' => lang('blank_is_no_change'), 'div' => array('class' => 'control-group')),
			'first_name' => array('value' => $c->first_name, 'div' => array('class' => 'control-group')),
			'last_name'  => array('value' => $c->last_name, 'div' => array('class' => 'control-group')),
			'phone'      => array('value' => $c->phone, 'div' => array('class' => 'control-group')),
			'website'    => array('value' => $c->website, 'div' => array('class' => 'control-group')),
			'submit'     => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'register', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}

	public function ajax(){
		$sSearch = string::slug(get('sSearch'));
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = get('iDisplayLength');
		$offset = get('iDisplayStart');
		$sort   = array('idu' => 'DESC');
		$where  = array("idu LIKE '%$sSearch%' OR email LIKE '%$sSearch%' OR first_name LIKE '%$sSearch%' OR phone LIKE '%$sSearch%'", 'disable' => '0');
		$total  = Model_User::count($where);

		$array  = array();
		$output = array(
			"sEcho" => intval(get('sEcho')),
			"iTotalRecords" => $total,
			"iTotalDisplayRecords" => $total,
			"aaData" => array()
		);
		$users = Model_User::fetch($where, $limit, $offset, $sort);
		if ($users) foreach ($users as $a) {
			$input = '<input type="checkbox" name="selected[]" class="checkboxes" value="'.$a->idu.'" />';
			$onoff = ($a->verified)?'<b>True</b>':'False';
			$action = '<a title="Edit" href="/admin/user/edit/'.$a->idu.'"><i class="icon-pencil"></i></a>';
			$array[] = array(
				$input,
				$a->idu,
				$a->first_name,
				$a->email,
				$a->phone,
				$onoff,
				$action
				);
		}
		Response::json(array(
						"sEcho"                => intval(get('sEcho')),
						"iTotalRecords"        => $total,
						"iTotalDisplayRecords" => $total,
						"aaData"               => $array));
		exit;
	}
}