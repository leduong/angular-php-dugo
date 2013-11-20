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
* Controller_Api_Silder class
*
* @package Controller_Welcome_Index
* @author [author] <[email]>
* @filename {{app}}/controller/api/search.php
* @template {{app}}/view/api/search.php
**/
class Controller_Api_Search extends Controller
{
	public function index(){
		$input    = input();
		$limit    = 10;
		$page     = ((int)$input->page>1)?(int)$input->page:1;
		$offset   = $limit*($page-1);
		$where    = (isset($input->search))?(array) $input->search:array();
		$array = array();
		if ($fetch=Model_Messages::fetch($where,$limit,$offset,array('id' => 'DESC'))){
			foreach ($fetch as $m) {
				$u = new Model_User($m->uid);
				$tag = $meta = array();
				foreach (array_slice(explode(',',$m->tag),0,3) as $v) {
					$tag[string::slug($v)] = trim($v);
				}
				if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
					$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
				}
				$like    = Model_Likes::count(array('msg_id' => $m->id));
				$comment = Model_Comments::count(array('msg_id' => $m->id));
				$array[]= array(
					'id'      => $m->id,
					'type'    => $m->type,
					'time'    => $m->time,
					'img'     => ($m->value)?$m->value:'default.png',
					'tag'     => $tag,
					'meta'    => $meta,
					'social' => array(
						'share'   => $share,
						'comment' => $comment,
						'like'    => $like,
						),
					'text'    => mb_convert_case($m->message, MB_CASE_TITLE, "UTF-8"),
					'user'    => array(
						'username' => $u->username,
						'name'     => $u->first_name." ".$u->last_name,
						'image'    => $u->image,
						'phone'    => $u->phone,
						),
				);
			}
		}
		Response::json($array);
		exit;
	}

	public function findtag()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input    = input();
				$array    = array();
				$limit    = 10;
				$page     = ((int)$input->page>1)?(int)$input->page:1;
				$offset   = $limit*($page-1);
				$keywords = (isset($input->keyword))?explode(",", $input->keyword):array();
				$query    = '';
				if ($keywords) foreach ($keywords as $k) {
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
							foreach (array_slice(explode(',',$m->tag),0,3) as $v) {
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
				}
				Response::json($array);
			}
			exit;
		}
	}
}