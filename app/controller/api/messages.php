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

class Controller_Api_Messages extends Controller
{
	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$u = unserialize(cookie::get('user'));
				$user = new Model_User($u['idu']);
				if($user){ // If exist User
					$input = input();
					$message = new Model_Messages();
					$message->uid = $u['idu'];
					foreach ((array)$input as $key => $value) {
						$message->$key = (isset($message->$key)&&($message->$key!=$value))?$value:$message->$key;
						if( in_array($key, array('address', 'map', 'price', 'phone')) ){
							$meta[$key] = $value;
						}
					}
					$message->save();

					foreach ($meta as $key => $value) {
						$mt = new Model_MessagesMeta();
						$mt->msg_id = $message->id;
						$mt->type = $key;
						$mt->value = $value;
						$mt->save();
					}

					Response::json(array(
						'flash' => 'successful',
						'message' => $message->to_array(),
						'meta'=>$meta));
				} else {
					Response::json(array('flash' => 'permission_denied'),403);
				}
			}
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$msg = new Model_Messages($input->id);
				if ($msg){
					$u = unserialize(cookie::get('user'));
					if($msg->uid==$u['idu']){ // messafe of User
						$mt = Model_MessagesMeta::fetch(array('msg_id'=>$input->id));
						foreach($mt as $m){
							$meta[$m->type] = $m->value;
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