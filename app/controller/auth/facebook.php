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
 * Controller_Auth_Facebook class
 *
 * @package Controller_Auth_Facebook
 * @author [author] <[email]>
 * @filename {{app}}/controller/auth/facebook.php
 * @template {{app}}/view/auth/facebook.php
 **/

class Controller_Auth_Facebook extends Controller
{
	public function index()
	{
		$this->content = new View('auth/facebook');
		$Input = input();
		$databaseUsers = ["12345465432", "234565432", "14424"];
		/*
		 * WARNING!
		 * If a new user exists in $databaseUsers or
		 * if an authorized user does not,
		 * the app will break!
		 * seems logical uh?
		 */


		if (isset($Input->facebook_id)) {
			if (in_array($Input->facebook_id, $databaseUsers)) {
				// users is logged in we will perform a query to get the data needed and will put it in the $_SESSION array
				// in order to return it to angular.
				$_SESSION['id'] = "some id";
				$_SESSION['facebook_id'] = $Input->facebook_id;
				$_SESSION['first_name'] = "some name";
				$_SESSION['last_name'] = "some last name";
				$_SESSION['picture'] = "a lookign good picture";
				$_SESSION['user_is'] = "existing user";
				echo "existing";
			} else {
				// user is not in our db, so if we have more than his facebook_id, we can write him to our database...
				if (isset($Input->last_name) && isset($Input->first_name) && isset($Input->picture)) {
					//write user to db and put it the data in the $_SESSION array in order to return it to angular.
					$_SESSION['id'] = "some id";
					$_SESSION['facebook_id'] = $Input->facebook_id;
					$_SESSION['first_name'] = $Input->first_name;
					$_SESSION['last_name'] = $Input->last_name;
					$_SESSION['picture'] = $Input->picture;
					$_SESSION['user_is'] = "new user";
					echo "new";
				} else {
					//not enough data gathered in order to write a new user;
					echo "not enough data";
				}
			}
		}
		else {
			echo "no facebook data";
		}
	}
} // END class