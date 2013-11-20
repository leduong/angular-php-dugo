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
	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$u = unserialize(cookie::get('user'));
				$meta = array();
				$input = input();
				$message = new Model_Messages();

				foreach ((array)$input as $key => $value) {
					if( in_array($key, array('address', 'map', 'price', 'phone')) ){
						$meta[$key] = $value;
					}
					elseif($key == 'message'){
						$message->message = $value;
					}
					elseif($key == 'tag'){
						$message->tag = $value;
					}
					elseif($key == 'type'){
						$message->type = $value;
					}
				}

				if($u['idu']){ // If exist User
					$message->uid = $u['idu'];
				} else {
					$user_exist = Model_User::fetch(array('phone' => numify($meta["phone"])),1);
					if ($user_exist[0]){
						$user = $user_exist[0];
					} else {
						$user        = new Model_User();
						$user->phone = numify($meta["phone"]);
						$user->save();
					}
					$message->uid = $user->idu;
				}

				$message->save();

				foreach ($meta as $key => $value) {
					$mt         = new Model_MessagesMeta();
					$mt->msg_id = $message->id;
					$mt->type   = $key;
					$mt->value  = $value;
					$mt->save();
				}

				// Tags
				$del = Model_TagsOccurrence::fetch(array('msg_id' => $message->id));
				if ($del) foreach ($del as $d) $d->delete();
				$tags = explode(',',$message->tag.",".$meta['price'].",".$meta['address']);
				foreach ($tags as $tag) {
					$tag_id = Model_Tags::get_or_insert($tag);
					if ($tag_id){
						$tags_occurrence         = new Model_TagsOccurrence();
						$tags_occurrence->msg_id = $message->id;
						$tags_occurrence->tag_id = $tag_id;
						$tags_occurrence->save();
					}
				}
				// end Tags

				Response::json(array(
					'message' => $message->to_array(),
					'meta'=>$meta)
				);
			}
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$tag = $meta = array();
				$input = input();
				$msg = new Model_Messages(str_replace('.html','',$input->id));
				if ($msg){
					$mt = Model_MessagesMeta::fetch(array('msg_id' => $msg->id));
					if ($mt) foreach($mt as $m) $meta[$m->type] = mb_convert_case($m->value, MB_CASE_TITLE, "UTF-8");

					foreach (explode(',',$msg->tag) as $v) $tag[string::slug($v)] = trim($v);

					if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
						$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
					}

					$user = new Model_User($msg->uid);
					Response::json(array(
						'user' => $user->to_array(),
						'post' => $msg->to_array(),
						'meta' => $meta,
						'tags' => $tag)
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
				$input = input();
				$msg = new Model_Messages($input->id);
				if ($msg){
					$u = unserialize(cookie::get('user'));
					if($msg->uid==$u['idu']){ // message of User
						$meta = array();
						foreach ((array)$input as $key => $value) {
							if( in_array($key, array('address', 'map', 'price', 'phone')) ){
								$meta[$key] = $value;
							} else {
								$msg->$key = (isset($msg->$key)&&($msg->$key!=$value))?$value:$msgmsg->$key;
							}
						}
						$msg->save();

						$del = Model_MessagesMeta::fetch(array('msg_id' => $input->id));
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
				$input = input();
				$msg = new Model_Messages($input->id);
				if ($msg){
					$u = unserialize(cookie::get('user'));
					if($msg->uid==$u['idu']){ // message of User

						$msg->delete();
						$meta = Model_MessagesMeta::fetch(array('msg_id' => $input->id));
						if ($meta) foreach ($meta as $d) $d->delete();

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