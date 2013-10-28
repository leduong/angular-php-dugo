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
 * Controller_Api_Group class
 *
 * @package Controller_Welcome_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/api/group.php
 * @template {{app}}/view/api/group.php
 **/

class Controller_Api_Group extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			if(POST){
				$input = input();
				if(isset($input->slug)) if($fetch = Model_Group::fetch(array('slug' => str_replace('.html','',$input->slug)),1)){
					$group = $fetch[0]->to_array();
					$group['map'] = explode(",",$group["map"]);
					Response::json(array('group' => $group));
				} else{
					Response::json(array('group' => array()),404);
				}
			}
		}
		exit();
	}
} // END class