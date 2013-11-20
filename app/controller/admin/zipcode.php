<?php
class Controller_Admin_Zipcode extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/zipcode/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Zipcode($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/zipcode/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$total  = Model_Zipcode::count();

		$this->content = new View('zipcode');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/zipcode/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->zipcodes = Model_Zipcode::fetch(
			NULL,
			$limit,
			$offset,
			array('name' => 'ASC'));

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('zipcode');
		$this->content->message = NULL;

		$rules = array(
			'name'   => 'required|string|max_length[32]',
			'sort'   => 'required|numeric'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Zipcode();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->sort = post('sort');
			$c->enable = post('enable')?1:0;
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}

		$fields = array(
			'name' => array('div' => array('class' => 'control-group')),
			'sort' => array('div' => array('class' => 'control-group')),
			'enable' => array('type' => 'checkbox', 'value'=>'1', 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('zipcode');
		$this->content->message = NULL;
		$rules = array(
			'name'   => 'required|string|max_length[32]',
			'sort'   => 'required|numeric'
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Zipcode(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->sort = post('sort');
			$c->enable = post('enable')?1:0;
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}

		$c = new Model_Zipcode(get('edit'));

		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name, 'div' => array('class' => 'control-group')),
			'sort' => array('value' => $c->sort, 'div' => array('class' => 'control-group')),
			'enable' => array('value' => '1', 'check'=>$c->enable, 'type' => 'checkbox', 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}