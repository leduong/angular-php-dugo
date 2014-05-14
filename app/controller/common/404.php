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
 * Controller_Common_404 class
 *
 * @package Controller_Common_404
 * @author [author] <[email]>
 * @filename {{app}}/controller/common/404.php
 * @template {{app}}/view/common/404.php
 **/

class Controller_Common_404 extends Controller
{
  public function index()
  {
    //$this->content = new View('common/404');
    if(AJAX_REQUEST){
  		$tpl = new Template("index");
		echo $tpl->make();
		exit;
  	} else $this->content = '';
  }
} // END class