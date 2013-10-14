<?php
class Controller_Admin_Likes extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/likes/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Likes($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/likes/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=25;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Likes::count();
		
		$this->content = new View('likes');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/likes/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');
		
		$this->content->likes = Model_Likes::fetch(
			NULL,
			$limit,
			$offset,
			array('post' => 'ASC'));
			
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('likes');
		$this->content->message = NULL;

		$rules = array(
			'post'   => 'required|numeric',
			'by'   => 'required|numeric'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Likes();
			$c->post = post('post');
			$c->by = post('by');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}
		
		$bys = array('' => 'Choose');
		
		if ($users = Model_User::fetch())
			foreach($users as $c){
				$bys[$c->idu] = $c->username;
			}

		$fields = array(
			'post' => array('div' => array('class' => 'control-group')),
			'by' => array('type' => 'select', 'options' => $bys, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('likes');
		$this->content->message = NULL;
		$rules = array(
			'post'   => 'required|numeric',
			'by'   => 'required|numeric'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Likes(post('key'));
			$c->post = post('post');
			$c->by = post('by');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		
		$bys = array('' => 'Choose');
		
		if ($users = Model_User::fetch())
			foreach($users as $c){
				$bys[$c->idu] = $c->username;
			}

		$c = new Model_Likes(get('edit'));
			
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'post' => array('value' => $c->post, 'div' => array('class' => 'control-group')),
			'by' => array('type' => 'select', 'value' => $c->by, 'options' => $bys, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}