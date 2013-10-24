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
* Controller_Auth_Register class
*
* @package Controller_Auth_Register
* @author [author] <[email]>
* @filename {{app}}/controller/auth/register.php
* @template {{app}}/view/auth/register.php
**/
class Controller_Auth_Register extends Controller
{
	public function index()
	{
		//$this->content = new View('auth/register');
		if(AJAX_REQUEST){
			if(POST){
				$Input = input();
				$find = sprintf("`username` = '%s' OR `email` = '%s'", $Input->username, $Input->email);
				$count = Model_User::count($find);
				if($count>0){
					Response::json(array('flash' => 'already_registered'));
				} else{
					$password = substr(sha1(mt_rand()), 17, 6);
					$u = new Model_User();
					$u->username = $Input->username;
					$u->email = $Input->email;
					$u->password = md5($password);
					$u->phone = $Input->phone;
					$u->save();

					if (config('mail_registered')){
						$message = sprintf(lang('mail_registered'),
							$Input->username, $Input->email, $password, DOMAIN);
						$mail = new Mail();
						$mail->setTo($Input->email);
						$mail->setFrom($this->appsite['email']);
						$mail->setSender(sprintf('Website %s', DOMAIN));
						$mail->setSubject(sprintf(lang('mail_registered_subject'), DOMAIN));
						$mail->setText(strip_tags(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
						$mail->send();
					}
					Response::json(array('flash' => 'successfully_registered'));
				}
			}
			else{
				$tpl = new Template('auth/register');
				echo $tpl->make();
			}
			exit;
		}
	}
} // END class