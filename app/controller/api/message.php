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

class Controller_Api_Message extends Controller
{
	public function tags()
	{
		if(AJAX_REQUEST && POST){
			$in     = input();
			$limit  = 10;
			$page   = (isset($in->page)&&((int)$in->page>1))?(int)$in->page:1;
			$offset = $limit * ($page-1);
			$tags   = array();
			$group = (isset($in->group))?"group_id >= 0":"group_id = 0";
			if (isset($in->content)){
				$db = registry('db');
				$query = "SELECT name, ((MATCH(name) AGAINST (? IN BOOLEAN MODE))/(LENGTH(name) - LENGTH(REPLACE(name, ' ', '')) + 1)) as percent
						FROM tags_auto
						WHERE $group AND (((MATCH(name) AGAINST (? IN BOOLEAN MODE))/(LENGTH(name) - LENGTH(REPLACE(name, ' ', '')) + 1)) > ?)
						ORDER BY `percent` DESC
						LIMIT $offset, $limit";
				$fetch = $db->fetch($query, array($in->content, $in->content, $this->appsite['min_match_word_pct']));
				foreach ($fetch as $f) $tags[] = $f->name;
				if ($tags){
					Response::json(array('tags' => $tags, 'price' => vnprice($in->content)));
				} else {
					Response::json(array('tags' => array()),404);
				}
			}
		}
		exit;
	}
	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$u = @unserialize(cookie::get('user'));
				if($u['idu']){ // If exist User
					$meta = $tags = array();
					$in = input();
					$m = new Model_Messages();
					$m->date = date('Y-m-d H:i:s');
					foreach ((array)$in as $k => $v) if($v&&$k) {
						if( in_array($k, array('address', 'map', 'price', 'rent', 'local')) ){
							$meta[$k] = trim($v);
						}
						elseif($k == 'message'){
							$m->message = $v;
						}
						elseif($k == 'type'){
							$m->type = $v;
						}
						elseif($k == 'tag'){
							$tags = array_unique($v);
						}
						elseif($k == 'images'){
							$m->value = implode(",", $v);
						}
					}


					/* Auto Tags */
					$autotags = Cache::get('autotags');
					if (!$autotags){
						$autotags = array();
						if ($ar = Model_TagsAuto::fetch()) foreach ($ar as $a) {
							$autotags[$a->slug] = $a->name;
						}
						if ($ar = Model_TagsGroup::fetch()) foreach ($ar as $a) {
							$autotags[$a->slug] = $a->name;
						}
						$autotags = array_unique($autotags);
						Cache::set('autotags', $autotags);
					}
					// Check
					if (strlen($m->message)>=3){
						$m->uid = $u['idu'];
						$ar = array();
						$message = string::slug($m->message);
						foreach ($autotags as $k => $v) if (strpos($message, $k) !== false) $ar[] = $v;
						if(isset($tags)) foreach ($tags as $t) if ($autotags[string::slug($t)]) $ar[] = $autotags[string::slug($t)];
						$m->tag = implode(",", array_unique($ar));

						$ar = array(); // reset array $ar
						foreach (explode(',',$m->tag) as $v) if(strlen($v)>2) $ar[] = string::slug($v);
						if (count($ar)){
							$link = $_link = implode("/", array_slice($ar, 0, 5));
							$i = 0;
							$check = 1;
							while ($check = Model_Messages::fetch(array('link' => $link))){
								$i++;
								$link = "$_link/$i";
							}
							$m->link = $link;
						}
						if (true!=Cache::get(md5(serialize((array)$in)))){
							$m->save();
							Cache::set(md5(serialize((array)$in)),true);
							// Send Notification
							$type = ($m->type=='status')?1:2;
							foreach (explode(',',$m->tag) as $v) {
								$where = implode(' OR ', Model_TagsGroup::get_query($v));
								if ($a = Model_Group::fetch(array($where),1)){
									$a = end($a);
									$owner = new Model_User($a->by);
									if ($owner->email){
										Model_Notifications::sendmail($type, $owner->email, $m->uid, $a->name, $a->slug);
									}
								}
							}
						}

						$link = (isset($link))?$link:$id;
						$url = ($m->type=='status')?"/c/$link.html":"/p/$link.html";
						pingSE($url);

						$del = Model_MessagesMeta::fetch(array('msg_id' => $m->id));
						if ($del) foreach ($del as $d) $d->delete();
						foreach ($meta as $key => $value) if ($value&&$key) {
							$mt         = new Model_MessagesMeta();
							$mt->msg_id = $m->id;
							$mt->type   = $key;
							$mt->value  = $value;
							$mt->save();
						}

						// Tags
						$arr = array();
						if (isset($meta["local"])){
							if($ar = @explode(',',$meta["local"])) while (count($ar)>0) {
								$arr[string::slug(implode(",", $ar))] = trim($ar[0]);
								$ar = array_slice($ar, 1);
							}
						}
						if(isset($arr)){
							if(isset($tags)) foreach ($tags as $t) $arr[string::slug($t)] = $t;
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
						Response::json(array('message' => array('id' => (isset($m->link))?$m->link:$m->id)));
					}else {
						Response::json(array('flash' => 'Nội dung quá ngắn, ít nhất 3 ký tự'), 403);
						exit;
					}
				}
				else {
					Response::json(array("flash" => "Lỗi: Đăng tin không thành công."), 403);
					exit;
				}
			}
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$tags = $group = $local = $meta = $comments = $image = $video = $audio = $attach = array();
				$in = input();
				$id = str_replace('.html','',$in->id);
				if (int($id)) {
					$m = new Model_Messages($id);
				} else {
					$m = Model_Messages::fetch(array('link' => $id),1);
					$m = end($m);
				}
				// Message
				if (isset($m->id)){
					// User
					$u  = new Model_User($m->uid);

					// Meta Tag
					$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id));
					if ($mt) foreach($mt as $v) $meta[$v->type] = trim($v->value);
					if (isset($meta["map"])) $meta["map"] = explode(",",$meta["map"]);
					if (isset($meta["local"])){
						if($ar = @explode(',',$meta["local"])) while (count($ar)>0) {
							$local[string::slug(implode(",", $ar))] = trim($ar[0]);
							$ar = array_slice($ar, 1);
						}
						$meta["local"] = $local;
					}
					$_tags = array();
					if($ar = @explode(',',$m->tag)) foreach ($ar as $a) if($x=trim($a)){
						if ($b = Model_Group::fetch(array('slug' => string::slug($x)),1)){
							$meta["local"][string::slug($x)] = $x;
							$g = explode(",",$b[0]->name.",".$b[0]->long_name);
							foreach ($g as $v) if ($y=trim($v)) $group[]=$y;
						}
						elseif ($b = Model_City::fetch(array('slug' => string::slug($x)),1)){
							$meta["local"][string::slug($x)] = $x;
						}
						else {
							$_tags = array_merge($_tags,Model_TagsGroup::get_array($x));
						}
					}
					$meta["local"] = array_unique($meta["local"]);
					//die(dump($_tags));

					if ($ar = array_diff(array_unique($_tags),$group)) foreach ($ar as $a) $tags[string::slug($a)] = trim($a);

					//
					$where   = array('msg_id' => $m->id);
					$share   = Model_Share::count($where);
					$like    = Model_Likes::count($where);
					$fetchs  = Model_Comments::fetch($where);
					$comment = count($fetchs);

					if ($fetchs) foreach ($fetchs as $c) {
						$by  = new Model_User($c->by);
						$comments[] = array(
							'comment'  => nl2br($c->message),
							'time'     => Time::show($c->time),
							'name'     => ($by->first_name)?$by->first_name:'Nhà đất #'.$by->idu,
							'location' => '',
							);
					}else{
						$comments[] = array(
							'comment'  => '',
							'time'     => '',
							'name'     => '',
							'location' => '',
							);
					}

					// User cookie
					$user   = @unserialize(cookie::get('user'));
					$liked = $shared = $commented = 0;
					if ($user&&$user['idu']){
						$where     = array('msg_id' => $m->id, 'by' => $user['idu']);
						$liked     = Model_Likes::count($where);
						$commented = Model_Comments::count($where);
						$shared    = Model_Share::count($where);
					}

					if ($value = @explode(",", $m->value)){
						foreach ($value as $v) if ($v=trim($v)) {
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
					}
					Response::json(array(
						'id'       => $m->id,
						'link'     => ($m->link)?$m->link:$m->id,
						'message'  => nl2br($m->message),
						'time'     => Time::show($m->date),
						'img'      => (isset($image[0]))?"http://static.nhadat.com/640/media/".$image[0]:'http://static.nhadat.com/640/media/default.png',
						'image'    => $image,
						'video'    => $video,
						'audio'    => $audio,
						'attach'   => $attach,
						'meta'     => $meta,
						'tags'     => $tags,
						'comments' => $comments,
						'social'   => array(
							'share'     => $share,
							'comment'   => $comment,
							'like'      => $like,
							'shared'    => $shared,
							'commented' => $commented,
							'liked'     => $liked,
						),
						'user'     => array(
							'id'       => $u->idu,
							'username' => ($u->username)?$u->username:'',
							'name'     => ($u->first_name)?$u->first_name:'Nhà đất #'.$u->idu,
							'image'    => ($u->image)?"http://static.nhadat.com/128/avatars/".$u->image:'http://static.nhadat.com/128/avatars/default.png',
							'phone'    => $u->phone,
							),
						)
					);
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}

	public function update()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$msg = new Model_Messages($in->id);
				if ($msg){
					$u = unserialize(cookie::get('user'));
					if($msg->uid==$u['idu']){ // message of User
						$meta = array();
						foreach ((array)$in as $key => $value) {
							if( in_array($key, array('address', 'map', 'price', 'phone')) ){
								$meta[$key] = $value;
							} else {
								$msg->$key = (isset($msg->$key)&&($msg->$key!=$value))?$value:$msgmsg->$key;
							}
						}
						$msg->save();

						$del = Model_MessagesMeta::fetch(array('msg_id' => $in->id));
						if ($del) foreach ($del as $d) $d->delete();

						foreach ($meta as $key => $value) {
							$mt = new Model_MessagesMeta();
							$mt->msg_id = $msg->id;
							$mt->type = $key;
							$mt->value = $value;
							$mt->save();
						}

						Response::json(array(
							'flash' => 'success',
							'message' => $msg->to_array(),
							'meta'=>$meta)
						);
					} else{
						Response::json(array('flash' => 'permission_denied'),403);
					}
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}

	public function destroy()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$id = $in->id;
				if (int($id)) {
					$msg = new Model_Messages($id);
				} else {
					$msg = Model_Messages::fetch(array('link' => $id),1);
					$msg = end($msg);
				}
				$u = @unserialize(cookie::get('user'));
				if (isset($msg->uid)){
					if($msg->uid==$u['idu']){
						//User
						$msg->delete();
						Response::json(array('flash' => 'success'));
					} else{
						Response::json(array('flash' => 'permission_denied'),403);
					}
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}
} // END class