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
 * Controller_Api_City class
 *
 * @package Controller_Welcome_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/api/city.php
 * @template {{app}}/view/api/city.php
 **/

class Controller_Api_Upload extends Controller
{
	public function index()
	{
		$files = @$_FILES["uploader"];
		$array = array();
		if($files){
			for ($i=0; $i < count($files); $i++) {
				$file = array(
					"name"     => @$files["name"][$i],
					"type"     => @$files["type"][$i],
					"tmp_name" => @$files["tmp_name"][$i],
					"error"    => @$files["error"][$i],
					"size"     => @$files["size"][$i]
				);
				if ($upload = upload::file($file, UPLOAD."media/".date('Y-m-d')."/")){
					$array[] = str_replace(UPLOAD.'media/','',$upload);
				}
			}
		}
		Response::json(array('images' => $array));
		exit();
	}
} // END class