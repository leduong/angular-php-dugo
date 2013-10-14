<?php
class Controller_Admin_Setting extends Controller
{
	public function index()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('config');
		$this->content->error_warning = NULL;
		$validation = new Validation();

		$fields = array();
		$settings = Model_Setting::fetch(array('group' => 'config'));
		foreach($settings as $s){
			$rules[$s->key] = 'required|string';
			$fields[$s->key] = array('value' => $s->value, 'description' => $s->description);
		}
		$fields['submit'] = array('type' => 'submit', 'value' => 'Submit');

		if($validation->run($rules))
		{
			foreach($_POST as $k => $v){
				if(isset($fields[$k]) && !in_array($k,array("submit","token"))){
					Model_Setting::$db->update('setting', array('value' => $v), array('group' => 'config', 'key' => $k));
					$fields[$k] = array('value' => $v);
				}
			}
			$this->content->error_warning = 'Success!';
		}

		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}