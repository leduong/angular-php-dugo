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
 * Controller_Api_Silder class
 *
 * @package Controller_Welcome_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/api/slider.php
 * @template {{app}}/view/api/slider.php
 **/

class Controller_Api_Slider extends Controller
{
	public function index(){
		$array = array();
		if(AJAX_REQUEST){
			/*
			$limit  = 3;
			$page   = ((int)get('page')>1)?(int)get('page'):1;
			$offset = $limit*($page-1);
			$sort   = array('id' => 'DESC');
			$where  = array("type" => "realestate", "`value` REGEXP '.jpg' OR `value` REGEXP '.png'");
			*/
			//if ($fetch=Model_Messages::fetch($where, $limit, $offset, $sort)){
			$fetch = @explode(",", $this->appsite['slider']);
			if ($fetch) {
				foreach ($fetch as $msg_id) if ((int)$msg_id) {
					$m = new Model_Messages((int)$msg_id);

					$image = array();
					$value = explode(',', $m->value);
					foreach ($value as $v) if ($v=trim($v)) {
						$p=pathinfo($v);
						if (@in_array($p['extension'], explode('|','gif|jpg|jpeg|png'))){
							$image[] = $v;
						}
					}
					/*$u = new Model_User($m->uid);
					$tag = $meta = $image = array();
					foreach (array_slice(explode(',',$m->tag),0,2) as $v) {
						$tag[string::slug($v)] = trim($v);
					}
					if ($mt = Model_MessagesMeta::fetch(array('msg_id'=>$m->id))) foreach($mt as $b){
						$meta[$b->type] = mb_convert_case($b->value, MB_CASE_TITLE, "UTF-8");
					}

					$array[]= array(
						'id'   => ($m->link)?$m->link:$m->id,
						'img'  => ($image)?current($image):'default.png',
						'tag'  => $tag,
						'meta' => $meta,
						'text' => mb_convert_case($m->message, MB_CASE_TITLE, "UTF-8"),
						'user' => array(
							'username' => $u->username,
							'name'     => $u->first_name." ".$u->last_name,
							'image'    => $u->image,
							'phone'    => $u->phone,
							),
					);*/
					$array[]= array(
						'id'   => ($m->link)?$m->link:$m->id,
						'img'  => ($image)?current($image):'default.png',
						'type' => ($m->type=='realestate')?'p':'c'
					);
				}
				Response::json($array);
				exit;
			}
			Response::json($array,404);
		}
		exit;
	}
}