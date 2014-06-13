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
							'name'     => ($u->first_name)?$u->first_name:'Nhà đất #'.$u->idu,
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
									'name'     => ($u->first_name)?$u->first_name:'Nhà đất #'.$u->idu,
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
		$array = $city = $district = $zipcode = $group = $topic = $agent = array();
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$keyword = string::slug($in->keyword);
				/*$fetch = Model_Tags::fetch(array("slug LIKE '%".$keyword."%'"));
				if ($fetch) foreach ($fetch as $f) {
					$all = Model_TagsGroup::get_query($f->name);
					$where = implode(' OR ', $all);
					if ($a = Model_Group::fetch(array($where),1)){
						$group[] = array('text' => $f->name, 'slug' => $f->slug);
					}
					elseif ($a = Model_City::fetch(array($where),1)){
						$city[] = array('text' => $f->name, 'slug' => $f->slug);
					}
					elseif (($a = Model_District::fetch(array('slug' => $f->slug),1))&&($b=end($a))){
						$district[] = array('text' => $b->name, 'slug' => $f->slug);
					}
					elseif (($a = Model_Zipcode::fetch(array('slug' => $f->slug),1))&&($b=end($a))){
						$zipcode[] = array('text' => implode(", ", array_slice(explode(", ", $b->full_name),0,2)), 'slug' => $f->slug);
					}
					else{
						$topic[] = array('text' => $f->name, 'slug' => $f->slug);
					}
				}*/
				$where = implode(' OR ', Model_TagsGroup::get_query($keyword));
				if ($ar = Model_City::fetch(array("slug LIKE '%".$keyword."%'"))){
					foreach ($ar as $a) $city[] = array('text' => $a->name, 'slug' => $a->slug);
				}
				if ($ar = Model_District::fetch(array("slug LIKE '%".$keyword."%'"),10)){
					foreach ($ar as $a) $district[] = array('text' => $a->name, 'slug' => $a->slug);
				}
				if ($ar = Model_Zipcode::fetch(array("slug LIKE '%".$keyword."%'"),10)){
					foreach ($ar as $a) $zipcode[] = array('text' => implode(", ", array_slice(explode(", ", $a->full_name),0,2)), 'slug' => $a->slug);
				}
				if ($ar = Model_TagsAuto::fetch(array("slug LIKE '%".$keyword."%'"))){
					foreach ($ar as $a) {
						if ($a->group_id>0){
							$group[] = array('text' => $a->name, 'slug' => $a->slug);
						} else $topic[] = array('text' => $a->name, 'slug' => $a->slug);
					}
				}
			}
		}
		Response::json(array(
				'city'     => $city,
				'district' => $district,
				'zipcode'  => $zipcode,
				'group'    => $group,
				'topic'    => $topic,
				'agent'    => $agent)
			);
		exit();
	}
	public function follows(){
		if(AJAX_REQUEST){
			$city = $group = $topic = $agent = array();
			if ($ar = Model_City::fetch(array(),5,0,array('sort' => 'DESC'))) foreach ($ar as $a) $city[]  = array('text' => $a->name, 'slug' => $a->slug);
			if ($ar = Model_Group::fetch(array(),5,0,array('hits' => 'DESC'))) foreach ($ar as $a) $group[] = array('text' => $a->name, 'slug' => $a->slug);
			if ($ar = Model_TagsAuto::fetch(array('group_id' => 0),5,0,array('hits' => 'DESC')))foreach ($ar as $a) $topic[] = array('text' => $a->name, 'slug' => $a->slug);

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
				$array  = $keywords = $results = array();
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
						foreach ($in->keyword as $k) $keywords[] = $k->slug;
					} else {
						$keywords = (array)$in->keyword;
					}
				}
				foreach (array_unique($keywords) as $k) $results[] = self::getKeyword($k);
				$intersect = $results[0];
				for ($j=1; $j < count($results); $j++) $intersect = self::giaogiao($intersect, $results[$j]);

				for ($i=$offset; $i < min(count($intersect),($offset+$limit)); $i++) {
					$m = new Model_Messages($intersect[$i]);
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
									'name'     => ($u->first_name)?$u->first_name:'Nhà đất #'.$u->idu,
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

	public function nero()
	{
		if(AJAX_REQUEST&&POST){
			$in    = input();
			$array = $keywords = array();
			if(isset($in->keyword)&&$in->keyword) {
				if (is_array($in->keyword)){
					foreach ($in->keyword as $k) $keywords[] = $k->slug;
				} else {
					$keywords = (array)$in->keyword;
				}
				// Get first Tag
				$slug  = string::slug($keywords[0]);
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

					$all = Model_TagsGroup::get_query($slug);
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
						$array['by_name'] = ($user->first_name)?$user->first_name:'Nhà đất #'.$user->idu;
						$array['name']    = $group["name"];
						$group['map']     = explode(",",$group["map"]);
						$array['type']    = "group";
						$array            = array_merge($group, $array);
					}
					else if($fetch = Model_City::fetch(array('slug' => $slug),1)){
						$a             = end($fetch);
						$same = Model_TagsGroup::get_array($slug);
						$array['long_name'] = $same[0];
						$array['map']  = explode(",",$a->map);
						$array['type'] = "city";
					}
					else if($fetch = Model_District::fetch(array('slug' => $slug),1)){
						$a             = end($fetch);
						$city          = new Model_City($a->city_id);
						$array['map']  = @explode(",",$a->map);
						$array['type'] = "city";
						$array['name'] = $a->name.", ".$city->name;
					}
					else if($fetch = Model_Zipcode::fetch(array('slug' => $slug),1)){
						$a             = end($fetch);
						$array['map']  = @explode(",",$a->map);
						$array['type'] = "city";
						$array['name'] = $a->full_name;
					} else {
						$same = Model_TagsGroup::get_array($slug);
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
		exit();
	}
	public function filter(){
		if(AJAX_REQUEST&&POST){
			$in       = input();
			$keywords = array();
			if(isset($in->keyword)&&$in->keyword) {
				if (is_array($in->keyword)){
					foreach ($in->keyword as $k) $keywords[] = $k->slug;
				} else {
					$keywords = (array)$in->keyword;
				}
			}
		}
		exit();
	}
	static public function getKeyword($keyword = NULL)
	{
		$ar = array();
		if($keyword!=NULL){
			$slug = string::slug($keyword);
			$fetch = Cache::get("keyword.".md5($slug));
			if (!$fetch) {
				$db = registry('db');
				//$all = Model_TagsGroup::get_query($keyword);
				$where = "slug = '$slug'";// implode(' OR ', $all);
				$query = "SELECT messages.id
				   FROM messages, tags, tags_occurrence
				   WHERE messages.id = tags_occurrence.msg_id AND
				   tags.id = tags_occurrence.tag_id AND
				   ($where)
				   GROUP BY messages.id
				   ORDER BY messages.time DESC
				   LIMIT 1000";
				$fetch = $db->fetch($query);
				Cache::set("keyword.".md5($slug),$fetch,600);
			}
			foreach ($fetch as $a) $ar[] = $a->id;
		}
		return $ar;
	}
	static public function giaogiao($ar1 = array(), $ar2 = array()){
		$ar = array();
		foreach ($ar1 as $a) if (in_array($a, $ar2)) $ar[]=$a;
		return $ar;
	}
}
