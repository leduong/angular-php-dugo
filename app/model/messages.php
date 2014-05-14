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


--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
	`id` int(12) NOT NULL AUTO_INCREMENT,
	`uid` int(32) NOT NULL,
	`message` text COLLATE utf8_unicode_ci NOT NULL,
	`tag` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
	`type` varchar(16) CHARACTER SET latin1 NOT NULL,
	`value` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`public` int(11) NOT NULL,
	`likes` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_Messages extends APCORM
{
	public static $t = 'messages'; // Table
	public static $k = 'id'; // Default 'id' , PRIMARY KEY AUTO_INCREMENT
	public static $f = 'msg_id'; // FOREIGN KEY

	public static $h = array(
		'like'       => 'Model_Likes',
		'meta'       => 'Model_MessagesMeta',
		'comments'   => 'Model_Comments',
		'occurrence' => 'Model_TagsOccurrence'
		); // Relations Ship
	//public static $h = array(); // Relations Ship
	public static function rebuild($id=0)
	{
		$meta = $arr = array();
		$m = new Model_Messages($id);
		if ($m){
			// Tags
			$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id));
			if ($mt) foreach($mt as $v) $meta[$v->type] = trim($v->value);
			if (isset($meta["local"])){
				if($ar = @explode(',',$meta["local"])) while (count($ar)>0) {
					$arr[string::slug(implode(",", $ar))] = trim($ar[0]);
					$ar = array_slice($ar, 1);
				}
			}
			if(isset($arr)){
				if($tags = @explode(',',$m->tag)) foreach ($tags as $t) $arr[string::slug($t)] = trim($t);
				$del = Model_TagsOccurrence::fetch(array('msg_id' => $m->id));
				if ($del) foreach ($del as $d) $d->delete();
				foreach ($arr as $key => $value) {
					$tag_id = Model_Tags::get_or_insert($value,$key);
					if ($tag_id){
						$count = Model_TagsOccurrence::count(array('msg_id' => $m->id, 'tag_id' => $tag_id));
						if ($count<1){
							$tags_occurrence         = new Model_TagsOccurrence();
							$tags_occurrence->msg_id = $m->id;
							$tags_occurrence->tag_id = $tag_id;
							$tags_occurrence->save();
						}
					}
				}
			}
			// end Tags
		}
	}
} // END class