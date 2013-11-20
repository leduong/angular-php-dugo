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
 * Controller_Api_User class
 *
 * @package Controller_Api_User
 * @author [author] <[email]>
 * @filename {{app}}/controller/api/user.php
 * @template {{app}}/view/api/user.php
 **/

class Controller_Api_User extends Controller
{
	public function lists()
	{
		if(AJAX_REQUEST){
			if(POST){
				$list = array();
				if ($ar = Model_User::fetch(array('agent' => 1))) foreach ($ar as $a){
					$user = array();
					foreach($a->to_array() as $k => $v) if ($k != 'password') $user[$k] = $v;
					$list[] = $user;
				}

				if($list){
					Response::json(array('user' => $list));
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}

	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$find = sprintf(
					"`username` = '%s' OR `email` = '%s' OR `phone` = '%s'",
					$input->username, $input->email, $input->phone);
				$count = Model_User::count($find);
				if($count>0){
					Response::json(array('flash' => 'already_registered'));
				} else{
					$password = substr(sha1(mt_rand()), 17, 6);
					$user = new Model_User();
					foreach ((array)$input as $key => $value) {
						$user->$key = (isset($user->$key)&&($user->$key!=$value))?$value:$user->$key;
					}
					$user->password = md5($password);
					$user->save();
					Response::json(array('flash' => 'successful', 'user' => $user->to_array()));
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
				if(isset($input->id)&&is_numeric($input->id)){
					$user = new Model_User($input->id);
				} else {
					$user = Model_User::fetch(array(
						'username' => str_replace('.html','',$input->username),1
					));
					$user = end($user);
					$user = $user->to_array();
					$user['max'] = 5;
					$user['rate'] = 0;
					$user['isReadonly'] = false;
				}
				if($user){
					Response::json(array('user' => $user));
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
				$user = new Model_User($input->id);
				if($user&&controller_auth::check($user->idu)){
					foreach ((array)$input as $key => $value) {
						$user->$key = (isset($user->$key)&&($user->$key!=$value))?$value:$user->$key;
					}
					$user->save();
					Response::json(array('flash' => 'success', 'user' => $user->to_array()));
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
				$user = new Model_User($input->id);
				if($user&&controller_auth::check($user->idu)){
					$user->delete();
					Response::json(array('flash' => 'success'));
				} else{
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}
} // END class