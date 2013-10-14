<?php
class Controller_Admin_Reports extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/reports/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Reports($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/reports/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=25;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Reports::count();
		
		$this->content = new View('reports');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/reports/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');
		
		$this->content->reports = Model_Reports::fetch(
			NULL,
			$limit,
			$offset,
			array('id' => 'DESC'));
			
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('reports');
		$this->content->message = NULL;

		$rules = array(
			'post' => 'required|string|max_length[11]',
			'parent' => 'required|numeric',
			'type' => 'required|numeric',
			'by' => 'required|numeric',
			'state' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Reports();
			$c->post = post('post');
			$c->parent = post('parent');
			$c->type = post('type');
			$c->by = post('by');
			$c->state = post('state');
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
			'post' => array('div' => array('class' => 'control-group')),
			'parent' => array('div' => array('class' => 'control-group')),
			'type' => array('div' => array('class' => 'control-group')),
			'by' => array('type' => 'select', 'options' => $users, 'div' => array('class' => 'control-group')),
			'state' => array('div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('reports');
		$this->content->message = NULL;
		$rules = array(
			'post' => 'required|string|max_length[11]',
			'parent' => 'required|numeric',
			'type' => 'required|numeric',
			'by' => 'required|numeric',
			'state' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Reports(post('key'));
			$c->post = post('post');
			$c->parent = post('parent');
			$c->type = post('type');
			$c->by = post('by');
			$c->state = post('state');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		
		$users = array('' => 'Choose');
		
		if ($us = Model_User::fetch())
			foreach($us as $u){
				$users[$u->idu] = $u->username;
			}

		$c = new Model_Reports(get('edit'));
			
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'post' => array('value' => $c->post, 'div' => array('class' => 'control-group')),
			'parent' => array('value' => $c->parent, 'div' => array('class' => 'control-group')),
			'type' => array('value' => $c->type, 'div' => array('class' => 'control-group')),
			'by' => array('type' => 'select', 'options' => $users, 'value' => $c->by, 'div' => array('class' => 'control-group')),
			'state' => array('value' => $c->state, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}