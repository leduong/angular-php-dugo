<?php
class Form extends View {
	public function __construct($validation=NULL,array $attributes=array(),$view='form') {
		parent::__construct($view);
		$this->set(array('attributes' => $attributes,'validation' => $validation));
	}
	public function fields(array $fields) {
		$a='attributes';
		$t='type';
		foreach($fields as $f =>&$o) {
			$o=$o+array('label' => lang($f),'value' => post($f),$t => 'text',$a => array('id' => $f,'name' => $f));
			if($o[$t]!='select'&&$o[$t]!='textarea') {
				$o[$a][$t]=$o[$t];
				$o[$t]='input';
			}
		}
		$this->fields=$fields;
	}
	public function __toString() {
		return html::tag('form',parent::__toString(),$this->attributes+array('method' => 'post'));
	}
}