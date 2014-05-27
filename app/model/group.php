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

CREATE TABLE IF NOT EXISTS `city` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) DEFAULT NULL,
	`slug` varchar(32) DEFAULT NULL,
	`enable` tinyint(1) DEFAULT NULL,
	`sort` int(11) DEFAULT NULL,
	`country_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `country_id` (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_Group extends APCORM
{
	public static $t = 'groups';
	public static $f = 'group_id'; // FOREIGN KEY
	public static $h = array(
		'tagsgroup' => 'Model_TagsGroup',
		'tagsauto'  => 'Model_TagsAuto'
		);
	public static function rebuild($id=0)
	{
		$g = new Model_Group($id);
		if ($g){
			// Model_TagsAuto && Model_Tags
			$tags = explode(",", implode(",", array($g->name,$g->long_name)));
			foreach ($tags as $v) Model_Tags::get_or_insert($v);

			$del = Model_TagsAuto::fetch(array('group_id' => $g->id));
			if ($del) foreach ($del as $d) $d->delete();
			foreach ($tags as $v) Model_TagsAuto::get_or_insert($g->name,$g->id);

			// Model_TagsGroup
			$del = Model_TagsGroup::fetch(array('group_id' => $g->id));
			if ($del) foreach ($del as $d) $d->delete();
			$tag_id = 0;
			$tag_id = Model_TagsGroup::get_or_insert($g->name,$tag_id,$g->id);
			// Tag peer
			$tags = explode(",", $g->long_name);
			if($tags)foreach ($tags as $v) if ($tag=trim($v)) Model_TagsGroup::get_or_insert($tag,$tag_id,$g->id);
			// Tag parrent (local and tag)
			$arr = array();
			foreach (@explode(',',$g->tag) as $a) $arr[string::slug($a)] = trim($a);
			if($ar = @explode(',',$g->local)) while (count($ar)>0) {
				$arr[string::slug(implode(",", $ar))] = trim($ar[0]);
				$ar = array_slice($ar, 1);
			}
			if($arr)foreach (array_unique($arr) as $k => $v) Model_TagsGroup::get_or_insert($v,$tag_id,$g->id,0,$k);
			//die(var_dump($arr));
		}
	}
} // END class
