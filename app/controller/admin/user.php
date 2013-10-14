<?php
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
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_User::count();
		
		$this->content = new View('user');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;
		
		$pagination = new Pagination($total,HTTP_SERVER."/admin/user/lists/page/[[page]].html",$page,$limit, true);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination'
		);
		$this->content->users = Model_User::fetch(NULL,$limit,$offset,array('username' => 'ASC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('user_create');
		$this->content->message = NULL;
		$rules = array(
		'email' => 'required|valid_email|max_length[128]');
		$fields = array(
			'username' => array('required' => '*', 'div' => array('class' => 'control-group')),
			'email' => array('required' => '*', 'description' => lang('required_valid_email'), 'div' => array('class' => 'control-group')),
			'password' => array('required' => '*', 'type' => 'password', 'div' => array('class' => 'control-group')),
			'first_name' => array('required' => '*', 'div' => array('class' => 'control-group')),
			'last_name' => array('required' => '*', 'div' => array('class' => 'control-group')),
			'phone' => array('div' => array('class' => 'control-group')),
			'website' => array('div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('register'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		
		$validation = new Validation();
		
		if($validation->run($rules)){
			$find = array('username' => post('email'));
			$find = array('email' => post('email'));
			$count = Model_User::count($find);
			if($count>0){
				$this->content->message = lang('already_registered');
			}else{
				$u = new Model_User();
				$u->username = string::slug(post('username'));
				$u->email = post('email');
				$u->password = md5(post('password'));
				$u->first_name = post('first_name');
				$u->last_name = post('last_name');
				$u->phone = post('phone');
				$u->website = post('website');
		
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
		$rules = array(
			'email' => 'required|valid_email|max_length[128]'
		);
		
		$validation = new Validation();

		if($validation->run($rules)){
			$u = new Model_User(post('key'));
			$u->username = string::slug(post('username'));
			$u->email = post('email');
			if (post('password')) $u->password = md5(post('password'));
			$u->first_name = post('first_name');
			$u->last_name = post('last_name');
			$u->phone = post('phone');
			$u->website = post('website');
			
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
			'key' => array('type' => 'hidden', 'value' => $c->idu, 'div' => array('class' => 'control-group')),
			'username' => array('required' => '*', 'value' => $c->username, 'div' => array('class' => 'control-group')),
			'email' => array('required' => '*', 'value' => $c->email, 'div' => array('class' => 'control-group')),
			'password' => array('type' => 'password', 'description' => lang('blank_is_no_change'), 'div' => array('class' => 'control-group')),
			'first_name' => array('required' => '*', 'value' => $c->first_name, 'div' => array('class' => 'control-group')),
			'last_name' => array('required' => '*', 'value' => $c->last_name, 'div' => array('class' => 'control-group')),
			'phone' => array('value' => $c->phone, 'div' => array('class' => 'control-group')),
			'website' => array('value' => $c->website, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
			
		$form = new Form($validation, array('id' => 'register', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}