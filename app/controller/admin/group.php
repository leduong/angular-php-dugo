<?php
class Controller_Admin_Group extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/group/lists');
	}
	public function ajax(){
		$sSearch = string::slug(get('sSearch'));
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = get('iDisplayLength');
		$offset = get('iDisplayStart');
		$sort   = array('id' => 'DESC');
		$where  = array("slug LIKE '%$sSearch%'");
		$total  = Model_Group::count($where);

		$array  = array();
		$output = array(
			"sEcho" => intval(get('sEcho')),
			"iTotalRecords" => $total,
			"iTotalDisplayRecords" => $total,
			"aaData" => array()
		);
		$groups = Model_Group::fetch($where, $limit, $offset, $sort);
		if ($groups) foreach ($groups as $g) {
			$by = new Model_User($g->by);
			$name = implode(",", array($by->first_name,$by->email,$by->phone));
			$input = '<input type="checkbox" name="selected[]" class="checkboxes" value="'.$g->id.'" />';
			$onoff = ($g->enable)?'On':'Off';
			$gmap = ($g->map)?'<a target="_blank" href="http://maps.googleapis.com/maps/api/staticmap?center='.str_replace(" ", "", $g->map).'&markers='.str_replace(" ", "", $g->map).'&zoom=15&size=640x640&sensor=false">[Map]</a>':'';
			$action = '<a title="Edit" href="/admin/group/edit/'.$g->id.'"><i class="icon-pencil"></i></a> - <a title="Merge" href="/admin/group/merge/'.$g->id.'"><i class="icon-exchange"></i></a>';
			$array[] = array(
				$input,
				$g->id,
				$g->name,
				$name,
				$g->address,
				$gmap,
				$onoff,
				$action
				);
		}
		Response::json(array(
						"sEcho"                => intval(get('sEcho')),
						"iTotalRecords"        => $total,
						"iTotalDisplayRecords" => $total,
						"aaData"               => $array));
		exit;
	}

	public function merge(){
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$group = new Model_Group(get('merge'));
		$this->content = new View('group');
		$this->content->message = $this->content->form = NULL;
		$old_tags = $new_tags = array();
		// old Tags and Name
		$ar = explode(",", implode(",", array($group->name,$group->long_name,$group->tag)));
		foreach (array_unique($ar) as $a) if ($b=trim($a)) $old_tags[] = $b;

		$rules = array(
			'to_group' => 'required|numeric',
		);
		$validation = new Validation();
		$fields = array(
			'group'    => array('value' => implode(",", array($group->name)+explode(",", $group->long_name)),'attributes' => array('disabled' => 'disabled'), 'div' => array('class' => 'control-group')),
			'to_group' => array('div' => array('class' => 'control-group')),
			'submit'   => array('type' => 'submit', 'value' => lang('apply'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;

		if($validation->run($rules)){
			$new = new Model_Group(post('to_group'));
			if ($new){
				// new_tags
				$ar = explode(",", implode(",", array($new->name,$new->long_name,$new->tag)));
				foreach (array_unique($ar) as $a) if ($b=trim($a)) $new_tags[] = $b;

				$db = registry('db');
				$where = implode(' OR ', Model_TagsGroup::get_query($group->name));
				$query = "SELECT messages.id,
						   COUNT(*) AS occurrences
						   FROM messages, tags, tags_occurrence
						   WHERE messages.id = tags_occurrence.msg_id AND
						   tags.id = tags_occurrence.tag_id AND
						   ($where)
						   GROUP BY messages.id";
				if ($fetch = $db->fetch($query)) foreach ($fetch as $o) {
					$m = new Model_Messages($o->id);
					if (isset($m->id)) {
						$m_tags = array_diff(@explode(",", $m->tag), $old_tags);
						$m->tag = implode(",", array_unique(array_merge($new_tags,$m_tags)));
						$m->save();
						$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id, 'type' => "local"),1);
						if ($mt){
							$mt = end($mt);
						} else {
							$mt = new Model_MessagesMeta();
							$mt->msg_id = $m->id;
							$mt->type = 'local';
						}
						$mt->value = $new->local;
						$mt->save();
						Model_Messages::rebuild($m->id);
					}
				}

				/*if ($this->appsite['mail_group_del']){
					$owner = new Model_User($group->by);
					if ($owner->email){
						$message = sprintf($this->appsite['mail_group_del'], $owner->first_name, $group->name, $new->name);
						$mail = new Mail();
						$mail->setTo($owner->email);
						$mail->setFrom($this->appsite['email']);
						$mail->setSender($this->appsite['domain']);
						$mail->setSubject('Tin nhắn từ nhadat.com');
						$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
						$mail->send();
					}
				}*/
				$group->delete();
				$this->content->message = lang('merge_success');
			}
			$limit  = $this->appsite['limit_per_page'];
			$page   = ((int)get('page')>1)?(int)get('page'):1;
			$offset = $limit*($page-1);
			$sort   = array('id' => 'DESC');
			$total  = Model_Group::count();
			$this->content->form = NULL;
			$this->content->page = $page;
			$pagination = new Pagination($total, HTTP_SERVER."/admin/group/lists/page/[[page]]", $page, $limit, true);
			$pagination->attributes = array('class' => 'dataTables_paginate paging_bootstrap pagination');
			$this->content->groups = Model_Group::fetch(NULL, $limit, $offset, $sort);
			$this->content->pagination = $pagination;
		}
	}

	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		$page     = ((int)get('page')>1)?(int)get('page'):1;
		if (isset($selected)) foreach($selected as $s){
			if($s>1){
				$c = new Model_Group($s);
				if ($ar = explode(",", $c->name.",".$c->long_name)){
					$x = array();
					foreach ($ar as $a) if ($b=string::slug($a)) $x[] = "slug = '$b'";
					$where = implode(" OR ", $x);
					$del = Model_Tags::fetch(array($where));
					if ($del) foreach ($del as $d) $d->delete();
				}
				$c->delete();
			}
		}
		redirect(HTTP_SERVER."/admin/group/lists/page/$page");
	}

	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit  = $this->appsite['limit_per_page'];
		$page   = ((int)get('page')>1)?(int)get('page'):1;
		$offset = $limit*($page-1);
		$sort   = array('id' => 'DESC');
		$total  = Model_Group::count();

		$this->content = new View('group');
		$this->content->message = $this->content->form = NULL;
		$this->content->page = $page;

		$pagination = new Pagination($total, HTTP_SERVER."/admin/group/lists/page/[[page]]", $page, $limit, true);
		$pagination->attributes = array('class' => 'dataTables_paginate paging_bootstrap pagination');

		$this->content->groups = Model_Group::fetch(NULL, $limit, $offset, $sort);
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		die("Off this function by Duong");
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('group');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string|max_length[32]',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c            = new Model_Group();
			$c->map       = post('map');
			$c->tag       = post('tag');
			$c->name      = post('name');
			$c->slug      = string::sanitize_url(post('name'));
			$c->local     = post('local');
			$c->enable    = post('enable')?1:0;
			$c->address   = post('address');
			$c->long_name = post('other_name');
			$c->save();

			// Model_TagsAuto && Model_Tags
			$tags = explode(",", implode(",", array($c->name,$c->long_name)));
			foreach ($tags as $v) Model_Tags::get_or_insert($v,$c->id);

			$del = Model_TagsAuto::fetch(array('group_id' => $c->id));
			if ($del) foreach ($del as $d) $d->delete();
			foreach ($tags as $v) Model_TagsAuto::get_or_insert($c->name,$c->id);

			// Model_TagsGroup
			$del = Model_TagsGroup::fetch(array('group_id' => $c->id));
			if ($del) foreach ($del as $d) $d->delete();
			$tag_id = 0;
			$tag_id = Model_TagsGroup::get_or_insert($c->name,$tag_id,$c->id);
			$tags = explode(",", $c->long_name);
			if($tags)foreach ($tags as $v) if ($tag=trim($v)) Model_TagsGroup::get_or_insert($tag,$tag_id,$c->id);

			$this->content->message = lang('success');
			unset($_POST);
		}

		$fields = array(
			'name'       => array('div' => array('class' => 'control-group')),
			'other_name' => array('div' => array('class' => 'control-group')),
			'address'    => array('div' => array('class' => 'control-group')),
			'local'      => array('div' => array('class' => 'control-group')),
			'tag'        => array('div' => array('class' => 'control-group')),
			'map'        => array('div' => array('class' => 'control-group')),
			'enable'     => array('value' => 1, 'type' => 'checkbox', 'div' => array('class' => 'control-group')),
			'submit'     => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('group');
		$this->content->message = NULL;
		$rules = array(
			'name' => 'required|string',
		);

		$c = new Model_Group(get('edit'));
		$validation = new Validation();
		if($validation->run($rules))
		{
			$old_tags = $new_tags = array();
			// old Tags and Name
			$ar = explode(",", implode(",", array($c->name,$c->long_name,$c->tag)));
			foreach (array_unique($ar) as $a) if ($b=trim($a)) $old_tags[] = $b;
			$old_name = $c->name;
			$old_tagsname = explode(",", implode(",", array($c->name,$c->long_name)));
			$where    = implode(' OR ', Model_TagsGroup::get_query($old_name));

			/*if ($found = Model_TagsAuto::count(array($where))){
				Response::json(array('flash' => "Tên \"$v\" đã được sử dụng"),403);
				exit;
			}*/

			// save to obj
			$c->map       = trim(post('map'));
			$c->name      = trim(post('name'));
			$c->slug      = string::sanitize_url(post('name'));
			$c->local     = trim(post('local'));
			$c->enable    = post('enable')?1:0;
			$c->address   = trim(post('address'));
			$c->long_name = trim(post('other_name'));

			// fix unique tag
			// new tag
			$x = array();
			$ar = explode(",", post('tag'));
			foreach (array_unique($ar) as $a) if ($b=trim($a)) $x[] = $b;
			// $c->tag = implode(",", $x); //Khong save tag edit
			// Save
			$c->save();

			// Model_TagsAuto && Model_Tags
			$tags = explode(",", implode(",", array($c->name,$c->long_name)));
			foreach ($tags as $v) Model_Tags::get_or_insert($v);

			$del = Model_TagsAuto::fetch(array('group_id' => $c->id));
			if ($del) foreach ($del as $d) $d->delete();
			foreach ($tags as $v) Model_TagsAuto::get_or_insert($c->name,$c->id);

			// Model_TagsGroup
			$del = Model_TagsGroup::fetch(array('group_id' => $c->id));
			if ($del) foreach ($del as $d) $d->delete();
			$tag_id = 0;
			$tag_id = Model_TagsGroup::get_or_insert($c->name,$tag_id,$c->id);
			$tags = explode(",", $c->long_name);
			if($tags)foreach ($tags as $v) if ($tag=trim($v)) Model_TagsGroup::get_or_insert($tag,$tag_id,$c->id);


			// Update
			$ar = explode(",", implode(",", array($c->name,$c->long_name,$c->tag)));
			foreach (array_unique($ar) as $a) if ($b=trim($a)) $new_tags[] = $b;

			if (array_diff($new_tags, $old_tags)){
				$db = registry('db');
				$query = "SELECT messages.id,
						   COUNT(*) AS occurrences
						   FROM messages, tags, tags_occurrence
						   WHERE messages.id = tags_occurrence.msg_id AND
						   tags.id = tags_occurrence.tag_id AND
						   ($where)
						   GROUP BY messages.id";
				if ($fetch = $db->fetch($query)) foreach ($fetch as $o) {
					$m = new Model_Messages($o->id);
					if (isset($m->id)) {
						$m_diff = array_diff(@explode(",", $m->tag),$old_tags);
						$m->tag = implode(",", array_unique(array_merge($new_tags,$m_diff)));
						$m->save();
						$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id, 'type' => "local"),1);
						if ($mt){
							$mt = end($mt);
						} else {
							$mt = new Model_MessagesMeta();
							$mt->msg_id = $m->id;
							$mt->type = 'local';
						}
						$mt->value = $c->local;
						$mt->save();
						Model_Messages::rebuild($m->id);
					}
				}
			}

			$new_tagsname = explode(",", implode(",", array($c->name,$c->long_name)));
			if ($diff = array_diff($old_tagsname, $new_tagsname)){
				$x = array();
				foreach ($diff as $a) if ($b=string::slug($a)) $x[] = "slug = '$b'";
				$where = implode(" OR ", $x);
				$del = Model_Tags::fetch(array($where));
				if ($del) foreach ($del as $d) $d->delete();
			}

			$this->content->message = lang('success');
			unset($_POST);
		}

		$fields = array(
			'key'         => array('type' => 'hidden', 'value' => $c->id),
			'name'        => array('value' => $c->name, 'div' => array('class' => 'control-group')),
			'other_name'  => array('value' => $c->long_name, 'div' => array('class' => 'control-group')),
			'address'     => array('value' => $c->address, 'div' => array('class' => 'control-group')),
			'local'       => array('value' => $c->local, 'div' => array('class' => 'control-group')),
			'tag'         => array('value' => $c->tag, 'div' => array('class' => 'control-group')),
			'map'         => array('value' => $c->map, 'div' => array('class' => 'control-group')),
			'enable'      => array('value' => $c->enable, 'check'=>$c->enable, 'type' => 'checkbox', 'div' => array('class' => 'control-group')),
			'submit'      => array('type' => 'submit', 'value' => lang('save'), 'class'=>'btn blue', 'div' => array('class' => 'form-actions'))
		);
		$form = new Form($validation, array('id' => 'form'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}