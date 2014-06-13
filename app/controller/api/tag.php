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
/**
* Controller_Api_Group class
*
* @package Controller_Welcome_Index
* @author [author] <[email]>
* @filename {{app}}/controller/api/group.php
* @template {{app}}/view/api/group.php
**/
class Controller_Api_Tag extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in    = input();
				$array = array();
				if(isset($in->slug)) {
					$slug  = str_replace('.html','',$in->slug);
					$all   = Model_TagsGroup::get_query($slug,1);
					$where = implode(' OR ', $all);

					if ($fetch = Model_Tags::fetch(array('slug' => $slug),1)) {
						$tag        = end($fetch);
						$realestate = Cache::get('realestate.'.$slug);
						$status     = Cache::get('status.'.$slug);
						if(!$realestate||!$status){ // First count
							$db = registry('db');
							$_q = "SELECT COUNT(DISTINCT messages.id)
									FROM tags, tags_occurrence, messages
									WHERE messages.type = '%s' AND
									messages.id = tags_occurrence.msg_id AND
									tags.id = tags_occurrence.tag_id AND tags.slug = '%s'";
							$status     = $db->column(sprintf($_q,'status',$slug));
							$realestate = $db->column(sprintf($_q,'realestate',$slug));
							//die(var_dump($realestate));
							Cache::set('realestate.'.$slug,$realestate);
							Cache::set('status.'.$slug,$status);
						}
						$array = $tag->to_array();
						$array['follows'] = Model_Follows::count(array('tag_id' => $tag->id));
						if ($u = @unserialize(cookie::get('user'))){
							$follow = Model_Follows::fetch(array('by' => $u['idu'], 'tag_id' => $tag->id));
							$array['followed'] = ($follow)?1:0;
						} else $array['followed'] = 0;

						$all = Model_TagsGroup::get_query($slug,1);
						$where = implode(' OR ', $all);
						//die($where);
						if($fetch = Model_Group::fetch(array($where),1)){
							$a = end($fetch);
							$a->hits++;
							$a->save();
							if ($a->tag){
								$tags = array();
								if($ar = @explode(',',$a->tag)) foreach ($ar as $b) if(trim($b)) $tags[string::slug($b)] = trim($b);
								$array["tags"] = $tags;
							}
							if ($a->local){
								$local = array();
								if($ar = @explode(',',$a->local)) while (count($ar)>0) {
									$local[string::slug(implode(",", $ar))] = trim($ar[0]);
									$ar = array_slice($ar, 1);
								}
								$array["locals"] = $local;
							}
							$group            = $a->to_array();
							$user             = new Model_User($group["by"]);
							$array['by_name'] = (isset($user->first_name))?$user->first_name:NULL;
							$array['name']    = $group["name"];
							$group['map']     = explode(",",$group["map"]);
							$array['type']    = "group";
							$array            = array_merge($group, $array);
						}
						else if($fetch = Model_City::fetch(array('slug' => $slug),1)){
							$a             = end($fetch);
							$same = Model_TagsGroup::get_array($slug);
							$array['long_name'] = implode(", ", $same);
							$array['map']  = explode(",",$a->map);
							$array['type'] = "city";
						}
						else if($fetch = Model_District::fetch(array('slug' => $slug),1)){
							$a             = end($fetch);
							$city          = new Model_City($a->city_id);
							$array['map']  = explode(",",$a->map);
							$array['type'] = "city";
							$array['name'] = $a->name.", ".$city->name;
						}
						else if($fetch = Model_Zipcode::fetch(array('slug' => $slug),1)){
							$a             = end($fetch);
							$array['map']  = explode(",",$a->map);
							$array['type'] = "city";
							$array['name'] = $a->full_name;
						} else {
							$same = Model_TagsGroup::get_array($slug,1);
							if (count($same)>1) $array['long_name'] = implode(", ", $same);
							$array['type'] = "topic";
						}
						Response::json(array(
							'data' => $array,
							'stats' => array('status' => $status, 'realestate' => $realestate)
							));
					} else Response::json(array('data' => array(), 'stats' => array('status' => 0, 'realestate' => 0)),404);
				} else Response::json(array('data' => array(), 'stats' => array('status' => 0, 'realestate' => 0)),404);
			}
		}
		exit();
	}
}
