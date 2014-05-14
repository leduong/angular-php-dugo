<?php
class Controller_Admin_Comments extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/comments/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Comments($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/comments/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$sort   = array('id' => 'DESC');
		$total  = Model_Comments::count();

		$this->content = new View('comments');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/comments/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->comments = Model_Comments::fetch(
			NULL,
			$limit,
			$offset,
			array('id' => 'DESC'));

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('comments');
		$this->content->message = NULL;

		$rules = array(
			'user_id'   => 'required|numeric',
			'msg_id'   => 'required|numeric',
			'message' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Comments();
			$c->msg_id = post('msg_id');
			$c->user_id = post('user_id');
			$c->message = post('message');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}

		$fields = array(
			'user_id' => array('div' => array('class' => 'control-group')),
			'msg_id' => array('div' => array('class' => 'control-group')),
			'message' => array('type' => "textarea", 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('comments');
		$this->content->message = NULL;
		$rules = array(
			'user_id'   => 'required|numeric',
			'msg_id'   => 'required|numeric',
			'message' => 'required|string'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Comments(post('key'));
			$c->user_id = post('user_id');
			$c->msg_id = post('msg_id');
			$c->message = post('message');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}

		$c = new Model_Comments(get('edit'));

		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'user_id' => array('value' => $c->by, 'div' => array('class' => 'control-group')),
			'msg_id' => array('value' => $c->msg_id, 'div' => array('class' => 'control-group')),
			'message' => array('value' => $c->message, 'type' => "textarea", 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}