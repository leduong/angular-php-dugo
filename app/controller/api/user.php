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
	public function verify() {
		if(AJAX_REQUEST&&POST){
			$in    = input();
			$u     = @unserialize(cookie::get('user'));
			$email = (isset($in->email))?trim(strtolower($in->email)):NULL;
			$phone = (isset($in->phone))?numify($in->phone):NULL;
			if(is_numeric($u["idu"])&&vnphone($phone)){
				$user  = Model_User::fetch(array('phone' => $phone, 'disable' => '0'),1);
				if ($user) {
					Response::json(array('flash' => 'Số điện thoại này đã sử dụng.'), 403);
					exit;
				} else {
					$rand          = rand(1000,9999);
					$user          = new Model_User();
					$user->phone   = $phone;
					$user->otp     = $rand;
					$user->disable = '1';
					$user->save();

					$sms = str_replace(" ", "%20", sprintf($this->appsite['sms_verify'],$rand, $rand));
					$sendsms = file_get_contents("http://center.fibosms.com/Service.asmx/SendSMS?&clientNo=CL8852&clientPass=28mCDiad&smsGUID=".uuid()."&serviceType=1&phoneNumber=$phone&smsMessage=$sms");
					//@log_message("$phone - $sms - $sendsms");
					//die();
					Response::json(array(
						'phone' => $phone,
						'flash' => 'Vui lòng kiểm tra tin nhắn bằng số điện thoại "'.$phone.'".'));
					exit;
				}
			}
			if (is_numeric($u["idu"])&&preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
				$user  = Model_User::fetch(array('email' => $email, 'disable' => '0'),1);
				$_user = new Model_User($u["idu"]);
				$rand  = rand(1000,9999);
				if ($user) {
					Response::json(array('flash' => 'E-mail này đã sử dụng.'), 403);
					exit;
				} else {
					$user  = Model_User::fetch(array('email' => $email),1);
					if($user){
						$user = end($user);
					} else {
						$user          = new Model_User();
						$user->email   = $email;
						$user->disable = '1';
					}
					$user->otp = $rand;
					$user->save();

					if ($this->appsite['email_confirm']){
						$message = sprintf($this->appsite['email_confirm'], $rand, $rand);
						$mail = new Mail();
						$mail->setTo($email);
						$mail->setFrom($this->appsite['email']);
						$mail->setSender($this->appsite['domain']);
						$mail->setSubject($this->appsite['email_subject']);
						$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
						$mail->send();
					}
					Response::json(array(
						'email' => $email,
						'flash' => "Mã xác nhận đã được gởi đến địa chỉ $email."));
					exit;
				}
			}
			Response::json(array('flash' => 'Thay đổi thông tin không hợp lệ.'), 403);
		}
		exit;
	}
	public function confirm() {
		if(AJAX_REQUEST&&POST){
			$in    = input();
			$u     = @unserialize(cookie::get('user'));
			$email = (isset($in->email))?trim(strtolower($in->email)):NULL;
			$phone = (isset($in->phone))?numify($in->phone):NULL;
			if(is_numeric($u["idu"])&&vnphone($phone)){
				$user  = Model_User::fetch(array('phone' => $phone, 'otp' => $in->otp),1);
				$_user = new Model_User($u["idu"]);
				if ($user&&$_user){
					$user          = end($user);
					$user->phone   = $_user->phone;
					$user->disable = '1';
					$user->save();

					$_user->phone  = $phone;
					$_user->save();
					$json = array();
					foreach($_user->to_array() as $k => $v) if ($k != 'password') $json[$k] = trim($v);
					Response::json(array(
						'flash' => 'Thông tin đã được cập nhập thành công.',
						'user' => $json));
				} else {
					Response::json(array('flash' => 'Thông tin cập nhập không thành công.'), 403);
				}
			}

			if (is_numeric($u["idu"])&&preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
				$user  = Model_User::fetch(array('email' => $email, 'otp' => $in->otp),1);
				$_user = new Model_User($u["idu"]);
				if ($user&&$_user){
					$user          = end($user);
					$user->email   = $_user->email;
					$user->disable = '1';
					$user->save();

					$_user->email  = $email;
					$_user->save();
					$json = array();
					foreach($_user->to_array() as $k => $v) if ($k != 'password') $json[$k] = trim($v);
					Response::json(array(
						'flash' => 'Thông tin đã được cập nhập thành công.',
						'user' => $json));
				} else {
					Response::json(array('flash' => 'Thông tin cập nhập không thành công.'), 403);
				}
			}
		}
		exit;
	}
	public function status() {
		$in = input();
		if(isset($in->user)&&($u = trim($in->user))){
			$phone = (numify($u)!='')?numify($u):$u;
			$a = sprintf("`username` = '%s' OR `email` = '%s' OR `phone` = '%s'", strtolower($u), strtolower($u), $phone);
			$where = array($a);
			if (Model_User::fetch($where,1)){
				if (is_numeric($phone)&&vnphone($phone)){
					Response::json(array('user' => 'phone'));
				} elseif (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $u)) {
					Response::json(array('user' => 'email'));
				} else Response::json(array('user' => 'User Exist'));
				exit;
			}
			else {
				$rand = rand(1000,9999);
				if (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $u)) {
					$user        = new Model_User();
					$user->email = strtolower($u);
					$user->otp   = $rand;
					$user->save();

					$code = substr(md5($user->idu), rand(1,15), 8);
					Cache::set($code,$user->idu,600);

					if ($this->appsite['email_body']){
						$message = sprintf($this->appsite['email_body'], $code, $rand);
						$mail = new Mail();
						$mail->setTo($u);
						$mail->setFrom($this->appsite['email']);
						$mail->setSender($this->appsite['domain']);
						$mail->setSubject($this->appsite['email_subject']);
						$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
						$mail->send();
					}

					Response::json(array(
						'user' => 'email',
						'flash' => 'Vui lòng kiểm tra e-mail "'.strtolower($u).'".'), 404);
					exit;
				} elseif (is_numeric($phone)&&vnphone($phone)){
					$user_exist = Model_User::fetch(array('phone' => $phone),1);
					if ($user_exist){
						$user      = $user_exist[0];
						$user->otp = $rand;
						$user->save();
					} else {
						$user        = new Model_User();
						$user->phone = $phone;
						$user->otp   = $rand;
						$user->save();
					}
					$code = substr(md5($user->idu), rand(1,15), 8);
					Cache::set($code,$user->idu,600);
					$sms = str_replace(" ", "%20", sprintf($this->appsite['sms_confirm'],$rand, $code));
					$sendsms = @file_get_contents("http://center.fibosms.com/Service.asmx/SendSMS?&clientNo=CL8852&clientPass=28mCDiad&smsGUID=".uuid()."&serviceType=1&phoneNumber=$phone&smsMessage=$sms");
					//@log_message("$phone - $sms - $sendsms");
					Response::json(array(
						'user' => 'phone',
						'flash' => 'Vui lòng kiểm tra tin nhắn bằng số điện thoại "'.$phone.'".'),404);
					exit;
				}
			}
		}
		Response::json(array(), 403);
		exit;
	}

	public function update(){
		if(AJAX_REQUEST&&POST){
			$u = @unserialize(cookie::get('user'));
			$in = input();
			if((int)$u['idu']){ // If exist User
				$user = new Model_User($u['idu']);
				if ($user) {
					if (isset($in->phone)&&!empty($in->phone)&&($user->phone!=$in->phone)){
						$phone = numify($in->phone);
						if(vnphone($phone)){
							$fetch = Model_User::fetch(array('phone' => $phone, 'disable' => '0'),1);
							if ($fetch){
								Response::json(array('flash' => 'Số điện thoại này đã sử dụng.'), 403);
								exit;
							}
							$user->phone = $phone;
						} else {
							Response::json(array('flash' => 'Số điện thoại không hợp lệ.'), 403);
							exit;
						}
					}
					if (isset($in->email)&&($user->email!=$in->email)){
						$fetch = Model_User::fetch(array('email' => $in->email, 'disable' => '0'),1);
						if ($fetch){
							Response::json(array('flash' => 'E-mail này đã sử dụng.'), 403);
							exit;
						}
						$user->email = strtolower($in->email);
					}
					if (isset($in->password)&&!empty($in->password)){
						if (isset($in->repassword)&&($in->password==$in->repassword)){
							if (empty($user->password)||($user->password==md5($in->current))){
								if (strlen($in->password)>=6){
									$user->password = md5($in->password);
								}
								else {
									Response::json(array('flash' => 'Mật khẩu quá ngắn, ít nhất 6 ký tự.'), 403);
									exit;
								}
							} else {
								Response::json(array('flash' => 'Mật khẩu hiện tại không chính xác.'), 403);
								exit;
							}
						} else {
							Response::json(array('flash' => 'Mật khẩu không khớp.'), 403);
							exit;
						}
					}
					if (isset($in->first_name)&&($user->first_name!=$in->first_name)){
						$user->first_name = trim($in->first_name);
					}
					$user->save();
					$json = array();
					$ar = $user->to_array();
					foreach($ar as $k => $v) if ($k != 'password') $json[$k] = trim($v);
					Response::json(array(
						'flash' => 'Thông tin đã được cập nhập thành công.',
						'user' => $json));
				} else {
					Response::json(array('flash' => 'Bạn cần thoát ra và đăng nhập lại.'), 403);
				}
			} else {
				Response::json(array('flash' => 'Bạn chưa đăng nhập.'), 403);
			}
		}
		exit;
	}

	public function login()
	{
		if(AJAX_REQUEST&&POST){
			$in = input();
			if(isset($in->user)&&isset($in->password)&&($u = trim($in->user))){
				$phone = (numify($u)!='')?numify($u):$u;
				$a = sprintf("username = '%s' OR email = '%s' OR phone = '%s'",	$u, $u, $phone);
				$where = array($a, 'disable' => '0');
				$fetch = Model_User::fetch($where,1);
				if ($fetch){
					$auth    = end($fetch);
					$user    = array();
					$time    = new Time($auth->date_update);
					$expired = ($time->getTimestamp() - time() + 600);
					foreach((array)$auth->to_array() as $k => $v) if ($k != 'password') $user[$k] = trim($v);
					if ($auth->password == md5($in->password)){
						cookie::set('user',serialize($user));
						Response::json(array('user' => $user, 'flash' => 'Đăng nhập thành công'));
					}
					elseif (($expired>0)&&($auth->otp == $in->password)&&(int)$in->password){
						if ($auth->verified=='0') {
							$auth->verified = 1;
						}
						$auth->otp       = NULL;
						$auth->date_join = date("Y-m-d");
						$auth->save();
						cookie::set('user',serialize($user));
						Response::json(array('user' => $user, 'flash' => 'Đăng nhập thành công'));
					}
					else{
						Response::json(array('flash' => 'Đăng nhập không thành công'), 401);
					}
				} else{
					Response::json(array('flash' => 'Đăng nhập không thành công'), 401);
				}
			}
		}
		exit;
	}

	public function sendmail(){
		if(AJAX_REQUEST&&POST){
			$in = input();
			if(isset($in->user)){
				$phone = numify($in->user);
				$rand = rand(1000,9999);
				if (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $in->user)) {

					$user = Model_User::fetch(array('email' => $in->user),1);
					if ($user){
						$user = end($user);
					} else {
						$user = new Model_User();
					}
					$user->email    = $in->user;
					$user->otp      = $rand;
					$user->password = NULL;
					$user->save();
					$code = substr(md5($user->idu), rand(1,15), 8);
					Cache::set($code,$user->idu,600);
					if ($this->appsite['email_body']){
						$message = sprintf($this->appsite['email_body'], $code, $rand);
						$mail = new Mail();
						$mail->setTo($in->user);
						$mail->setFrom($this->appsite['email']);
						$mail->setSender($this->appsite['domain']);
						$mail->setSubject($this->appsite['email_subject']);
						$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
						$mail->send();
					}
					Response::json(array('flash' => 'Mật khẩu mới đã được gởi đến "'.$in->user.'" và chỉ có hiệu lực trong vòng 10 phút.'));
				} elseif (vnphone($phone)) {
					$check = Cache::get("phone".$phone);
					if($check){
						Response::json(array('flash' => 'Hãy thử lại sau 10 phút. Bạn không sử dụng quá 2 lần trong 10 phút.'));
					} else {
						$user = Model_User::fetch(array('phone' => $phone),1);
						if ($user){
							$user = end($user);
						} else {
							$user = new Model_User();
						}
						$user->phone    = $phone;
						$user->otp      = $rand;
						$user->password = NULL;
						$user->save();
						$code = substr(md5($user->idu), rand(1,15), 8);
						Cache::set($code,$user->idu,600);
						$sms = str_replace(" ", "%20", sprintf($this->appsite['sms_confirm'],$rand, $code));

						Response::json(array('flash' => 'Mật khẩu mới đã được gởi đến "'.$in->user.'" và chỉ có hiệu lực trong vòng 10 phút.'));
						$sendsms = file_get_contents("http://center.fibosms.com/Service.asmx/SendSMS?&clientNo=CL8852&clientPass=28mCDiad&smsGUID=".uuid()."&serviceType=1&phoneNumber=$phone&smsMessage=$sms");
						//@log_message("$phone - $sms - $sendsms");
						Cache::set("phone".$phone,true,600);
					}
				}  else {
					Response::json(array('flash' => 'E-mail không hợp lệ.'), 404);
				}
			}
		}
		exit;
	}

	public function facebook()
	{
		if(AJAX_REQUEST&&POST){
			$in = input();
			if(isset($in->facebook_id)){
			}
		}
		exit;
	}

	public function logout()
	{
		cookie::set('user',NULL);
		Response::json(array('flash' => 'Logged Out!'));
		exit;
	}

	public function guest()
	{
		$ssid = session_id();
		cookie::set('user', $ssid);
		Response::json(array('user' => $ssid, 'flash' => 'Guest!'));
		exit;
	}

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
	public function username(){
		if(AJAX_REQUEST){
			$in = input();
			if(isset($in->username)){
				$array = array();
				$fetch = Model_User::fetch(array('username' => $in->username),1);
				if ($fetch) foreach ($fetch as $f) $array[] = $f->to_array();
				Response::json(array('results' => $array));
			}
		}
		exit;
	}
	public function create()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$find = sprintf("email = '%s' OR phone = '%s'", $in->email, $in->phone);
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
				if(isset($in->id)&&is_numeric($in->id)){
					$user = new Model_User($in->id);
				} else {
					$user = Model_User::fetch(array(
						'username' => str_replace('.html','',$in->username),1
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

	public function destroy()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				$user = new Model_User($in->id);
				if($user&&controller_auth::check($user->idu)){
					$user->delete();
					Response::json(array('flash' => 'success'));
				} else {
					Response::json(array('flash' => 'not_found'),404);
				}
			}
		}
		exit;
	}
} // END class
