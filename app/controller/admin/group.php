<?php
class Controller_Admin_Group extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/group/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$d = new Model_Group($s);
			$d->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/group/lists/page/$page");
	}

	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Group::count();
		$sort = array('id' => 'DESC');

		$this->content = new View('group');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/group/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');


		$this->content->groups = Model_Group::fetch(
			NULL,
			$limit,
			$offset,
			$sort);

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('group');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string|max_length[32]',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Group();
			$c->name        = post('name');
			$c->address     = post('address');
			$c->description = post('description');
			$c->map         = post('map');
			$c->slug        = string::sanitize_url(post('name'));
			$c->enable      = post('enable')?1:0;
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}
		$cities = array('0' => '');
		if ($districts = Model_City::fetch())
			foreach($districts as $c){
				$cities[$c->id] = $c->name;
		}

		$fields = array(
			'name'        => array(),
			'address'     => array(),
			'description' => array(),
			'map'         => array(),
			'enable'      => array('value' => 1, 'type' => 'checkbox'),
			'submit'      => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('group');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string|max_length[32]',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Group(post('key'));
			$c->name        = post('name');
			$c->address     = post('address');
			$c->description = post('description');
			$c->map         = post('map');
			$c->slug        = string::sanitize_url(post('name'));
			$c->enable      = post('enable')?1:0;
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}
		$cities = array('0' => '');
		if ($a = Model_City::fetch())
			foreach($a as $c){
				$cities[$c->id] = $c->name;
		}
		$c = new Model_Group(get('edit'));
		$fields = array(
			'key'         => array('type' => 'hidden', 'value' => $c->id),
			'name'        => array('value' => $c->name),
			'address'     => array('value' => $c->address),
			'description' => array('value' => $c->description),
			'map'         => array('value' => $c->map),
			'enable'      => array('value' => $c->enable, 'check'=>$c->enable, 'type' => 'checkbox'),
			'submit'      => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}