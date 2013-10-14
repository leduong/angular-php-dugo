<?php
class Controller_Admin_Relations extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/relations/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Relations($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/relations/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=25;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Relations::count();
		
		$this->content = new View('relations');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/relations/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');
		
		$this->content->relations = Model_Relations::fetch(
			NULL,
			$limit,
			$offset,
			array('id' => 'DESC'));
			
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('relations');
		$this->content->message = NULL;

		$rules = array(
			'leader' => 'required|numeric',
			'subscriber' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Relations();
			$c->leader = post('leader');
			$c->subscriber = post('subscriber');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}
		
		$users = array('' => 'Choose');
		
		if ($us = Model_User::fetch())
			foreach($us as $u){
				$users[$u->idu] = $u->username;
			}
			
		$messages = array(''=>'Choose');
		
		if($ms = Model_Messages::fetch())
			foreach($ms as $m){
				$messages[$m->id] = $m->message;
			}

		$fields = array(
			'leader' => array('type' => 'select', 'options' => $users, 'div' => array('class' => 'control-group')),
			'subscriber' => array('type' => 'select', 'options' => $users, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('relations');
		$this->content->message = NULL;
		$rules = array(
			'leader' => 'required|numeric',
			'subscriber' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Relations(post('key'));
			$c->leader = post('leader');
			$c->subscriber = post('subscriber');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		
		$users = array('' => 'Choose');
		
		if ($us = Model_User::fetch())
			foreach($us as $u){
				$users[$u->idu] = $u->username;
			}

		$c = new Model_Relations(get('edit'));
			
		$fields = array(
			'leader' => array('type' => 'select', 'options' => $users, 'div' => array('class' => 'control-group')),
			'subscriber' => array('type' => 'select', 'options' => $users, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}