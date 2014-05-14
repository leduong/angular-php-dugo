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
class Controller_Api_Group extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				if(isset($in->slug)) {
					$slug = str_replace('.html','',$in->slug);
					if($fetch = Model_Group::fetch(array('slug' => $slug),1)){
						$group = $fetch[0]->to_array();
						$group['map'] = explode(",",$group["map"]);

						if ($tag = Model_Tags::fetch(array('slug' => $slug),1)) $tag_id = $tag[0]->id;

						if ($u = @unserialize(cookie::get('user'))){
							$follow = Model_Follows::fetch(array('by' => $u['idu'], 'tag_id' => $tag_id));
							if ($follow) $group['followed'] = 1;
						} else $group['followed'] = 0;
						Response::json(array('group' => $group));
					} else{
						Response::json(array('group' => array()),404);
					}
				}
			}
		}
		exit();
	}

	public function create(){
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$u = @unserialize(cookie::get('user'));
				if($u['idu']) if(isset($in->name)){
					$tags = explode(",", implode(",", array($in->name,$in->long_name)));
					if ($tags) foreach ($tags as $v) if ($slug = string::slug($v)) {
						if ($found = Model_TagsAuto::count(array('slug' => $slug),1)){
							Response::json(array('flash' => "Tên \"$v\" đã được sử dụng"),403);
							exit;
						}
						if (mb_strlen($v)>32){
							Response::json(array('flash' => "Tên \"$v\" tối đa 32 ký tự"),403);
							exit;
						}
					}

					$g              = new Model_Group();
					$g->by          = int($u['idu']);
					$g->name        = $in->name;
					$g->slug        = string::slug($g->name);
					$g->map         = isset($in->map)?$in->map:NULL;
					$g->tag         = isset($in->tag)?implode(",", $in->tag):NULL;
					$g->local       = isset($in->local)?$in->local:NULL;
					$g->address     = isset($in->address)?$in->address:NULL;
					$g->long_name   = isset($in->long_name)?$in->long_name:NULL;
					$g->short_name  = isset($in->short_name)?$in->short_name:NULL;
					$g->subdomain   = isset($in->subdomain)?$in->subdomain:NULL;
					$g->description = isset($in->description)?$in->description:NULL;
					if($g->save()){
						// Model_TagsAuto
						Model_TagsAuto::get_or_insert($g->name,$g->id);
						foreach ($tags as $v) Model_Tags::get_or_insert($v,$g->id);
						// Model_TagsGroup
						$tag_id = 0;
						$tag_id = Model_TagsGroup::get_or_insert($g->name,$tag_id,$g->id);
						$tags = explode(",", $g->long_name);
						if($tags)foreach ($tags as $v) if ($tag=trim($v)) Model_TagsGroup::get_or_insert($tag,$tag_id,$g->id);
						// Out
						$group = $g->to_array();
						$group['map'] = explode(",",$group["map"]);
						Response::json(array('group' => $group));
						exit;
					}
				}
			}
		}
		Response::json(array('group' => array()),404);
		exit;
	}

	public function tags(){
		if(AJAX_REQUEST){
			$in = input();
			if(isset($in->keyword)){
				$array    = $where = array();
				$slug     = preg_replace("/[^0-9a-z.-]/", "", string::slug($in->keyword));
				$keywords = explode("-", $slug);
				foreach ($keywords as $v) $where[] = "slug LIKE '%".string::slug($v)."%'";
				$where = implode(" OR ", $where);
				$fetch = Model_Group::fetch(array($where),10);
				if ($fetch) foreach ($fetch as $f) $array[] = $f->to_array();
				Response::json(array('results' => $array, 'slug' => $slug));
			}
		}
		exit;
	}
	public function domain(){
		if(AJAX_REQUEST){
			$in = input();
			if(isset($in->domain)){
				$array = array();
				$fetch = Model_Group::fetch(array('subdomain' => $in->domain),10);
				if ($fetch) foreach ($fetch as $f) $array[] = $f->to_array();
				Response::json(array('results' => $array));
			}
		}
		exit;
	}
} // END class