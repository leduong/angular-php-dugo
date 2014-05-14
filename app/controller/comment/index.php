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
 * Controller_Comment_Index class
 *
 * @package Controller_Comment_Index
 * @author [author] <[email]>
 * @filename {{app}}/controller/comment/index.php
 * @template {{app}}/view/share/index.php
 **/

class Controller_Comment_Index extends Controller
{
	public function index()
	{
		if(AJAX_REQUEST){
			if(POST){
			}else{
				$tpl = new Template("comment");
				echo $tpl->make();
			}
			exit;
		}
		else{
			$id = substr(url(),2,(strlen(url())-7));
			if (int($id)) {
				$m = new Model_Messages($id);
			} else {
				$m = Model_Messages::fetch(array('link' => $id),1);
				$m = end($m);
			}
			if($m){
				$meta = array();
				$mt = Model_MessagesMeta::fetch(array('msg_id' => $m->id));
				if ($mt) foreach($mt as $v) $meta[$v->type] = trim($v->value);
				$keywords = trim(implode(", ", @explode(",", $m->tag.",".$meta['address'].",".$meta['local'])));
				$description = substr(substr($m->message,0,256),0,strrpos(substr($m->message,0,256)," "));
				$this->appsite['site_title'] = ($keywords)?$keywords:$description;
				$this->appsite['meta_keywords'] = ($keywords)?$keywords:$description;
				$this->appsite['meta_description'] = $description;
			}
			$this->content = '';
		}
	}
} // END class