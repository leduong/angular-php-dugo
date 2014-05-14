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


/*** for MySQL

--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `idu` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(32) NOT NULL DEFAULT 'Khách vãng lai',
  `last_name` varchar(32) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `opt` char(4) NOT NULL,
  `email` varchar(256) NOT NULL,
  `facebook` varchar(256) NOT NULL,
  `twitter` varchar(128) NOT NULL,
  `gplus` varchar(256) NOT NULL,
  `website` varchar(128) NOT NULL,
  `bio` varchar(160) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `location` varchar(128) NOT NULL,
  `verified` enum('0','1') NOT NULL,
  `privacy` enum('0','1') NOT NULL,
  `gender` enum('-','male','female') NOT NULL,
  `agent` enum('0','1') NOT NULL,
  `online` enum('0','1') NOT NULL,
  `email_comment` tinyint(4) NOT NULL,
  `email_like` int(11) NOT NULL,
  `cover` varchar(128) NOT NULL DEFAULT 'default.png',
  `image` varchar(128) NOT NULL DEFAULT 'default.png',
  `born` date NOT NULL,
  `date_join` date NOT NULL,
  `date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idu`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

***/


/**
 * Model class
 *
 * @package default
 * @author
 **/
class Model_User extends APCORM
{
  public static $t = 'users';
  public static $k = 'idu'; // KEY

  public static $h = array();
} // END class
