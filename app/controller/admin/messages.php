<?php
class Controller_Admin_Messages extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/messages/lists');
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');

		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Messages($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/messages/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=25;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Messages::count();

		$this->content = new View('messages');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/messages/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array(
			'class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->messages = Model_Messages::fetch(
			NULL,
			$limit,
			$offset,
			array('uid' => 'ASC'));

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('messages');
		$this->content->message = NULL;

		$rules = array(
			'uid'   => 'required|numeric',
			'message'   => 'string',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Messages();
			$c->uid = post('uid');
			$c->message = post('message');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}

		$users = array(''=>'Choose');

		if($us = Model_User::fetch())
			foreach($us as $u){
				$users[$u->idu] = $u->username;
			}

		$fields = array(
			'uid' => array('type'=>'select', 'options'=>$users, 'div' => array('class' => 'control-group')),
			'message' => array('div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('messages');
		$this->content->message = NULL;
		$rules = array(
			'message'   => 'required|string',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Messages(post('key'));
			$msg_id = $c->id;
			$c->message = post('message');
			$c->tag = post('tag');
			$c->save();
			// Tags
			$del = Model_TagsOccurrence::fetch(array('msg_id' => $msg_id));
			if ($del) foreach ($del as $d) $d->delete();
			$tags = explode(',',$c->tag);
			foreach ($tags as $tag) {
				$tag_id = Model_Tags::get_or_insert($tag);
				if ($tag_id){
					$tags_occurrence = new Model_TagsOccurrence();
					$tags_occurrence->msg_id = $msg_id;
					$tags_occurrence->tag_id = $tag_id;
					$tags_occurrence->save();
				}
			}
			// end Tags
			unset($_POST);
			$this->content->message = lang('success');
		}

		$c = new Model_Messages(get('edit'));
		$uid = new Model_User($c->uid);
		$user = sprintf("%s %s (%s)",$uid->first_name, $uid->last_name, $uid->username);

		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'uid' => array('value' => $user, 'attributes' => array('disabled' => 'disabled')),
			'message' => array('type' => 'textarea', 'value' => $c->message, 'div' => array('class' => 'control-group'),
				'attributes' => array('rows' => 10, 'name' => 'message', 'width' => '100%')),
			'tag' => array('value' => $c->tag, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}