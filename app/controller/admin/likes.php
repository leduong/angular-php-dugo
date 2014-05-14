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
			$d = new Model_Likes($s);
			$d->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;
		redirect(HTTP_SERVER."/admin/likes/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = 25;
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$total  = Model_Likes::count();

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
		$pagination->attributes = array('class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->likes = Model_Likes::fetch(
			NULL,
			$limit,
			$offset,
			array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('likes');
		$this->content->message = NULL;

		$rules = array(
			'msg_id'  => 'required|numeric',
			'user_id' => 'required|numeric'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Likes();
			$c->by     = post('user_id');
			$c->msg_id = post('msg_id');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}

		$fields = array(
			'msg_id' => array('div' => array('class' => 'control-group')),
			'user_id' => array('div' => array('class' => 'control-group')),
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
			'msg_id'  => 'required|numeric',
			'user_id' => 'required|numeric'
		);
		$validation = new Validation();
		$c = new Model_Likes(get('edit'));
		if($validation->run($rules))
		{
			$c->msg_id = post('msg_id');
			$c->by     = post('user_id');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}

		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'user_id' => array('value' => $c->by, 'div' => array('class' => 'control-group')),
			'msg_id' => array('value' => $c->msg_id, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}