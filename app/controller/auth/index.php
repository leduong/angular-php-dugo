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
		$a = get("v");
		echo '<meta charset="utf-8"><meta http-equiv="refresh" content="1; url=http://www.nhadat.com/newpassword.html">';
		$txt = 'window.setTimeout(location.href = "http://www.nhadat.com/newpassword.html",1000);';
		if($b = Cache::get($a)){
			$auth = new Model_User($b);
			if ($auth->idu){
					if ($auth->verified=='0') {
						$auth->verified = 1;
						$auth->save();
					}
					$user = array();
					foreach((array)$auth->to_array() as $k => $v) if ($k != 'password') $user[$k] = trim($v);
					cookie::set('user',serialize($user));
			}
			$txt .= "var user = JSON.stringify(".json_encode($user).");\n";
			$txt .= "localStorage.setItem('user', user);\n";
			$txt .= "alert('Bấm Ok để chỉ định mật khẩu mới. Xin cám ơn.');";
		}
		echo "<script>$txt</script>";
		exit;
	}

	public function verify(){

	}

	public function login()
	{
		if(AJAX_REQUEST){
			$tpl = new Template("login");
			echo $tpl->make();
			exit;
		}
	}
	public function account()
	{
		if(AJAX_REQUEST){
			$tpl = new Template("account");
			echo $tpl->make();
			exit;
		}
	}
	public function update()
	{
		if(AJAX_REQUEST){
			$tpl = new Template("update");
			echo $tpl->make();
			exit;
		}
	}
	public function newpassword(){
		if(AJAX_REQUEST){
			$tpl = new Template("newpassword");
			echo $tpl->make();
			exit;
		}
	}
} // END class