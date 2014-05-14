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
class Model_TagsAuto extends APCORM
{
	public static $t = 'tags_auto'; // Table
	public static function get_or_insert($name, $group_id = 0, $hits = 0){
		$name = trim($name);
		$slug = string::slug($name);
		if($t=self::fetch(array('slug' => $slug),1)){
			return $t[0]->id;
		}
		else{
			$t           = new Model_TagsAuto();
			$t->name     = $name;
			$t->slug     = $slug;
			$t->hits     = (int)$hits;
			$t->group_id = $group_id;
			$t->save();
			return $t->id;
		}
	}
} // END class