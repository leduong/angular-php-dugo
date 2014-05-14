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

class Controller_Api_Follow extends Controller
{
	public function create()
	{
		if(AJAX_REQUEST&&POST){
			$in = input();
			if (isset($in->follow)){
				// Check User via Cookie
				$u = @unserialize(cookie::get('user'));
				$tag = Model_Tags::fetch(array('slug' => $in->follow),1);
				if ($tag) $tag_id = $tag[0]->id;
				if (isset($tag_id)) if(isset($u['idu'])&&(int)$u['idu']){
					// Cache :: Total
					$total = Cache::get('follow.'.$in->follow);
					if(!$total){
						$total = Model_Follows::count(array('tag_id' => $tag_id));
						Cache::set('follow.'.$in->follow,$total);
					}
					if($followed = Model_Follows::fetch(array('by' => $u['idu'], 'tag_id' => $tag_id),1)){
						Response::json(array('total' => $total));
					}
					else{
						$follow         = new Model_Follows();
						$follow->by     = $u['idu'];
						$follow->tag_id = $tag_id;
						$follow->save();
						Response::json(array('total' => $total+1));
					}
				}
				else Response::json(array('total' => 0), 403);
			}
			// Return 0
			else Response::json(array('total' => 0), 403);
		}
		exit;
	}

	public function read()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				if (isset($in->follow)){
					// Cache :: Total
					$total = Cache::get('follow'.$in->follow);
					if(!$total){
						$total = Model_Follows::count(array('tag_id' => $in->follow));
						Cache::set('follow'.$in->follow,$total);
					}
					else Response::json(array('total' => $total));
				}
				// Return Not Found
				else Response::json(array('error' => 404), 404);
			}
		}
		exit;
	}
} // END class