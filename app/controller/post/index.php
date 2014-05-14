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
 * Controller_Welcome_Index class
 *
 * @package Controller_Welcome_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/welcome/index.php
 * @template {{app}}/view/welcome/index.php
 **/

class Controller_Post_Index extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			$tpl = new Template("post");
			echo $tpl->make();
			exit;
		} else {
			$id = substr(url(),2,(strlen(url())-7));
			if (int($id)) {
				$m = new Model_Messages($id);
			} else {
				$m = Model_Messages::fetch(array('link' => $id),1);
				$m = end($m);
			}
			if($m){
				$meta = $keywords = array();
				$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id));
				if ($mt) foreach($mt as $v) $meta[$v->type] = trim($v->value);
				$ar = @explode(",", $m->tag.",".$meta['address'].",".$meta['local'].",".$this->appsite['meta_keywords']);
				foreach ($ar as $a) if ($b=trim($a)) $keywords[] = $b;

				$this->appsite['site_title']       = "Mua bán nhà đất, Bất động sản ".implode(", ", array_slice(array_unique($keywords),0,5));
				$this->appsite['meta_keywords']    = implode(", ", array_unique($keywords));
				$this->appsite['meta_description'] = substr(substr($m->message,0,256),0,strrpos(substr($m->message,0,256)," "));
			}
			$this->content = '';
		}
	}
} // END class