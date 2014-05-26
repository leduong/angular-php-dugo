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
 * Controller_Tag_Index class
 *
 * @package Controller_Tag_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/tag/index.php
 * @template {{app}}/view/tag/index.php
 **/

class Controller_Tag_Index extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			$tpl = new Template("tag");
			echo $tpl->make();
			exit;
		} else {
			$slug = substr(url(),2,(strlen(url())-7));
			$keywords = $ar = array();
			$all = Model_TagsGroup::get_array($slug);
			if($fetch = Model_Group::fetch(array('slug' => $slug),1)){
				$a   = end($fetch);
				$str = $a->name.",".$a->long_name.",".$a->tag.",".$a->address.",".$a->local;
				$ar  = @explode(",", $str);
			}
			else if($fetch = Model_City::fetch(array('slug' => $slug),1)){
				$a  = end($fetch);
				$ar = @explode(",", $a->name);
			}
			else if($fetch = Model_District::fetch(array('slug' => $slug),1)){
				$a    = end($fetch);
				$city = new Model_City($a->city_id);
				$ar   = @explode(",", $a->name.", ".$city->name);
			}
			else if($fetch = Model_Zipcode::fetch(array('slug' => $slug),1)){
				$a  = end($fetch);
				$ar = @explode(",", $a->full_name);
			} else if ($fetch = Model_Tags::fetch(array('slug' => $slug),1)) {
				$a  = end($fetch);
				$ar = @explode(",", $a->name);
			}
			$arr = array_merge(array("Mua bán nhà đất","Bất động sản "), $ar, $all, explode(",", $this->appsite['meta_keywords']));
			foreach ($arr as $a) if ($b=string::slug($a)) $keywords[] = str_replace("-", " ", $b);
			$this->appsite['site_title']       = implode(", ", array_unique($keywords));
			$this->appsite['meta_keywords']    = implode(", ", array_unique($keywords));
			$this->appsite['meta_description'] = implode(", ", array_unique($keywords));
			$this->content = $slug;
		}
	}
} // END class
