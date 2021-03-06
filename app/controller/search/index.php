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
 * Controller_Search_Index class
 *
 * @package Controller_Search_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/search/index.php
 * @template {{app}}/view/search/index.php
 **/

class Controller_Search_Index extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			if(POST){
				$array    = array();
				$limit    =10;
				$page     =((int)get('page')>1)?(int)get('page'):1;
				$offset   =$limit*($page-1);
				$input    = input();
				$keywords = explode(",", $input->keyword);
				$query    = '';

				foreach ($keywords as $k) {
					$slug = string::slug($k);
					if(($substr=substr($slug,0,2))&& in_array($substr,array('p-','x-','h-'))){
						$slug = substr($slug,2,strlen($slug));
					}
					$query .= "OR slug LIKE '%$slug%'";
				}
				$query = trim($query,"OR");

				if ($fetch=Model_Tags::fetch(array("$query"),$limit,$offset)){
					foreach ($fetch as $f) {
						$f->load();
						foreach ($f->occurrence() as $o) {
							$m = new Model_Messages($o->msg_id);
							$u = new Model_User($m->uid);
							$tag = $meta = array();
							foreach (array_slice(explode(',',$m->tag),0,2) as $v) {
								$tag[string::slug($v)] = trim($v);
							}
							if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
								$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
							}
							$array[]= array(
								'title' => $f->name,
								'img' => ($m->type=='picture')?'http://192.241.221.27:8080/thumb.php?w=320&h=320&t=m&src='.$m->value:'/uploads/media/default.png',
								'tag' => $tag,
								'meta' => $meta,
								'text' => mb_convert_case($m->message, MB_CASE_TITLE, "UTF-8"),
								'user' => array(
									'username' => $u->username,
									'name' => $u->first_name." ".$u->last_name,
									'avatar' => "http://www.gravatar.com/avatar/".md5($u->email)."?s=20&d=wavatar&r=g",
									'phone' => $u->phone,
									),
							);
						}
					}
				}
				if ($array){
					Response::json($array);
				}
				else Response::json(array('flash' => "Không tìm thấy $query"), 404);
			}else{
				$tpl = new Template("search");
				echo $tpl->make();
			}
			exit;
		}
		else $this->content = '';
	}
	public function show(){
		$callback = get('jsonp');
		$limit    =10;
		$page     =((int)get('page')>1)?(int)get('page'):1;
		$offset   =$limit*($page-1);
		$input    = input();
		$where    = (isset($input->search))?(array) $input->search:array();

		$array = array();
		if ($fetch=Model_Messages::fetch($where,$limit,$offset,array('id' => 'DESC'))){
			foreach ($fetch as $m) {
				$u = new Model_User($m->uid);
				$tag = $meta = array();
				foreach (array_slice(explode(',',$m->tag),0,2) as $v) {
					$tag[string::slug($v)] = trim($v);
				}
				if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
					$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
				}
				$array[]= array(
					'id'   => $m->id,
					'type' => $m->type,
					'time' => $m->time,
					'img'  => ($m->value)?$m->value:'default.png',
					'tag'  => $tag,
					'meta' => $meta,
					'text' => mb_convert_case($m->message, MB_CASE_TITLE, "UTF-8"),
					'user' => array(
						'username' => $u->username,
						'name'     => $u->first_name." ".$u->last_name,
						'image'    => $u->image,
						'phone'    => $u->phone,
						),
				);
			}
		}
		header('content-type: application/json; charset=utf-8');
		echo $callback . '('.json_encode($array).')';
		exit;
	}
	public function typeahead(){
		if(AJAX_REQUEST){
			$array = array();
			if(POST){
				$input = input();
				$keyword = string::slug($input->keyword);/*
				$fetch = Model_Zipcode::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch){
					foreach ($fetch as $f) {
						$city = new Model_City($f->city_id);
						$district = new Model_District($f->district_id);
						$array[]=$f->name.", ".$district->name.", ".$city->name;
					}
				}
				$fetch = Model_District::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch){
					foreach ($fetch as $f) {
						$city = new Model_City($f->city_id);
						$array[]=$f->name.", ".$city->name;
					}
				}
				$fetch = Model_City::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch){
					foreach ($fetch as $f) {
						$array[]=$f->name;
					}
				}*/
				$fetch = Model_Tags::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch){
					foreach ($fetch as $f) {
						$array[]=$f->name;
						# code...
					}
				}
			}
			Response::json($array);
			exit();
		}
	}
	public function zipcode(){
		if(AJAX_REQUEST){
			$array = array();
			if(POST){
				$input = input();
				$keyword = string::slug($input->keyword);

				$fetch = Model_Group::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch) foreach ($fetch as $f) $array[]=$f->name.", ".$f->address;
				// Zipcode
				$fetch = Model_Zipcode::fetch(array("slug LIKE '%$keyword%'"),8);
				if ($fetch){
					foreach ($fetch as $f) {
						$city = new Model_City($f->city_id);
						$district = new Model_District($f->district_id);
						$array[]=$f->name.", ".$district->name.", ".$city->name;
					}
				}
			}
			Response::json($array);
			exit();
		}
	}
} // END class