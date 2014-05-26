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

class Controller_Api_Like extends Controller
{
	public function create()
	{
		if(AJAX_REQUEST&&POST){
			$in = input();
			$u = unserialize(cookie::get('user'));// Check User via Cookie
			if (isset($in->like)){
				$id = $in->like;
				if (int($id)) {
					$msg = new Model_Messages($id);
				} else {
					$msg = Model_Messages::fetch(array('link' => $id),1);
					$msg = end($msg);
				}
				$total = Model_Likes::count(array('msg_id' => $msg->id));
				if(isset($u['idu'])&&(int)$u['idu']){
					if($liked = Model_Likes::fetch(array('by' => $u['idu'], 'msg_id' => $msg->id),1)){
						$like = end($liked);
						$like->delete();
						// update likes
						$msg->likes = $total-1;
						$msg->save();
						Response::json(array('like' => False, 'total' => $total-1));
					}
					else {
						$like         = new Model_Likes();
						$like->by     = $u['idu'];
						$like->msg_id = $msg->id;
						$like->save();
						// update likes
						$msg->likes = $total+1;
						$msg->save();
						Response::json(array('like' => True, 'total' => $total+1));

						// Send Notification
						$owner = new Model_User($msg->uid);
						$type = ($msg->type=='status')?5:3;
						$link = ($msg->link)?$msg->link:$msg->id;
						$name = substr(substr(str_replace("\n", " ", $msg->message),0,32),0,strrpos(substr(str_replace("\n", " ", $msg->message),0,32)," "));
						if ($owner->email){
							Model_Notifications::sendmail($type,$owner->email,$u['idu'],$name,$link);
						}
					}
				}
				else Response::json(array(), 403);
			}
			// Return 0
			else Response::json(array(), 403);
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				if (isset($input->like)){
					// Cache :: Total
					$total = Cache::get('like'.md5($input->like));
					if(!$total){
						$total = Model_Likes::count(array('msg_id' => $input->like));
						Cache::set('like'.md5($input->like),$total);
					}
					else Response::json(array('total' => $total));
				}
				// Return Not Found
				else Response::json(array('error' => 404), 404);
			}
		}
		exit;
	}
} // END class