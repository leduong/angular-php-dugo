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


/*** for MySQL

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
	`id` int(11) NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



- .

-

-

-

-
***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_Notifications extends APCORM
{
	public static $t = 'notifications'; // Table
	//public static $k = 'id'; // Default 'id' , PRIMARY KEY AUTO_INCREMENT
	//public static $f = 'key_id'; // FOREIGN KEY

	//public static $h = array(); // Relations Ship
	public static function sendmail($type = 0, $to = NULL, $uid = 0, $name = NULL, $link = NULL){
		/*
		type:
			0: Thich dia danh
			1: Trao doi o dia danh
			2: Rao dang o dia danh
			3: Thich tin rao
			4: Binh luan tin rao
			5: Thich trao doi
			6: Binh luan trao doi
		*/
		$ar = array(
			"%s vừa mới thích địa danh %s tại http://www.nhadat.com/t/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới tạo trao đổi trên địa danh %s tại http://www.nhadat.com/t/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới tạo tin rao đăng trên địa danh %s tại http://www.nhadat.com/t/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới thích mẫu tin rao đăng '%s' của bạn tại http://www.nhadat.com/p/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới bình luận mẫu tin rao đăng '%s' của bạn tại http://www.nhadat.com/p/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới thích trao đổi '%s' của bạn tại http://www.nhadat.com/c/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html",
			"%s vừa mới bình luận trao đổi '%s' của bạn tại http://www.nhadat.com/c/%s.html - Nếu bạn không quan tâm nội dung email này, hãy bấm vào đây http://www.nhadat.com/%s.html"
			);
		if (($uid>0)&&($to!=NULL)&&($name!=NULL)&&($link!=NULL)){
			$u = new Model_User($uid);
			$o = end(Model_User::fetch(array('email' => $to, 'disable' => '0'),1));
			if (isset($u->idu)){
				$user = ($u->first_name)?$u->first_name:NULL;
				$user = $user?:substr($u->email, 0, strpos($u->email, "@"));
				$user = $user?:$u->phone;
				$a = new Model_Notifications();
				$a->uid = $o->idu;
				$a->message = sprintf($ar[$type], $user, $name, $link, rand(1000000,9999999));
				$a->save();
				$mail = new Mail();
				$mail->setTo($to);
				$mail->setFrom("cskh@nhadat.com");
				$mail->setSender("nhadat.com");
				$mail->setSubject("Tin nhắn từ nhadat.com");
				$mail->setText(strip_tags(html_entity_decode($a->message, ENT_QUOTES, 'UTF-8')));
				$mail->send();
			}
		}
	}
} // END class
