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
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(80) DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_Tags extends APCORM
{
  public static $t = 'tags'; // Table
  public static $k = 'id'; // Default 'id' , PRIMARY KEY AUTO_INCREMENT
  public static $f = 'tag_id'; // FOREIGN KEY

  public static $h = array('occurrence' => 'Model_TagsOccurrence'); // Relations Ship
  public static function get_or_insert($name,$slug = NULL){
    $slug = (is_null($slug))?string::slug($name):$slug;
    if($fetch=self::fetch(array('slug' => $slug),1)){
      return $fetch[0]->id;
    }
    else{
      $t = new Model_Tags();
      $t->name = trim($name);
      $t->slug = $slug;
      $t->save();
      return $t->id;
    }
  }
} // END class
