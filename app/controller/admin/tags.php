<?php
class Controller_Admin_Tags extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/tags/lists');
	}

	public function upload()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('tags');
		$this->content->message = NULL;
		$rules = array();
		$validation = new Validation();
		if($validation->run($rules))
		{
			$result = array();
			$csv = explode("\n", post('csv'));
			$txt = explode("\n", post('txt'));
			/*if ($csv&&$txt){
				$del = Model_TagsGroup::fetch(array('group_id' => '0'));
				if ($del) foreach ($del as $d) $d->delete();
				$del = Model_TagsAuto::fetch(array('group_id' => '0'));
				if ($del) foreach ($del as $d) $d->delete();
			}*/

			if (count($csv)>1) {
				$del = Model_TagsAuto::fetch(array('group_id' => '0'));
				if ($del) foreach ($del as $d) $d->delete();

				foreach ($csv as $a) if (($b = explode(",", $a))&&(count($b)==2)){
					$insert = Model_TagsAuto::get_or_insert($b[0],0,$b[1]);
					if ($insert) $result[] = "$a => Ok";
				} else {
					$result[] = "$a => Skip";
				}
			}

			if (count($txt)>1) {
				$del = Model_TagsGroup::fetch(array('group_id' => '0'));
				if ($del) foreach ($del as $d) $d->delete();

				foreach ($txt as $a) if (($b = explode(":", $a))&&(count($b)==2)){
					if (strtolower($b[0])==='del'){
						$c = explode(",", $b[1]);
						$d = array();
						foreach ($c as $v) $q[] = "`slug` = '".string::slug($v)."'";
						$del = Model_TagsAuto::fetch(array('group_id' => '0', implode(" OR ", $q)));
						if ($del) foreach ($del as $d) $d->delete();

						//$del = Model_TagsGroup::fetch(array('group_id' => '0', implode(" OR ", $q)));
						//if ($del) foreach ($del as $d) $d->delete();
						$del = Model_Tags::fetch(array(implode(" OR ", $q)));
						if ($del) foreach ($del as $d) $d->delete();
						$result[] = "$a => Ok";
					} elseif (strtolower($b[0])==='peer'){
						$c = explode(",", $b[1]);
						$tag_id = Model_TagsGroup::get_or_insert($c[0],0,0,1);
						for ($i=1; $i <= count($c); $i++) Model_TagsGroup::get_or_insert($c[$i],$tag_id,0,1);
						if ($tag_id) $result[] = "$a => Ok";
					} elseif (strtolower($b[0])==='parent'){
						$c = explode(",", $b[1]);
						$tag_id = Model_TagsGroup::get_or_insert($c[0],0,0);
						for ($i=1; $i <= count($c); $i++) Model_TagsGroup::get_or_insert($c[$i],$tag_id,0,0);
						if ($tag_id) $result[] = "$a => Ok";
					}
				} else {
					$result[] = "$a => Skip";
				}
			}
			$this->content->message = implode("<br />", $result);
			unset($_POST);
		}

		$fields = array(
			'tag_suggest' => array('type' => 'textarea', 'div' => array('class' => 'control-group'), 'attributes' => array('name' => 'csv', 'rows' => '5', 'placeholder' => "tag_name,tag_hit")),
			'tag_group' => array('type' => 'textarea', 'div' => array('class' => 'control-group'), 'attributes' => array('name' => 'txt', 'rows' => '5', 'placeholder' => "del:tag1,tag2,tag3 \n peer:tag1,tag2,tag3 \n parent:tag1,tag2")),
			'submit' => array('type' => 'submit', 'value' => lang('Go'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');

		if (isset($selected)) foreach($selected as $s){
			$c = new Model_TagsGroup($s);
			$c->delete();
		}

		$page=((int)post('page')>1)?(int)post('page'):1;

		redirect(HTTP_SERVER."/admin/tags/lists/page/$page");
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$where  = array('tag_id' => 0);
		$total  = Model_TagsGroup::count($where);
		$sort	= array('id' => 'DESC');

		$this->content          = new View('tags');
		$this->content->message = $this->content->form = NULL;
		$this->content->page    = $page;

		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/tags/lists/page/[[page]]",
			$page,
			$limit,
			true
		);
		$pagination->attributes = array('class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->tags = Model_TagsGroup::fetch(
			$where,
			$limit,
			$offset,
			$sort);

		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('tags');
		$this->content->message = NULL;

		$rules = array(
			'name'   => 'required|string',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c         = new Model_TagsGroup();
			$c->name   = post('name');
			$c->slug   = string::slug(post('name'));
			$c->tag_id = post('group');
			$c->save();
			$this->content->message = lang('success');
			unset($_POST);
		}


		$parent = array('0' => '');
		if ($ar = Model_TagsGroup::fetch(array('tag_id' => 0)))
			foreach($ar as $a){
				$parent[$a->id] = $a->name;
				if($s = $a->tags())
					foreach ($s as $b) $parent[$b->id] = $a->name." » ".$b->name;
		}
		$fields = array(
			'name' => array('div' => array('class' => 'control-group')),
			'group' => array('type' => 'select', 'options' => $parent, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);

		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('tags');
		$this->content->message = NULL;
		$rules = array(
			'name'   => 'required|string|max_length[32]',
		);

		$c = new Model_TagsGroup(get('edit'));

		$validation = new Validation();
		if($validation->run($rules))
		{
			$c->name   = post('name');
			$c->slug   = string::slug(post('name'));
			$c->tag_id = post('group');
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}

		$parent = array('0' => '');
		if ($ar = Model_TagsGroup::fetch(array('tag_id' => 0)))
			foreach($ar as $a){
				$parent[$a->id] = $a->name;
				if($s = $a->tags())
					foreach ($s as $b) $parent[$b->id] = $a->name." » ".$b->name;
		}

		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name, 'div' => array('class' => 'control-group')),
			'group' => array('type' => 'select', 'value' => $c->tag_id, 'options'=> $parent, 'div' => array('class' => 'control-group')),
			'submit' => array('type' => 'submit',
				'value' => lang('save'),
				'class'=>'btn blue',
				'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form', 'class' => 'form-horizontal'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}