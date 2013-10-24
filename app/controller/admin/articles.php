<?php
class Controller_Admin_Articles extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/articles/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected abs(number)s $s){
			$c = new Model_Articles($s);
			$c->delete();
		}
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Articles::count();

		$this->content = new View('articles');
		$this->content->message = lang('delete_success');
		$this->content->form = NULL;

		$pagination = new Pagination($total,HTTP_SERVER."/admin/articles/lists/page/[[page]].html",$page,$limit);
		$this->content->categories = Model_Articles::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Articles::count();

		$this->content = new View('articles');
		$this->content->message = $this->content->form = NULL;

		$pagination = new Pagination($total,HTTP_SERVER."/admin/articles/lists/page/[[page]].html",$page,$limit);
		$this->content->categories = Model_Articles::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('articles');
		$this->content->message = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Articles();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->category_id = post('parent');
			$c->meta_keywords = post('meta_keywords');
			$c->meta_description = post('meta_description');
			$c->description = post('description');
			$c->sort_order = post('sort_order');
			$c->status = post('status')?1:0;
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}
		$parent = array('0' => '');
		if ($categories = Model_Articles::fetch(array('category_id' => 0))) foreach($categories as $c){
			$parent[$c->id] = $c->name;
			if($s=$c->category())
				foreach ($s as $v) $parent[$v->id] = $c->name." » ".$v->name;
		}
		$fields = array(
			'name' => array(),
			'parent' => array('type' => 'select', 'options' => $parent),
			'meta_keywords' => array(),
			'meta_description' => array(),
			'description' => array('type' => 'textarea'),
			'sort_order' => array(),
			'status' => array('value' => 1, 'type' => 'radio'),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('articles');
		$this->content->message = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Articles(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->category_id = post('parent');
			$c->meta_keywords = post('meta_keywords');
			$c->meta_description = post('meta_description');
			$c->description = post('description');
			$c->sort_order = post('sort_order');
			$c->status = post('status')?1:0;
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		$parent = array('0' => '');
		if ($categories = Model_Articles::fetch(array('category_id' => 0))) foreach($categories as $c){
			$parent[$c->id] = $c->name;
			if($s=$c->category())
				foreach ($s as $v) $parent[$v->id] = $c->name." » ".$v->name;
		}
		$c = new Model_Articles(get('edit'));
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name),
			'parent' => array('type' => 'select', 'options' => $parent, 'value' => $c->category_id),
			'meta_keywords' => array('value' => $c->meta_keywords),
			'meta_description' => array('value' => $c->meta_description),
			'description' => array('type' => 'textarea', 'value' => $c->description),
			'sort_order' => array('value' => $c->sort_order),
			'status' => array('value' => $c->status, 'type' => 'radio'),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}