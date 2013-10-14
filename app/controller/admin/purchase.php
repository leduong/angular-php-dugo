<?php
class Controller_Admin_Purchase extends Controller
{
	public function index()
	{
		return $this->lists();
	}
	public function delete()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$selected = post('selected');
		if (isset($selected)) foreach($selected as $s){
			$c = new Model_ProductPurchase($s);
			$c->delete();
		}
		redirect(HTTP_SERVER.'/admin/purchase/lists');
	}
	public function lists()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$limit=100;
		$page=((int)get('page')>1)?(int)get('page'):1;
		$offset=$limit*($page-1);
		$total = Model_ProductPurchase::count();
		$order = array();
		$this->content = new View('purchase');
		$this->content->message = $this->content->form = NULL;
		
		$pagination = new Pagination(
			$total,
			HTTP_SERVER."/admin/purchase/lists/page/[[page]].html",
			$page,
			$limit);
		$this->content->purchases = Model_ProductPurchase::fetch(
			NULL,
			$limit,
			$offset,
			array('product_id' => 'DESC', 'id' => 'DESC'));
		$this->content->pagination = $pagination;
		if ($ar = Model_Order::fetch()) foreach($ar as $a) $order[$a->id] = $a->name;
		$this->content->order = $order;
	}
	public function edit()
	{
		if (false==controller_admin_index::checklogin()) redirect(HTTP_SERVER.'/admin/login');
		$this->content = new View('shop_form');
		$this->content->message = NULL;
		if ($c = new Model_ProductPurchase(get('edit'))){
			$product_id = new Model_Product($c->product_id);
			$user = new Model_User($c->user_id);
			$order = array();
			if ($ar = Model_Order::fetch()) foreach($ar as $a) $order[$a->id] = $a->name;

			$validation = new Validation();
			$rules = array('status' => 'required');
			if($validation->run($rules)){
				$c->status = post('status');
				$c->save();
				unset($_POST);
				$this->content->message = lang('success');
			}

			$fields = array(
				'name' => array('value' => $user->full_name, 'attributes' => array('disabled' => 'disabled')),
				'coupon' => array('value' => $product_id->name, 'attributes' => array('disabled' => 'disabled')),
				'status' => array('type' => 'select', 'options' => $order, 'value' => $c->status),
				'submit' => array('type' => 'submit', 'value' => lang('save')));
			$form = new Form($validation, array('id' => 'create'));
			$form->fields($fields);
			$this->content->form = $form;
		}//end if C
	}
}