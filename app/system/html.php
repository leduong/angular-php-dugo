<?php
class html {
	public static function gravatar($email='',$size=80,$alt='Gravatar',$rating='g') {
		return '<img src="http://www.gravatar.com/avatar/'.md5($email)."?s=$size&d=wavatar&r=$rating\" alt=\"$alt\" />";
	}
	public static function email($email) {
		$s='';
		foreach(str_split($email) as $l) {
			switch(rand(1,3)) {
				case 1:
					$s.='&#'.ord($l).';'; break;
				case 2:
					$s.='&#x'.dechex(ord($l)).';'; break;
				case 3: $s.=$l;
			}
		}
		return $s;
	}
	static function ul_from_array(array $ul,array $attributes=array()) {
		$h='';
		foreach($ul as $k => $v)
			if(is_array($v))
				$h.=self::tag('li',$k.self::ul_from_array($v));
			else
				$h.=self::tag('li',$v);
		return self::tag('ul',$h,$attributes);
	}
	public static function attributes(array $attributes=array()) {
		if(!$attributes)
			return;
		asort($attributes);
		$h='';
		foreach($attributes as $k => $v)
			$h.=" $k=\"".h($v).'"';
		return $h;
	}
	public static function tag($tag,$text='',array $attributes=array()) {
		return "\n<$tag".self::attributes($attributes).($text===0?' />':">$text</$tag>");
	}
	public static function link($url,$text='',array $attributes=array()) {
		return self::tag('a',$text,($attributes+array('href' => site_url($url))));
	}
	public static function select($name,array $options=array(),$selected=NULL,array $attributes=array()) {
		$h='';
		foreach($options as $k => $v) {
			$a=array('value' => $k);
			if($selected&&in_array($k,(array) $selected))
				$a['selected']='selected';
			$h.=self::tag('option',$v,$a);
		}
		return self::tag('select',$h,$attributes+array('name' => $name));
	}
	public static function datetime($ts=NULL,$name='datetime',$class='datetime') {
		$ts=new Time($ts);
		$t=$ts->getArray($ts);
		$e[]=self::month_select($t['month'],$name);
		foreach(lang('time_units') as $k => $v)
			$e[]=html::tag('input',0,array('name' => "{$name}[$k]",'type' => 'text','value' => isset ($t[$k])?$t[$k]:0,'class' => $k));
		return vsprintf(lang('html_datetime'),$e);
	}
	public static function month_select($month=1,$name='datetime',$class='month') {
		return html::select("{$name}[month]",lang('html_months'),$month,array('class' => $class));
	}
	public static function bbcode($t) {
		$bbcode = array(
			'/\[b\](.*?)\[\/b\]/is',
			'/\[i\](.*?)\[\/i\]/is',
			'/\[u\](.*?)\[\/u\]/is',
			'/\[url\=(.*?)\](.*?)\[\/url\]/is',
			'/\[url\](.*?)\[\/url\]/is',
			'/\[center\](.*?)\[\/center\]/is',
			'/\[img\=(.*?)\](.*?)\[\/img\]/is',
			'/\[img\](.*?)\[\/img\]/is',
			'/\[code\](.*?)\[\/code\]/is',
			'/\[quote\](.*?)\[\/quote\]/is',
			'/\[big\](.*?)\[\/big\]/is',
			'/\[small\](.*?)\[\/small\]/is',
			'/\[youtube\]http:\/\/youtu.be\/(.*?)\[\/youtube\]/is',
			);
		$unbbcode = array(
			'<strong>$1</strong>',
			'<em>$1</em>',
			'<u>$1</u>',
			'<a href="$1" rel="nofollow" title="$2 - $1">$2</a>',
			'<a href="$1" rel="nofollow" title="$1">$1</a>',
			'<div style="text-align: center;">$1</div>',
			'<img src="$1" alt="$2" />',
			'<img src="$1" alt="" />',
			'<pre class="code">$1</pre>',
			'<blockquote>$1</blockquote>',
			'<big>$1</big>',
			'<small>$1</small>',
			'<iframe width="560" height="315" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
			);
		return preg_replace($bbcode, $unbbcode, nl2br($t));
	}
}