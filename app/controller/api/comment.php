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

class Controller_Api_Comment extends Controller
{
	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				if (isset($in->msg_id)){
					$id = $in->msg_id;
					if (int($id)) {
						$msg = new Model_Messages($id);
					} else {
						$msg = Model_Messages::fetch(array('link' => $id),1);
						$msg = end($msg);
					}
					$total = Model_Comments::count(array('msg_id' => $msg->id));

					// Check User via Cookie
					$user = unserialize(cookie::get('user'));
					if(isset($user['idu'])&&(int)$user['idu']&&(isset($in->comment))){
						$by = new Model_User($user['idu']);
						if ($by){
							$c          = new Model_Comments();
							$c->by      = $user['idu'];
							$c->msg_id  = $msg->id;
							$c->message = $in->comment;
							$c->save();
							//update comments
							$msg->comments = $total+1;
							$msg->save();

							Response::json(array(
								'comment'  => nl2br($c->message),
								'time'     => Time::show(time()),
								'name'     => $by->first_name,
								'location' => '',
								));
						}
					}
					else Response::json(array('total' => $total), 403);
				}
				// Return 0
				else Response::json(array('total' => 0), 403);
			}
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$cmt = new Model_Comments($input->id);
				if ($cmt){
					$u = unserialize(cookie::get('user'));
					if($cmt->uid==$u['idu']){ // messafe of User
						Response::json(array(
							'flash' => 'success',
							'comment' => $cmt->to_array(),
						));
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
				$cmt = new Model_Comments($input->id);
				if ($cmt){
					$u = unserialize(cookie::get('user'));
					if($cmt->uid==$u['idu']){ // message of User
						foreach ((array)$input as $key => $value) {
							$cmt->$key = (isset($cmt->$key)&&($cmt->$key!=$value))?$value:$cmt->$key;
						}
						$cmt->save();
						Response::json(array(
							'flash' => 'success',
							'cmt' => $cmt->to_array(),
						));
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
				$cmt = new Model_Messages($input->id);
				if ($cmt){
					$u = unserialize(cookie::get('user'));
					if($cmt->uid==$u['idu']){ // message of User
						$cmt->delete();
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