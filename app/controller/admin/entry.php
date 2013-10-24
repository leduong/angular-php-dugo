<?php
class Controller_Admin_Entry extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/entry/lists');
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$z = new Model_Entry($s);
			$z->delete();
		}
		redirect(HTTP_SERVER.'/admin/entry/lists');
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('entry');
		$this->content->message = NULL;
		$rules = array(
			'title' => 'required|string|max_length[128]',
			'description' => 'required|string',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$z = new Model_Entry();
			$z->title = post('title');
			$z->slug = string::sanitize_url(post('title'));
			$z->category_id = post('category');
			$z->meta_keywords = post('meta_keywords');
			$z->meta_description = post('meta_description');
			$z->excerpt = post('excerpt');
			$z->description = post('description');
			$z->published = post('published')?1:0;
			$z->image = post('image');
			$z->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		$parent = array('0' => '');
		if ($articles = Model_Articles::fetch(array('category_id' => 0))) foreach($articles as $x){
			$parent[$x->id] = $x->name;
			if($s=$x->category()) foreach ($s as $v) $parent[$v->id] = $x->name." » ".$v->name;
		}
		$fields = array(
			'title' => array(),
			'category' => array('type' => 'select', 'options' => $parent),
			'link' => array(),
			'meta_keywords' => array(),
			'meta_description' => array(),
			'excerpt' => array('type' => 'textarea'),
			'description' => array('type' => 'textarea'),
			'image' => array('description' => '<img id="image_preview" class="preview" src="/images/noimage.jpg" alt="no image"  onclick="image_upload(\'image\',\'image_preview\');" />', 'attributes' => array('id' => 'image', 'onclick' => "image_upload('image','image_preview');"),),
			'published' => array('value' => 1, 'type' => 'radio'),
			'submit' => array('type' => 'submit', 'value' => lang('submit'))
		);
		$form = new Form($validation, array('id' => 'entry'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('entry');
		$this->content->message = NULL;
		$rules = array(
			'title' => 'required|string|max_length[128]',
			'description' => 'required|string',
		);
		$validation = new Validation();
		if($validation->run($rules))
		{
			$z = new Model_Entry(post('key'));
			$z->title = post('title');
			$z->category_id = post('category');
			$z->meta_keywords = post('meta_keywords');
			$z->meta_description = post('meta_description');
			$z->excerpt = post('excerpt');
			$z->description = post('description');
			$z->published = post('published')?1:0;
			$z->slug = string::sanitize_url(post('title'));
			$z->image = post('image');
			$z->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		$parent = array('0' => '');
		if ($articles = Model_Articles::fetch(array('category_id' => 0))) foreach($articles as $x){
			$parent[$x->id] = $x->name;
			if($s=$x->category()) foreach ($s as $v) $parent[$v->id] = $x->name." » ".$v->name;
		}
		$z = new Model_Entry(get('edit'));
		$image = ($z->image)?$z->image:'/images/noimage.jpg';
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $z->id),
			'title' => array('value' => $z->title),
			'category' => array('type' => 'select', 'options' => $parent, 'value' => $z->category_id),
			'link' => array('value' => $z->link),
			'meta_keywords' => array('value' => $z->meta_keywords),
			'meta_description' => array('value' => $z->meta_description),
			'excerpt' => array('type' => 'textarea', 'value' => $z->excerpt),
			'description' => array('type' => 'textarea', 'value' => $z->description),
			'image' => array('value' => $z->image, 'description' => '<img id="image_preview" class="preview" src="'.$image.'" alt=""  onclick="image_upload(\'image\',\'image_preview\');" />', 'attributes' => array('id' => 'image', 'onclick' => "image_upload('image','image_preview');"),),
			'published' => array('value' => $z->published, 'type' => 'radio'),
			'submit' => array('type' => 'submit', 'value' => lang('save'))
		);
		$form = new Form($validation, array('id' => 'entry'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function lists(){
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('entry_list');
		$this->content->message = NULL;
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);

		$total = Model_Entry::count();
		$pagination = new Pagination($total,HTTP_SERVER."/entry/page/[[page]].html",$page,$limit);
		$this->content->entries = Model_Entry::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
}