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
 * Controller_Api_Contact class
 *
 * @package Controller_Api_Contact
 * @author [author] <[email]>
 * @filename {{app}}/controller/welcome/index.php
 * @template {{app}}/view/welcome/index.php
 **/

class Controller_Api_Contact extends Controller
{
  public function index() {
		if(AJAX_REQUEST&&POST){
			$in = input();
			$message = sprintf("Họ tên: %s\nEmail hoặc Số điện thoại: %s\n\nNội dung:\n%s\n\n\n--\n%s",
				$in->fullname,
				$in->email,
				$in->message,
				DOMAIN);
			$mail = new Mail();
			/*if(is_array($smtp)){
				$mail->protocol = 'smtp';
				$mail->hostname = $smtp['smtp_hostname'];
				$mail->username = $smtp['smtp_username'];
				$mail->password = $smtp['smtp_password'];
			}*/
			$mail->setTo($this->appsite['email']);
			$mail->setFrom($in->email);
			$mail->setSender($in->fullname);
			$mail->setSubject(sprintf('%s gởi thông tin từ website.', implode(", ", $in->select)));
			$mail->setText(html_entity_decode($in->message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
			Response::json(array('flash' => 'Nội dung đã được gởi. Cám ơn'));
		}
		exit;
	}
} // END class