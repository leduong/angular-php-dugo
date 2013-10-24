<?php
class Controller_Admin_Shop extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/shop/lists');
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Shop::count();
		
		$this->content = new View('shop');
		
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Shop($s);
			$c->delete();
		}
		$this->content->error_warning = lang('delete_success');
		$pagination = new Pagination($total,HTTP_SERVER."/admin/shop/lists/page/[[page]].html",$page,$limit);
		$this->content->shops = Model_Shop::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Shop::count();
		
		$this->content = new View('shop');
		$this->content->error_warning = $this->content->form = NULL;
		
		$pagination = new Pagination($total,HTTP_SERVER."/admin/shop/lists/page/[[page]].html",$page,$limit);
		$this->content->shops = Model_Shop::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$city = array('0' => '-');
		$cities = Model_CouponsCities::fetch();
		$enabledisable = array('0' => lang('disable'), '1' => lang('enable'));
		foreach($cities as $c) $city[$c->id] = $c->name;
		$rules = array(
		'name' => 'required',
		'city' => 'required',
		'address' => 'required|min_length[20]');
		$fields = array(
			'name' => array('required' => '*'), 
			'address' => array('type' => 'textarea', 'required' => '*'),
			'city' => array('type' => 'select', 'options' => $city, 'required' => '*'),
			'status' => array('type' => 'radio'),
			'image' => array('description' => '<img id="image_preview" src="/images/noimage.jpg" alt=""  onclick="image_upload(\'image\',\'image_preview\');" />'),
			'submit' => array('type' => 'submit', 'value' => lang('create')));
		
		$validation = new Validation();

		if($validation->run($rules)){
			$c = new Model_Shop();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->city = post('city');
			$c->address = post('address');
			$c->status = post('status')?1:0;
			$c->logo = post('image');
			$c->save();
			unset($_POST);
			$this->content->error_warning = lang('success');
		}	
		$this->content = new View('shop_form');
		$this->content->error_warning = NULL;
		$form = new Form($validation, array('id' => 'create'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('shop_form');
		$this->content->error_warning = NULL;
		
		$city =  array('0' => '-');
		$cities = Model_CouponsCities::fetch();
		$enabledisable = array('0' => lang('disable'), '1' => lang('enable'));
		foreach($cities as $c) $city[$c->id] = $c->name;
		
		$validation = new Validation();
		$rules = array(
			'name' => 'required',
			'city' => 'required',
			'address' => 'required|min_length[20]');
		if($validation->run($rules)){
			$c = new Model_Shop(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->city = post('city');
			$c->address = post('address');
			$c->status = post('status')?1:0;
			$c->logo = post('image');
			$c->save();
			unset($_POST);
			$this->content->error_warning = lang('success');
		}
		
		$c = new Model_Shop(get('edit'));
		$image = ($c->logo)?$c->logo:'/images/noimage.jpg';
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name, 'required' => '*'),
			'city' => array('type' => 'select', 'options' => $city, 'value' => $c->city, 'required' => '*'),
			'address' => array('type' => 'textarea', 'value' => $c->address, 'required' => '*'),
			'status' => array('value' => $c->status, 'type' => 'radio'),
			'image' => array('value' => $c->logo, 'description' => '<img id="image_preview" class="preview" src="'.$image.'" alt=""  onclick="image_upload(\'image\',\'image_preview\');" />'),
			'submit' => array('type' => 'submit', 'value' => lang('save')));
		$form = new Form($validation, array('id' => 'create'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}