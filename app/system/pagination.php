<?php
class Pagination {
	public $ul       =NULL;
	public $total    =NULL;
	public $current  =1;
	public $uri      =NULL;
	public $per_page =NULL;
	public $links    =2;
	public $key      ='[[page]]';
	public $attributes=array('class' => 'pagination','id' => 'pagination');
	public function __construct($total,$uri,$current,$per_page=10,$ul=false) {
		$this->per_page =$per_page;
		$this->total    =$t=ceil($total/$per_page);
		$c              =int($current,1);
		$this->current  =$c>$t?$t:$c;
		$this->uri      =$uri;
		$this->ul       =$ul;
	}
	public function __toString() {
		$r = $this->previous().$this->first().$this->links().$this->last().$this->next();
		return html::tag('div',($this->ul)?html::tag('ul',$r):$r,$this->attributes);
	}
	public function previous() {
		if($this->current>1){
			$r = html::link(str_replace($this->key,$this->current-1,$this->uri),lang('pagination_previous'));
			return ($this->ul)?html::tag('li',$r):$r;
		}
	}
	public function first() {
		if($this->current>$this->links+1){
			$r = html::link(str_replace($this->key,1,$this->uri),lang('pagination_first'));
			return ($this->ul)?html::tag('li',$r):$r;
		}
	}
	public function last() {
		if($this->current+$this->links<$this->total){
			$r = html::link(str_replace($this->key,$this->total,$this->uri),lang('pagination_last'));
			return ($this->ul)?html::tag('li',$r):$r;
		}
	}
	public function next() {
		if($this->current<$this->total){
			$r = html::link(str_replace($this->key,$this->current+1,$this->uri),lang('pagination_next'));
			return ($this->ul)?html::tag('li',$r):$r;
		}
	}
	public function links() {
		$c=$this->current;
		$l=$this->links;
		$u=$this->uri;
		$t=$this->total;
		$s=(($c-$l)>0)?$c-$l:1;
		$e=(($c+$l)<$t)?$c+$l:$t;
		$h='';
		for($i=$s; $i<=$e;++$i) {
			if($c==$i)
				$h.=html::tag('a',$i,array('class' => 'current'));
			else
				$h.=html::link(str_replace($this->key,$i,$u),$i);
		}
		return ($this->ul)?html::tag('li',$h):$h;
	}
}