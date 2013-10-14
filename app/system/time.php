<?php
class Time extends DateTime {
	public function __construct($time='now',DateTimeZone $timezone=NULL) {
		if(is_int($time))
			$time="@$time";
		if(is_array($time))
			$time=self::fromArray($time);
		if($timezone)
			parent::__construct($time,$timezone);
		else
			parent::__construct($time);
	}
	public function diff($now='NOW',$absolute=FALSE) {
		if(!($now instanceOf DateTime))
			$now=new Time($now);
		return parent::diff($now,$absolute);
	}
	public function getSQL() {
		return $this->format('Y-m-d H:i:s');
	}
	public function difference($d='NOW',$l=1) {
		$d=$this->diff($d);
		$u=array('y' => 'năm','m' => 'tháng','d' => 'ngày','h' => 'giờ','i' => 'phút','s' => 'giây');
		$r=array();
		foreach($u as $k => $n) {
			$v=$d->$k;
			if($v)
				$r[]="$v $n";// EN.($v>1?'s':'');
			if(count($r)==$l)
				return implode(', ',$r);
		}
	}
	public function humanFriendly($format='M j, Y \a\t g:ia') {
		$diff=$this->diff();
		$t=$this->getTimestamp();
		if(!$diff->d) {
			$s=$this->difference();
			return $t>time()?"$s ago":"in $s";
		}
		return $this->format($format);
	}
	public function getArray() {
		$ts=$this->getTimestamp();
		return array('year' => date('Y',$ts),'month' => date('m',$ts),'day' => date('d',$ts),'hour' => date('H',$ts),'minute' => date('i',$ts),'second' => date('s',$ts));
	}
	public static function fromArray(array $data) {
		foreach(array('year','month','day','hour','minute','second') as $k)
			$$k=isset ($data[$k])?$data[$k]:0;
		return mktime($hour,$minute,$second,$month,$day,$year);
	}
	public static function show($time) {
		$t=new Time($time);
		return $t->humanFriendly();
	}
}