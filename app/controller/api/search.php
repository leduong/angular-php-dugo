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
		$user       = @unserialize(cookie::get('user'));
		$in         = input();
		$limit      = 10;
		$page       = (isset($in->page)&&((int)$in->page>1))?(int)$in->page:1;
		$offset     = $limit*($page-1);
		$retina = (isset($in->retina)&&((int)$in->retina>1))?"320":"160";

		$where      = (isset($in->search))?(array) $in->search:array();
		$array      = array();
		$status     = Model_Messages::count($where + array('type' => 'status'));
		$realestate = Model_Messages::count($where + array('type' => 'realestate'));
		if ($fetch=Model_Messages::fetch($where,$limit,$offset,array('time' => 'DESC'))){
			foreach ($fetch as $m) {
				$commented = $liked = $shared = NULL;
				$image = $video = $audio = $attach = $tags = $meta = array();
				// Get User
				$u = new Model_User($m->uid);
				if(isset($u->idu)){
					foreach (array_slice(explode(',',$m->tag),0,3) as $v) $tags[string::slug($v)] = trim($v);
					if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
						$meta[$b->type] = $b->value;
					}
					if (isset($user['idu'])){
						$where     = array('msg_id' => $m->id, 'by' => $user['idu']);
						$liked     = Model_Likes::count($where);
						$commented = Model_Comments::count($where);
						//$shared    = Model_Share::count($where);
					}

					if ($value = explode(",", $m->value)) foreach ($value as $v) {
						$p=pathinfo($v);
						if (@in_array($p['extension'], explode('|','gif|jpg|jpeg|png'))){
							$image[] = $v;
						}
						elseif (@in_array($p['extension'], explode('|','mp3|mp4|mov|ogg'))){
							$video[] = $v;
						}
						elseif (@in_array($p['extension'], explode('|','mp3|m4a'))){
							$audio[] = $v;
						}
						elseif (@in_array($p['extension'], explode('|','txt|doc|docx|xls|ppt|pdf|rtf|zip|rar|tar|gz'))){
							$attach[] = $v;
						}
					}

					$array[]= array(
						'id'     => $m->id,
						'link'   => ($m->link)?$m->link:$m->id,
						'type'   => $m->type,
						'time'   => Time::show($m->date),
						'img'    => (isset($image[0]))?"$retina/media/".$image[0]:"$retina/media/default.png",
						'tag'    => $tags,
						'meta'   => $meta,
						'image'  => $image,
						'video'  => $video,
						'audio'  => $audio,
						'attach' => $attach,
						'text'   => nl2br($m->message),
						'social' => array(
							'share'     => 0,
							'comment'   => $m->comments,
							'like'      => $m->likes,
							'shared'    => 0,
							'commented' => $commented,
							'liked'     => $liked,
							),
						'user'   => array(
							'id'       => $u->idu,
							'username' => (isset($u->username))?$u->username:'',
							'name'     => $u->first_name,
							'image'    => ($u->image)?"/48/avatars/".$u->image:'/48/avatars/default.png',
							'phone'    => $u->phone,
							),
					);
				}
			}
		}
		Response::json($array);
		exit;
	}

	public function findtag()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in     = input();
				$user   = @unserialize(cookie::get('user'));
				$array  = $keywords = $querys = array();
				$limit  = 10;
				$page   = (isset($in->page)&&((int)$in->page>1))?(int)$in->page:1;
				$offset = $limit*($page-1);
				$retina = (isset($in->retina)&&((int)$in->retina>1))?"320":"160";
				/* SELECT messages.id,
				   COUNT(*) AS occurrences,
				   (SELECT COUNT(*) FROM likes WHERE msg_id=messages.id) likes
				   FROM messages, tags, tags_occurrence
				   WHERE messages.id = tags_occurrence.msg_id AND
				   tags.id = tags_occurrence.tag_id AND
				   ($where)
				   GROUP BY messages.id
				   ORDER BY messages.time DESC, likes DESC, occurrences DESC
				   LIMIT $offset, $limit */
				$db = registry('db');
				if (isset($in->keyword)){
					if (is_array($in->keyword)){
						foreach ($in->keyword as $k) $keywords[] = $k->text;
					} else {
						$keywords = (array)$in->keyword;
					}
				}
				foreach (array_unique($keywords) as $k) {
					$all = Model_TagsGroup::get_query($k);
					$querys[] = implode(' OR ', $all);
					//$querys[] = "(".implode(' OR ', $all).")";
				}
				$where = implode(' OR ', $querys);

				$query = "SELECT messages.id,
						   COUNT(*) AS occurrences
						   FROM messages, tags, tags_occurrence
						   WHERE messages.id = tags_occurrence.msg_id AND tags.id = tags_occurrence.tag_id AND
						   ($where)
						   GROUP BY messages.id
						   ORDER BY messages.time DESC
						   LIMIT $offset, $limit";
				$fetch = Cache::get("findtag.".md5($query));
				if (!$fetch) {
					$fetch = $db->fetch($query);
					Cache::set("findtag.".md5($query),$fetch,60);
				}

				if ($fetch) foreach ($fetch as $o) {
					$m = new Model_Messages($o->id);
					if (isset($m->id)) {
						$commented = $liked = $shared = NULL;
						$image = $video = $audio = $attach = $tags = $meta = array();
						// Get User
						if (isset($m->uid)) $u = new Model_User($m->uid);
						if(isset($u->idu)&&isset($m->id)){
							if($ar = explode(',',$m->tag)) foreach (array_slice($ar,0,3) as $v) $tags[string::slug($v)] = trim($v);
							if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
								$meta[$b->type] = $b->value;
							}

							if ($user&&$user['idu']){
								$where     = array('msg_id' => $m->id, 'by' => $user['idu']);
								$liked     = Model_Likes::count($where);
								$commented = Model_Comments::count($where);
								//$shared    = Model_Share::count($where);
							}

							if ($value = explode(",", $m->value)) foreach ($value as $v) {
								$p=pathinfo($v);
								if (@in_array($p['extension'], explode('|','gif|jpg|jpeg|png'))){
									$image[] = trim($v);
								}
								elseif (@in_array($p['extension'], explode('|','mp3|mp4|mov|ogg'))){
									$video[] = trim($v);
								}
								elseif (@in_array($p['extension'], explode('|','mp3,m4a'))){
									$audio[] = trim($v);
								}
								else{
									$attach[] = trim($v);
								}
							}

							$array[]= array(
								'id'     => $m->id,
								'link'   => ($m->link)?$m->link:$m->id,
								'type'   => $m->type,
								'time'   => Time::show($m->date),
								'img'    => (isset($image[0]))?"$retina/media/".$image[0]:"$retina/media/default.png",
								'tag'    => $tags,
								'meta'   => $meta,
								'image'  => $image,
								'video'  => $video,
								'audio'  => $audio,
								'attach' => array_slice($attach,0,4),
								'text'   => $m->message,
								'social' => array(
									'share'     => 0,
									'comment'   => $m->comments,
									'like'      => $m->likes,
									'shared'    => 0,
									'commented' => $commented,
									'liked'     => $liked,
									),
								'user'   => array(
									'id'       => $u->idu,
									'username' => (isset($u->username))?$u->username:'',
									'name'     => $u->first_name,
									'image'    => ($u->image)?"/48/avatars/".$u->image:'/48/avatars/default.png',
									'phone'    => $u->phone,
									),
							);
						}
					}
				}
				Response::json($array);
			}
		}
		exit;
	}

	public function typeahead(){
		$array = $city = $group = $topic = $agent = array();
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$keyword = string::slug($in->keyword);
				$fetch = Model_Tags::fetch(array("slug LIKE '%$keyword%'"),16);
				if ($fetch) foreach ($fetch as $f) {
					if (($a = Model_Group::fetch(array('slug' => $f->slug),1))&&($b=end($a))){
						$group[] = array('text' => $b->name);
					}
					elseif (($a = Model_City::fetch(array('slug' => $f->slug),1))&&($b=end($a))){
						$city[] = array('text' => $b->name);
					}
					else{
						$topic[] = array('text' => $f->name);
					}
				}
			}
		}
		Response::json(array(
				'city' => $city,
				'group' => $group,
				'topic' => $topic,
				'agent' => $agent)
			);
		exit();
	}
	public function follows(){
		if(AJAX_REQUEST){
			$city = $group = $topic = $agent = array();
			if ($ar = Model_City::fetch(array(),5,0,array('sort' => 'DESC'))) foreach ($ar as $a) $city[]  = array('text' => $a->name);
			if ($ar = Model_Group::fetch(array(),5,0,array('hits' => 'DESC'))) foreach ($ar as $a) $group[] = array('text' => $a->name);
			if ($ar = Model_Topic::fetch(array(),5))foreach ($ar as $a) $topic[] = array('text' => $a->name);

			Response::json(array(
				'city' => $city,
				'group' => $group,
				'topic' => $topic,
				'agent' => $agent)
			);
		}
		exit;
	}

	public function tag()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in     = input();
				$user   = @unserialize(cookie::get('user'));
				$array  = $keywords = $querys = array();
				$limit  = 10;
				$page   = (isset($in->page)&&((int)$in->page>1))?(int)$in->page:1;
				$offset = $limit*($page-1);
				$retina = (isset($in->retina)&&((int)$in->retina>1))?"320":"160";
				/* SELECT messages.id,
				   COUNT(*) AS occurrences,
				   (SELECT COUNT(*) FROM likes WHERE msg_id=messages.id) likes
				   FROM messages, tags, tags_occurrence
				   WHERE messages.id = tags_occurrence.msg_id AND
				   tags.id = tags_occurrence.tag_id AND
				   ($where)
				   GROUP BY messages.id
				   ORDER BY messages.time DESC, likes DESC, occurrences DESC
				   LIMIT $offset, $limit */
				$db = registry('db');
				if (isset($in->keyword)){
					if (is_array($in->keyword)){
						foreach ($in->keyword as $k) $keywords[] = $k->text;
					} else {
						$keywords = (array)$in->keyword;
					}
				}
				foreach (array_unique($keywords) as $k) {
					$all = Model_TagsGroup::get_query($k);
					$querys[] = implode(' OR ', $all);
					//$querys[] = "(".implode(' OR ', $all).")";
				}
				$where = implode(' OR ', $querys);

				$query = "SELECT messages.id
				   FROM messages, tags, tags_occurrence
				   WHERE messages.id = tags_occurrence.msg_id AND
				   tags.id = tags_occurrence.tag_id AND
				   ($where)
				   GROUP BY messages.id
				   ORDER BY messages.time DESC
				   LIMIT $offset, $limit";
				$fetch = Cache::get("findtag.".md5($query));
				if (!$fetch) {
					$fetch = $db->fetch($query);
					Cache::set("findtag.".md5($query),$fetch,120);
				}

				foreach ($fetch as $o) {
					$m = new Model_Messages($o->id);
					if (isset($m->id)) {
						$commented = $liked = $shared = NULL;
						$image = $video = $audio = $attach = $tags = $meta = array();
						// Get User
						if (isset($m->uid)) $u = new Model_User($m->uid);
						if(isset($u->idu)&&isset($m->id)){
							if($ar = explode(',',$m->tag)) foreach (array_slice($ar,0,3) as $v) $tags[string::slug($v)] = trim($v);
							if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
								$meta[$b->type] = $b->value;
							}

							if ($user&&$user['idu']){
								$where     = array('msg_id' => $m->id, 'by' => $user['idu']);
								$liked     = Model_Likes::count($where);
								$commented = Model_Comments::count($where);
								//$shared    = Model_Share::count($where);
							}

							if ($value = explode(",", $m->value)) foreach ($value as $v) {
								$p=pathinfo($v);
								if (@in_array($p['extension'], explode('|','gif|jpg|jpeg|png'))){
									$image[] = trim($v);
								}
								elseif (@in_array($p['extension'], explode('|','mp3|mp4|mov|ogg'))){
									$video[] = trim($v);
								}
								elseif (@in_array($p['extension'], explode('|','mp3,m4a'))){
									$audio[] = trim($v);
								}
								else{
									$attach[] = trim($v);
								}
							}

							$array[]= array(
								'id'     => $m->id,
								'link'   => ($m->link)?$m->link:$m->id,
								'type'   => $m->type,
								'time'   => Time::show($m->date),
								'img'    => (isset($image[0]))?"$retina/media/".$image[0]:"$retina/media/default.png",
								'tag'    => $tags,
								'meta'   => $meta,
								'image'  => $image,
								'video'  => $video,
								'audio'  => $audio,
								'attach' => array_slice($attach,0,4),
								'text'   => $m->message,
								'social' => array(
									'share'     => 0,
									'comment'   => $m->comments,
									'like'      => $m->likes,
									'shared'    => 0,
									'commented' => $commented,
									'liked'     => $liked,
									),
								'user'   => array(
									'id'       => $u->idu,
									'username' => (isset($u->username))?$u->username:'',
									'name'     => $u->first_name,
									'image'    => ($u->image)?"/48/avatars/".$u->image:'/48/avatars/default.png',
									'phone'    => $u->phone,
									),
							);
						}
					}
				}
				Response::json($array);
			}
		}
		exit;
	}
}