<?php
class Controller_Admin_Modules extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/modules/lists');
	}

	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('modules');
		$this->content->message = $this->content->form = NULL;
		$this->content->lists = Model_Setting::fetch(array('group' => 'module'));
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('modules');
		$this->content->message = NULL;
		$rules = array('key' => 'required|numeric');
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Setting(post('key'));
			$c->value = post('description');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		
		$c = new Model_Setting(get('edit'));
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => lang($c->key), 'attributes' => array('disabled' => 'disabled')),
			'description' => array('type' => 'textarea', 'value' => $c->value),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}