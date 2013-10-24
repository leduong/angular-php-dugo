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
 * Controller_Welcome_Index class
 *
 * @package Controller_Auth_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/auth/index.php
 * @template {{app}}/view/auth/index.html
 **/

class Controller_Auth_Index extends Controller
{
	public function index()
	{
		Response::json(array(), 403);
		exit;
	}

	public function status() {
		exit;
		//return Response::json(Auth::check());
	}

	public function login()
	{
		if(AJAX_REQUEST){
			if(POST){
				$request = input();
				$a = sprintf(
					"`username` = '%s' OR `email` = '%s' OR `phone` = '%s'",
					$request->email, $request->email, $request->email);
				$find = array($a);
				$fetch = Model_User::fetch($find,1);
				if ($fetch){
					$auth = $fetch[0];
					if ($auth->password == md5($request->password)){
						$user = array();
						foreach((array)$auth->d as $k => $v){
							if ($k != 'password') $user[$k] = $v;
						}
						cookie::set('user',serialize((array)$request));
						Response::json(array('user' => $user, 'flash' => 'Login success'));
					}
					else{
						Response::json(array('flash' => 'Invalid username or password'), 401);
					}
				} else{
					Response::json(array('flash' => 'Invalid username'), 401);
				}
			}
			else{
				$tpl = new Template("auth/login");
				echo $tpl->make();
			}
			exit;
		}
	}

	public function logout()
	{
		cookie::set('user',NULL);
		Response::json(array('flash' => 'Logged Out!'));
		exit;
	}
} // END class