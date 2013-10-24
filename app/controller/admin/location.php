<?php
class Controller_Admin_Location extends Controller
{
	public function index()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('location');
		$this->content->error_warning = NULL;
		$this->content->categories = Model_CouponsCities::fetch();
		$this->content->form = NULL;
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_CouponsCities($s);
			$c->delete();
		}
		redirect(HTTP_SERVER.'/admin/location/create');
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('location');
		$this->content->error_warning = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_CouponsCities();
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->save();
			unset($_POST);
		}
		$countries = array();
		if($a = Model_CouponsCountries::fetch()) foreach($a as $b) $countries[$b->id] = $b->name;
		$fields = array(			
			'country' => array('type' => 'select', 'options' => $countries),
			'name' => array(),
			'submit' => array('type' => 'submit', 'value' => lang('save')));
		$this->content->categories = NULL;
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('location');
		$this->content->error_warning = NULL;
		$rules = array('name' => 'required|string|max_length[128]');
		$validation = new Validation();
		if($validation->run($rules))
		{
			return;
			$c = new Model_CouponsCities(post('key'));
			$c->name = post('name');
			$c->slug = string::sanitize_url(post('name'));
			$c->save();
			unset($_POST);
			$this->content->error_warning = 'Success';
		}
		
		$c = new Model_CouponsCities(get('edit'));
		$countries = array();
		if($a = Model_CouponsCountries::fetch()) foreach($a as $b) $countries[$b->id] = $b->name;
		$fields = array(
			'country' => array('type' => 'select', 'value' => $c->country, 'options' => $countries),
			'name' => array('value' => $c->name),			
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'submit' => array('type' => 'submit', 'value' => lang('save')));
		$this->content->categories = NULL;
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}