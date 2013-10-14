<?php
class GD {
	public static function thumbnail($f,$w=80,$h=80,$q=80,$m=false) {
		$d=IMAGE.$w."x$h/";
		$n=substr(basename($f),0,strrpos(basename($f),'.')).'.jpg';
		if(is_file($d.$n) OR !dir::usable($d) OR !is_file($f) OR !($i=self::open($f))) return;
		if(imagejpeg(self::resize($i,$w,$h,$m),$d.$n,$q)) return $d.$n;
	}
	public static function open($f) {
		if(is_file($f)&&($e=pathinfo($f,PATHINFO_EXTENSION))&&($x='imagecreatefrom'.(strtolower($e)=='jpg'?'jpeg':strtolower($e)))&&($i=$x($f))&&is_resource($i)) return $i;
	}
	public static function resize($i,$w,$h,$m=false) {
		$x=imagesx($i);
		$y=imagesy($i);
		$s=(min($w,$h)<=200)?max($w/$x,$h/$y):min($w/$x,$h/$y);
		if ($s>1){
			$w=$x;$h=$y;
			$n=imagecreatetruecolor($w,$h);
			self::alpha($n);
			imagecopyresampled($n,$i,0,0,0,0,$w,$h,$x,$y);
		}
		elseif(min($w,$h)>200){
			$w=$x*$s;$h=$y*$s;
			$n=imagecreatetruecolor($w,$h);
			self::alpha($n);
			imagecopyresampled($n,$i,0,0,0,0,$w,$h,$x,$y);
		}
		else{
			$n=imagecreatetruecolor($w,$h);
			self::alpha($n);
			imagecopyresampled($n,$i,(int)(($w-($x*$s))/2),(int)(($h-($y*$s))/2),0,0,($x*$s),($y*$s),$x,$y);
		}
		return ($m==false || min($w,$h)<=200)?$n:self::watermark($n,$w,$h);
	}
	public function watermark($n,$w,$h,$watermark = 'images/watermark.png',$p = 'centercenter') {
		if(!is_file(ROOT_PATH.$watermark) OR !($i=self::open(ROOT_PATH.$watermark))) return $n;
		$fx=imagesx($i);$fy=imagesy($i);
		switch($p) {
			case 'topleft':$x=0;$y=0;break;
			case 'topright':$x=$w-$fx;$y=0;break;
			case 'bottomleft':$x=0;$y=$h-$fy;break;
			case 'bottomright':$x=$w-$fx;$y=$h-$fy;break;
			case 'centercenter':$x=($w-$fx)/2;$y=($h-$fy)/2;break;
		}
		imagecopy($n,$i,$x,$y,0,0,$fx,$fy);
		return $n;
	}
	public static function alpha($i) {
		imagecolortransparent($i,imagecolorallocate($i,0,0,0));
		imagesavealpha($i,true);
	}

	public static function header($ext) {
		headers_sent()||header('Content-type: image/'.$ext);
	}
}
?>