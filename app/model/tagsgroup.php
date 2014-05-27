<?php
/*
 *
 * Copyright 2013 Le Duong <du@leduong.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */


/*** for MySQL
CREATE TABLE IF NOT EXISTS `tags_auto` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(80) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_TagsGroup extends APCORM
{
	public static $t = 'tags_group'; // Table
	//public static $k = 'id'; // Default 'id' , PRIMARY KEY AUTO_INCREMENT
	public static $f = 'tag_id'; // FOREIGN KEY

	public static $h = array(
		'tags'       => 'Model_TagsGroup'
		); // Relations Ship
	//public static $h = array(); // Relations Ship
	public static function get_or_insert($name, $tag_id=0, $group_id=0, $peer=1, $slug=NULL){
		$name = trim($name);
		$slug = (is_null($slug))?string::slug($name):$slug;
		if ($name&&$slug){
			$t           = new Model_TagsGroup();
			$t->name     = $name;
			$t->slug     = $slug;
			$t->peer     = (int)$peer;
			$t->tag_id   = $tag_id;
			$t->group_id = $group_id;
			$t->save();
			return $t->id;
		}
	}

	public static function get_query($name){
		$ar = self::get_all($name);
		$arr = array();
		if ($ar) foreach ($ar as $k => $v) $arr[]  = "`slug` = '".$k."'";
		return $arr;
	}

	public static function get_array($name){
		$ar = self::get_all($name);
		$arr = array();
		if ($ar) foreach ($ar as $k => $v) $arr[]  = $v;
		return $arr;
	}

	public static function get_slug($name){
		$ar = self::get_all($name);
		$arr = array();
		if ($ar) foreach ($ar as $k => $v) $arr[]  = $k;
		return $arr;
	}

	public static function get_all($name){
		$slug  = string::slug($name);
		$fetch = Model_TagsGroup::fetch(array('slug' => $slug));
		if ($fetch){
			$ar = array();
			foreach ($fetch as $t) {
				if($t->tag_id==0){
					$t->load();
					$tags = $t->tags();
					$ar[$t->slug] = $t->name;
					if($tags)foreach ($tags as $a) if($a->slug&&$a->name) $ar[$a->slug] = $a->name;
				} elseif ($t->peer==1) {
					$g = new Model_TagsGroup($t->tag_id);
					if (isset($g->id)){
						$g->load();
						$tags = $g->tags();
						$ar[$g->slug] = $g->name;
						if($tags)foreach ($tags as $a) if($a->slug&&$a->name) $ar[$a->slug] = $a->name;
					}
				} else {
					$ar[$t->slug] = $t->name;
				}
			}
			return $ar;
		}
		return array("$slug" => "$name");
	}
} // END class