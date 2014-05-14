<?php
class Controller_Welcome extends Controller
{
	public function index(){
		if(AJAX_REQUEST){
			$tpl = new Template("index");
			echo $tpl->make();
			exit;
		} else $this->content = '';
	}
}