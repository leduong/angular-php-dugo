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
 * Controller_Profile_Index class
 *
 * @package Controller_Profile_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/profile/index.php
 * @template {{app}}/view/profile/index.php
 **/

class Controller_Profile_Index extends Controller
{
  public function index()
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
			}

			if($user){
				$slider     = array();
				$where      = array('uid' => $user->idu, 'type' => 'realestate');
				$status     = Model_Messages::count(array('uid' => $user->idu, 'type' => 'status'));
				$realestate = Model_Messages::count($where);
				if ($fetch=Model_Messages::fetch($where, 10, 0, array('id' => 'DESC'))){
					foreach ($fetch as $m) {
						$tag = $meta = array();
						foreach (array_slice(explode(',',$m->tag),0,2) as $v) {
							$tag[string::slug($v)] = trim($v);
						}
						if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
							$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
						}
						$slider[]= array(
							'img'  => ($m->value)?$m->value:'default.png',
							'tag'  => $tag,
							'meta' => $meta,
							'text' => mb_convert_case($m->message, MB_CASE_TITLE, "UTF-8"),
							'user' => array(
								'username' => $user->username,
								'name'     => $user->first_name." ".$user->last_name,
								'image'    => $user->image,
								'phone'    => $user->phone,
								),
						);
					}
				}
				Response::json(array(
						'user'   => $user->to_array(),
						'slider' => $slider,
						'stats'  => array(
							'status'     => $status,
							'realestate' => $realestate
							),
						)
					);
			} else{
				Response::json(array(),404);
			}
  		} else {
			$tpl = new Template("profile/index");
			echo $tpl->make();
  		}
		exit;
	} else $this->content = '';
  }
} // END class