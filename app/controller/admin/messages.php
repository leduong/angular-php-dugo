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
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$total  = Model_Messages::count();
		$sort	= array('id' => 'DESC');

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
			$sort);

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
			$c          = new Model_Messages();
			$c->uid     = post('uid');
			$c->message = post('message');
			$c->type    = post('type');
			$c->tag     = post('tag');
			$c->save();

			$meta = array();
			if(post('local')   != '') $meta['local'] = post('local');
			if(post('address') != '') $meta['address'] = post('address');
			if(post('map')     != '') $meta['map'] = post('map');
			if(post('rent')   != '') $meta['rent'] = post('rent');
			if(post('price')   != '') $meta['price'] = post('price');
			foreach($meta as $k => $v){
				$mt = new Model_MessagesMeta();
				$mt->msg_id = $c->id;
				$mt->type = $k;
				$mt->value = $v;
				$mt->save();
			}

			Model_Messages::rebuild($c->id);
			$this->content->message = lang('success');
			unset($_POST);
		}

		$types = array('status'=>'Status', 'realestate'=>'Real Estate');

		$fields = array(
			'uid'     => array('div' => array('class' => 'control-group')),
			'type'    => array('type'=>'select', 'options'=>$types, 'div' => array('class' => 'control-group')),
			'message' => array('type' => 'textarea', 'div' => array('class' => 'control-group')),
			'tag'     => array('div' => array('class' => 'control-group')),
			'address' => array('div' => array('class' => 'control-group')),
			'rent'    => array('div' => array('class' => 'control-group')),
			'price'   => array('div' => array('class' => 'control-group')),
			'map'     => array('div' => array('class' => 'control-group')),
			'submit'  => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
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

		$c = new Model_Messages(get('edit'));
		$msg_id = $c->id;

		$validation = new Validation();
		if($validation->run($rules))
		{
			$c->message = post('message');
			$c->tag     = post('tag');
			$c->type    = post('type');
			$c->value   = post('picture');
			$c->save();
			// Ping SEO
			$url = ($c->type=='status')?"/c/".$c->link.".html":"/p/".$c->link.".html";
			pingSE($url);

			$del = Model_MessagesMeta::fetch(array('msg_id' => $msg_id));
			if ($del) foreach ($del as $d) $d->delete();
			$meta = array();

			if(post('local')   != '') $meta['local'] = post('local');
			if(post('address') != '') $meta['address'] = post('address');
			if(post('map')     != '') $meta['map'] = post('map');
			if(post('rent')    != '') $meta['rent'] = post('rent');
			if(post('price')   != '') $meta['price'] = post('price');
			foreach($meta as $k => $v){
				$mt = new Model_MessagesMeta();
				$mt->msg_id = $msg_id;
				$mt->type = $k;
				$mt->value = trim($v);
				$mt->save();
			}
			Model_Messages::rebuild($c->id);
			// end Tags
			unset($_POST);
			$this->content->message = lang('success');
		}

		$uid = new Model_User($c->uid);
		$ms = array('address'=>'','rent'=>'','map'=>'','price'=>'','local'=>'');
		$types = array('status'=>'Status', 'realestate'=>'Real Estate');
		$meta = Model_MessagesMeta::fetch(array('msg_id'=>$c->id));
		if ($meta) foreach($meta as $m){
			$ms[$m->type] = $m->value;
		}
		$user = sprintf("%s %s (id: %s)",$uid->first_name, $uid->last_name, $uid->idu);

		$fields = array(
			'key'        => array('type' => 'hidden', 'value' => $c->id),
			'uid'        => array('value' => $user, 'attributes' => array('disabled' => 'disabled')),
			'type'       => array('value' => $c->type, 'type'=>'select', 'options'=>$types,),
			'message'    => array('type' => 'textarea', 'value' => $c->message, 'div' => array('class' => 'control-group'), 'attributes' => array('rows' => 10, 'name' => 'message', 'width' => '100%')),
			'tag'        => array('value' => $c->tag, 'div' => array('class' => 'control-group')),
			'picture'    => array('value' => $c->value, 'div' => array('class' => 'control-group')),
			'address'    => array('value' => $ms['address'], 'div' => array('class' => 'control-group')),
			'local'      => array('value' => $ms['local'], 'div' => array('class' => 'control-group')),
			'rent'       => array('value' => $ms['rent'], 'div' => array('class' => 'control-group')),
			'price'      => array('value' => $ms['price'], 'div' => array('class' => 'control-group')),
			'map'        => array('value' => $ms['map'], 'div' => array('class' => 'control-group')),
			'submit'     => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}