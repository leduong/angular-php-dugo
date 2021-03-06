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
 * Controller_Contact_Index class
 *
 * @package Controller_Contact_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/welcome/index.php
 * @template {{app}}/view/welcome/index.php
 **/

class Controller_Contact_Index extends Controller
{
  public function index() {
		if(AJAX_REQUEST){
			if(POST){
				$in = input();
				$message = sprintf("Họ tên: %s\nEmail: %s\nĐịa chỉ: %s\nĐiện thoại: %s\n\nNội dung:\n%s\n\n\n--\n%s",
					$in->full_name,
					$in->email,
					$in->address,
					$in->phone,
					$in->message,
					DOMAIN);
				$mail = new Mail();
				if(is_array($smtp)){
					$mail->protocol = 'smtp';
					$mail->hostname = $smtp['smtp_hostname'];
					$mail->username = $smtp['smtp_username'];
					$mail->password = $smtp['smtp_password'];
				}
				$mail->setTo($this->appsite['email']);
				$mail->setFrom($in->email);
				$mail->setSender($in->full_name);
				$mail->setSubject(sprintf('Liên hệ: %s gởi thông tin từ website.', $in->full_name));
				$mail->setText(html_entity_decode($in->message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				Response::json(array('flash' => 'sent'));
			}
			else{
				$tpl = new Template("contact");
				echo $tpl->make();
			}
			exit;
		}
		else $this->content = '';
  }
} // END class