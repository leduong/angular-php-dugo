<?php
class Controller_Admin_Product extends Controller
{
	public function index()
	{
		redirect(HTTP_SERVER.'/admin/product/lists');
	}
	public function subtype()
	{	
		$type_id = get('type_id');$subtype_id = get('subtype_id');		
		if ($types = new Model_ProductCategory($type_id)) {
			$subtype = $types->subtype();
			foreach($subtype as $z) {
				$a=array('value' => $z->id);
				if($subtype_id == $z->id) $a['selected']='selected';
				print html::tag('option',$z->name,$a);
			}
		}
		else print '<option value="">-</option>';
		exit;
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		
		$this->content = new View('product');
		$this->content->message = $this->content->form = NULL;
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_Product($s);
			$c->delete();
		}
		$this->content->message = lang('delete_success');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Product::count();
		
		$pagination = new Pagination($total,HTTP_SERVER."/admin/product/lists/page/[[page]].html",$page,$limit);
		$this->content->coupons = Model_Product::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_Product::count();
		
		$this->content = new View('product');
		$this->content->message = $this->content->form = NULL;
		
		$pagination = new Pagination($total,HTTP_SERVER."/admin/product/lists/page/[[page]].html",$page,$limit);
		$this->content->coupons = Model_Product::fetch(NULL,$limit,$offset,array('id' => 'DESC'));
		$this->content->pagination = $pagination;
	}
	public function create()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$enabledisable = array('0' => lang('disable'), '1' => lang('enable'));
		$categories = array('0' => '-');
		if ($fetchs = Model_ProductCategory::fetch(array('category_id' => 0))) 
			foreach($fetchs as $c){
				$categories[$c->id] = $c->name;
				if($s=$c->category())
					foreach ($s as $v) $categories[$v->id] = $c->name." » ".$v->name;
		}
			
		$rules = array(
			'name' => 'required',
			'category' => 'required'
			);
		$fields = array(
			'name' => array('required' => '*'),
			'category' => array('type' => 'select', 'options' => $categories, 'required' => '*'),
			'extra_link' => array(), 
			'offer' => array(),'real_value' => array(),
			'min_limit' => array(),'max_limit' => array(),
			'start_date' => array(),'end_date' => array(),
			'status' => array('value' => $c->status, 'type' => 'radio'),
			'excerpt' => array('type' => 'textarea', 'required' => '*'),
			'description' => array('type' => 'textarea'),
			'image' => array('description' => '<img id="image_preview" class="preview" src="/images/noimage.jpg" alt="no image"  onclick="image_upload(\'image\',\'image_preview\');" />'),
			'image_background' => array('description' => '<img id="image_background_preview" class="preview" src="/images/noimage.jpg" alt="no image"  onclick="image_upload(\'image_background\',\'image_background_preview\');" />'),
			'submit' => array('type' => 'submit', 'value' => lang('submit')));

		$validation = new Validation();
		if($validation->run($rules)){
			$c = new Model_Product();
			$c->name = post('name');
			$c->excerpt = post('excerpt');$c->description = cleanhtml(post('description'));
			$c->slug = string::sanitize_url(post('name'));
			$c->extra_link = post('extra_link');
			$c->category = post('category');
			$c->real_value = post('real_value');$c->offer = post('offer');
			$c->start_date = post('start_date');$c->end_date = post('end_date');
			$c->min_limit = post('min_limit');$c->max_limit = post('max_limit');
			$c->status = post('status')?1:0;
			$c->image = post('image'); $c->image_background = post('image_background');			
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}	
		$this->content = new View('product_form');
		$this->content->message = NULL;
		$form = new Form($validation, array('id' => 'create'));
		$form->fields($fields);
		$this->content->form = $form;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('product_form');
		$this->content->message = NULL;
		$enabledisable = array('0' => lang('disable'), '1' => lang('enable'));
		$categories = array('0' => '-');
		if ($fetchs = Model_ProductCategory::fetch(array('category_id' => 0))) 
			foreach($fetchs as $c){
				$categories[$c->id] = $c->name;
				if($s=$c->category())
					foreach ($s as $v) $categories[$v->id] = $c->name." » ".$v->name;
		}
		$rules = array(
			'name' => 'required',
			'category' => 'required'
			);
					
		$validation = new Validation();
		if($validation->run($rules))
		{
			$c = new Model_Product(post('key'));
			$c->name = post('name');
			$c->excerpt = post('excerpt');$c->description = cleanhtml(post('description'));
			$c->slug = string::sanitize_url(post('name'));
			$c->extra_link = post('extra_link');
			$c->category = post('category');
			$c->real_value = post('real_value');$c->offer = post('offer');
			$c->start_date = post('start_date');$c->end_date = post('end_date');
			$c->min_limit = post('min_limit');$c->max_limit = post('max_limit');
			$c->status = post('status')?1:0;
			$c->image = post('image'); $c->image_background = post('image_background');			
			$c->save();
			unset($_POST);
			$this->content->message = lang('success');
		}
		
		$c = new Model_Product(get('edit'));
		$image = ($c->image)?$c->image:'/images/noimage.jpg';
		$image_background = ($c->image_background)?$c->image_background:'/images/noimage.jpg';
		$fields = array(
			'key' => array('type' => 'hidden', 'value' => $c->id),
			'name' => array('value' => $c->name, 'required' => '*'),
			'category' => array('type' => 'select', 'options' => $categories, 'value' => $c->category, 'required' => '*'),
			'extra_link' => array('value' => $c->extra_link),
			'real_value' => array('value' => $c->real_value),			
			'offer' => array('value' => $c->offer),
			'min_limit' => array('value' => $c->min_limit),
			'max_limit' => array('value' => $c->max_limit),
			'start_date' => array('value' => $c->start_date),
			'end_date' => array('value' => $c->end_date),
			'status' => array('value' => $c->status, 'type' => 'radio'),
			'excerpt' => array('type' => 'textarea', 'value' => $c->excerpt, 'required' => '*'),
			'description' => array('type' => 'textarea', 'value' => $c->description),			
			'image' => array('value' => $c->image, 'description' => '<img id="image_preview" class="preview" src="'.$image.'" alt=""  onclick="image_upload(\'image\',\'image_preview\');" />'),
			'image_background' => array('value' => $c->image_background, 'description' => '<img id="image_background_preview" class="preview" src="'.$image_background.'" alt=""  onclick="image_upload(\'image_background\',\'image_background_preview\');" />'),		
			'submit' => array('type' => 'submit', 'value' => lang('save')));
		$form = new Form($validation, array('id' => 'create'));
		$form->fields($fields);
		$this->content->form = $form;
	}
}