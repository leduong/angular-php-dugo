<?php
class Controller_Admin_Info extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/info/lists');
	}
	
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Info($s);
			$c->delete();
		}
		redirect(HTTP_SERVER.'/admin/info/lists');
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Info::count();
		
		$this->content = new View('info');
		$this->content->error_warning = $this->content->form = NULL;
		
		$pagination = new Pagination($total,HTTP_SERVER."/admin/info/lists/page/[[page]].html",$page,$limit);
		$this->content->lists = Model_Info::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('info');
		$this->content->error_warning = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Info();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->description = post('description');
			$c->sort_order = post('sort_order');
			$c->status = post('status');
			$c->save();
			unset($_POST);
			$this->content->error_warning = lang('success');
		}
	
		$fields = array(
			'name' => array(),
			'description' => array('type' => 'textarea'),
			'sort_order' => array(),
			'status' => array('type' => 'select', 'options' => array('1' => lang('enable'), '0' => lang('disable'))),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('info');
		$this->content->error_warning = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Info(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->description = post('description');
			$c->sort_order = post('sort_order');
			$c->status = post('status');
			$c->save();
			unset($_POST);
			$this->content->error_warning = lang('success');
		}
		
		$c = new Model_Info(get('edit'));
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name),
			'description' => array('type' => 'textarea', 'value' => $c->description),
			'sort_order' => array('value' => $c->sort_order),
			'status' => array('type' => 'select', 'options' => array('1' => lang('enable'), '0' => lang('disable')), 'value' => $c->status),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}