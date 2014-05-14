<?php
require ('init.php');

// Init database
$sql = config('database');
// $sql['sqlite'] or $sql['mysql']
$db = new DB($sql['mysql']);
registry('db', $db);
ORM::$db = $db;

if ($ar = Model_District::fetch()) foreach ($ar as $a) {
	$slug = string::slug($a->name);
	if(($substr=substr($slug,0,2))&& in_array($substr,array('q-','h-'))){
		$slug = substr($slug,2,strlen($slug));
	}
	$a->slug = $slug;
	$a->save();
	echo "$slug\n";
}

if ($ar = Model_Zipcode::fetch()) foreach ($ar as $a) {
	$d = new Model_District($a->district_id);
	$c = new Model_City($a->city_id);

	$a->name = str_replace("P 0", "P ", $a->name);
	$slug = string::slug($a->name);
	if(($substr=substr($slug,0,2))&& in_array($substr,array('p-','x-'))){
		$slug = substr($slug,2,strlen($slug));
	}
	$a->slug = $slug."-".$d->slug."-".$c->slug;
	$a->full_name = $a->name.", ".$d->name.", ".$c->name;
	$a->save();
	echo $a->slug."\n";
	# code...
}