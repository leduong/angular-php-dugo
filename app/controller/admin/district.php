<?php
class Controller_Admin_District extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/district/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_District($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/district/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$sort   = array('id' => 'DESC');
		$total  = Model_District::count();

		$this->content = new View('district');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/district/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');


		$this->content->districts = Model_District::fetch(
			NULL,
			$limit,
			$offset,
			array('city_id' => 'ASC'));

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('district');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string|max_length[32]',
			'sort' => 'required|numberic',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_District();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->city_id = post('city');
			$c->sort = post('sort');
			$c->enable = post('enable')?1:0;
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
			'name' => array(),
			'city' => array('type' => 'select', 'options' => $cities),
			'enable' => array('value' => 1, 'type' => 'checkbox'),
			'sort' => array(),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('category');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string|max_length[32]',
			'sort' => 'required|numberic',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_District(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->city_id = post('city');
			$c->sort = post('sort');
			$c->enable = post('enable')?1:0;
			$c->image = post('image');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		$cities = array('0' => '');
		if ($a = Model_City::fetch())
			foreach($a as $c){
				$cities[$c->id] = $c->name;
		}
		$c = new Model_District(get('edit'));
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name),
			'city' => array('type' => 'select', 'options' => $cities, 'value'=>$c->city_id),
			'sort' => array('value' => $c->sort),
			'enable' => array('value' => $c->enable, 'check'=>$c->enable, 'type' => 'checkbox'),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}