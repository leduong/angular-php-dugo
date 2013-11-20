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
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$u     = unserialize(cookie::get('user'));
				if(($u['idu'])&&isset($input->like)){
					$like       = new Model_Likes();
					$like->by   = $u['idu'];
					$like->post = $input->like;
					$like->save();
					$total = Model_Likes::count(array('post' => $input->like));
					Response::json(array('total' => $total));
				}
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
				$like = new Model_Likes($input->id);
				if ($like){
					Response::json(array(
						'flash' => 'success',
						'like' => $like->to_array(),
					));
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
				$like = new Model_Likes($input->id);
				if ($cmt){
					foreach ((array)$input as $key => $value) {
						$like->$key = (isset($like->$key)&&($like->$key!=$value))?$value:$like->$key;
					}
					$like->save();
					Response::json(array(
						'flash' => 'success',
						'like' => $like->to_array(),
					));
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
				$like = new Model_Likes($input->id);
				if ($like){
					$like->delete();
					Response::json(array('flash' => 'success'));
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}
} // END class