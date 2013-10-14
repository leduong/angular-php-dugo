<?php
class Validation {
	public $errors=array();
	public $error_prefix='';
	public $error_suffix='';
	public $token=TRUE;
	public function run(array $fields) {
		if($_POST) {
			foreach($fields as $f => $r) {
				$r=explode('|',$r);
				if(!in_array('required',$r)&&!post($f)) continue;
				$this->_rules($f,post($f),$r);
				$this->validate_token();
			}
			if(!$this->errors) return 1;
		}
		$this->create_token();
	}
	protected function _rules($f,$d,array $rules) {
		foreach($rules as $r) {
			list($r,$p)=$this->_parse_rule($r);
			if(method_exists($this,$r))
				$o=$this->$r($f,$d,$p);
			else
				$o=$r($d);
			if($o===FALSE) break;
			if($o!==TRUE) $_POST[$f]=$o;
		}
	}
	protected function _parse_rule($rule) {
		$r=$rule;
		$p=NULL;
		if(strpos($r,'[')!==FALSE) {
			preg_match('/(\w+)\[(.*?)\]/i',$r,$m);
			$r=$m[1];
			$p=$m[2];
		}
		return array($r,$p);
	}
	protected function _set_error($field,$name,array $params=array()) {
		$this->errors[$field]=vsprintf(lang('validation_'.$name),array_merge(array(lang($field)),$params));
	}
	public function set_error($field,$error) {
		$this->errors[$field]=$error;
	}
	public function display_errors($prefix='',$suffix='') {
		if(!$this->errors)
			return;
		$h='';
		foreach($this->errors as $e)
			$h.=($prefix?$prefix:$this->error_prefix).$e.($suffix?$suffix:$this->error_suffix)."\n\n";
		return $h;
	}
	public function error($field,$prefix=TRUE) {
		if(isset ($this->errors[$field])) {
			if($prefix)
				return $this->error_prefix.$this->errors[$field].$this->error_suffix;
			return $this->errors[$field];
		}
	}
	public function string($field,$data) {
		if($data=trim(str($data))) return $data;
		$this->_set_error($field,'required');
		return FALSE;
	}
	public function required($field,$data) {
		if($data) return TRUE;
		$this->_set_error($field,'required');
		return FALSE;
	}
	public function set($field,$data) {
		if(isset ($_POST[$field])) return TRUE;
		$this->_set_error($field,'set');
		return FALSE;
	}
	public function alpha($field,$word) {
		if(preg_match("/^([a-z])+$/i",$word)) return TRUE;
		$this->_set_error($field,'alpha');
		return FALSE;
	}
	public function alpha_numeric($field,$data) {
		if(preg_match("/^([a-z0-9])+$/i",$data)) return TRUE;
		$this->_set_error($field,'alpha_numeric');
		return FALSE;
	}
	public function numeric($field,$number) {
		if(is_numeric($number)or($number==0)) return TRUE;
		$this->_set_error($field,'numeric');
		return FALSE;
	}
	public function matches($field,$data,$field2) {
		if(isset ($_POST[$field2])&&$data===post($field2)) return TRUE;
		$this->_set_error($field,'matches',array($field2));
		return FALSE;
	}
	public function min_length($field,$data,$length) {
		if(mb_strlen($data)>=$length) return TRUE;
		$this->_set_error($field,'min_length',array($length));
		return FALSE;
	}
	public function max_length($field,$data,$length) {
		if(mb_strlen($data)<=$length) return TRUE;
		$this->_set_error($field,'max_length',array($length));
		return FALSE;
	}
	public function exact_length($field,$data,$length) {
		if(mb_strlen($data)==$length) return TRUE;
		$this->_set_error($field,'exact_length',array($length));
		return FALSE;
	}
	public function valid_email($field,$email) {
		if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$email)) return TRUE;
		$this->_set_error($field,'valid_email');
		return FALSE;
	}
	public function valid_base64($field,$data) {
		if(!preg_match('/[^a-zA-Z0-9\/\+=]/',$data)) return TRUE;
		$this->_set_error($field,'valid_base64');
		return FALSE;
	}
	public function captcha($field,$data) {
		if($data==session('captcha')) return TRUE;
		$this->_set_error($field,'invalid_captcha');
		return FALSE;
	}
	public function create_token() {
		if($this->token&&class_exists('session',0)) Session::token();
	}
	public function validate_token() {
		if(!$this->token||!class_exists('session',0)) return TRUE;
		if(Session::token(post('token'))) return TRUE;
		$this->_set_error('token','invalid_token');
		return FALSE;
	}
}