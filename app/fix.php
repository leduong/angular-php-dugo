<?php
require ('init.php');

// Init database
$sql = config('database');
// $sql['sqlite'] or $sql['mysql']
$db = new DB($sql['mysql']);
registry('db', $db);
ORM::$db = $db;

$db->query("TRUNCATE tags_occurrence;TRUNCATE tags");

$tags = array();
if ($ar = Model_TagsAuto::fetch()) foreach ($ar as $a) {
	$tags[$a->slug] = $a->name;
}
if ($ar = Model_TagsGroup::fetch()) foreach ($ar as $a) {
	$tags[$a->slug] = $a->name;
}
$tags = array_unique($tags);

if ($ar = Model_City::fetch()) foreach ($ar as $a) {
	Model_Tags::get_or_insert($a->name);
	echo $a->name."\n";
	# code...
}

if ($ar = Model_Group::fetch()) foreach ($ar as $c) {
	// Model_TagsAuto && Model_Tags
	$ar = explode(",", implode(",", array($c->name,$c->long_name)));
	foreach ($ar as $v) Model_Tags::get_or_insert($v);

	$del = Model_TagsAuto::fetch(array('group_id' => $c->id));
	if ($del) foreach ($del as $d) $d->delete();
	foreach ($ar as $v) Model_TagsAuto::get_or_insert($c->name,$c->id);

	// Model_TagsGroup
	$del = Model_TagsGroup::fetch(array('group_id' => $c->id));
	if ($del) foreach ($del as $d) $d->delete();
	$tag_id = 0;
	$tag_id = Model_TagsGroup::get_or_insert($c->name,$tag_id,$c->id);
	$ar = explode(",", $c->long_name);
	if($ar)foreach ($ar as $v) if ($tag=trim($v)) Model_TagsGroup::get_or_insert($tag,$tag_id,$c->id);
	echo $c->name."\n";
	# code...
}

if ($ms = Model_Messages::fetch()) foreach ($ms as $m) {
	$message = string::slug($m->message);
	$b = explode(",", $m->tag);
	echo "\n\n".$m->id." -> ";
	foreach ($tags as $k => $v) if (strpos($message, $k) !== false) {
		$b[] = $v;
		echo "$v...";
	}
	$m->tag = implode(",", array_unique($b));
	$m->save();
	Model_Messages::rebuild($m->id);
}