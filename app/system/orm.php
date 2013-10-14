<?php
class ORM {
	public $d=array(),$r=array(),$c=array(),$l,$s;
	public static $db,$t,$k='id',$f,$b=array(),$h=array(),$hmt=array(),$o=array(),$cache=0;
	public function __construct($id=0) {
		if(!$id) return;
		if(is_numeric($id)) $this->d[static::$k]=$id;
		else{
			$this->d=(array) $id;
			$this->l=1;
		}
		$this->s=1;
	}
	public function k() {
		return isset ($this->d[static::$k])?$this->d[static::$k]:NULL;
	}
	public function to_array() {
		if($this->load()) return $this->d;
	}
	public function set($a) {
		foreach($a as $c => $v) $this->__set($c,$v);
		return $this;
	}
	public function __set($k,$v) {
		if(!array_key_exists($k,$this->d) OR $this->d[$k]!==$v) {
			$this->d[$k]=$v;
			$this->c[$k]=$k;
			$this->s=0;
		}
	}
	public function __get($k) {
		$this->load();
		return array_key_exists($k,$this->d)?$this->d[$k]:$this->r($k);
	}
	public function __isset($k) {
		if($this->load()) return (array_key_exists($k,$this->d)||isset ($this->r[$k]));
	}
	public function __unset($k) {
		$this->load();
		unset ($this->d[$k],$this->c[$k],$this->r[$k]);
	}
	public function reload() {
		$k=$this->k();
		$this->d=$this->c=$this->r=array();
		$this->l=0;
		$this->d[static::$k]=$k;
		return $this->load();
	}
	public function clear() {
		$t=$this;
		$t->d=$t->r=$t->c=array();
		$t->l=$t->s=0;
	}
	public function load() {
		$t=$this;
		if($t->l)return 1;
		$k=static::$k;
		if(empty ($t->d[$k]))return 0;
		$id=$t->d[$k];
		if(!($r=static::cache_get($t::$t.$id)))
			if($r=self::select('row','*',$t,array($k => $id))) static::cache_set($t::$t.$id,$r);
			if($r) {
				$t->d=(array) $r;
				return $t->s=$t->l=1;
			}
			else $t->clear();
	}
	public function r($a) {
		$m=isset (static::$b[$a])?static::$b[$a]:static::$h[$a];
		$t=$this;
		if(isset ($t->r[$a])) return $t->r[$a];
		return $t->r[$a]=new $m(isset (static::$b[$a])?$t->d[$m::$f]:self::select('column',$m::$k,$m,array(static::$f => $t->k())));
	}
	public function __call($m,$a) {
		$f='fetch';
		if(substr($m,0,6)==='count_') {
			$f='count';
			$m=substr($m,6);
		}
		$a=$a+array(array(),0,0,array());
		$a[0][static::$f]=$this->k();
		if(isset (static::$h[$m])) {
			$c=static::$h[$m];
			return $c::$f($a[0],$a[1],$a[2],$a[3]);
		}
		else return $this->hmt($m,$a);
	}
	public function hmt($m,$a) {
		$c=static::$hmt[$m];
		$t=key($c);
		$m=current($c);
		return self::objects($m::$f,$m,$t,array($this::$f => $this->k())+$a[0],$a[1],$a[2],$a[3]);
	}
	public static function objects($k=0,$c=0,$m=0,$w=0,$l=0,$o=0,$ord=array()) {
		if($r=self::select('fetch',$k,$m,$w,$l,$o,$ord)) {
			$c=$c?:get_called_class();
			foreach($r as $k => $v) $r[$k]=new $c($v);
		}
		return $r;
	}
	public static function select($f,$c,$m=0,$w=array(),$l=0,$o=0,$ord=array()) {
		$m=$m?:get_called_class();
		$ord=$ord+static::$o;
		if($f!='fetch') {
			$l=$o=0;
			$ord=array();
		}
		$v=DB::select(($c?$c:'COUNT(*)'),$m::$t,$w,$l,$o,$ord);
		return static::$db->$f($v[0],$v[1],($c=='*'?NULL:0));
	}
	public static function fetch(array $where=NULL,$limit=0,$offset=0,array $order_by=array()) {
		return self::objects(static::$k,0,0,$where,$limit,$offset,$order_by);
	}
	public static function count(array $where=NULL) {
		return self::select('column',0,0,$where);
	}
	public function save() {
		static::before();
		$t=$this;
		if(!$t->c) return $t;
		$d=array();
		foreach($t->c as $c) $d[$c]=$t->d[$c];
		if(v($t->d[$t::$k])) $t->update($d);
		else $t->insert($d);
		$t->c=array();
		static::after();
		return $t;
	}
	protected function insert(array $data) {
		$t=$this;
		$id=static::$db->insert($t::$t,$data);
		$t->d[$t::$k]=$id;
		$t->l=$t->s=1;
		return $id;
	}
	protected function update(array $data) {
		$t=$this;
		$t->s=1;
		$r=static::$db->update($t::$t,$data,array($t::$k => $t->d[$t::$k]));
		static::cache_delete($t::$t.$t->d[$t::$k]);
		return $r;
	}
	public function delete($id=0) {
		$id=$id?:$this->k();
		$c=$this->delete_relations();
		$c+=self::$db->delete('DELETE FROM '.$this::$t.' WHERE '.static::$k.'=?',array($id));
		static::cache_delete($this::$t.$id);
		$this->clear();
		return $c;
	}
	public function delete_relations() {
		$c=0;
		foreach(static::$h as $a => $m)
			foreach($this->$a() as $o)
				$c+=$o->delete();
		return $c;
	}
	public static function before() {}
	public static function after() {}
	public static function cache_set($k,$v) {}
	public static function cache_get($k) {}
	public static function cache_delete($k) {}
	public static function cache_exists($k) {}
}