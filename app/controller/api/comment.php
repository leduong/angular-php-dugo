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
				$u = unserialize(cookie::get('user'));
				$user = new Model_User($u['idu']);
				if($user){ // If exist User
					$input = input();
					$comment = new Model_Comments();
					$comment->uid = $u['idu'];
					foreach ((array)$input as $key => $value) {
			            $comment->$key = (isset($comment->$key)&&($comment->$key!=$value))?$value:$comment->$key;
		            }
					$comment->save();
					Response::json(array(
						'flash' => 'successful',
						'comment' => $comment->to_array()
					));
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