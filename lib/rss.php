<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2013 by Jason Pell

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
include_once ('./lib/auth.php');
include_once("./lib/database.php");
include_once("./lib/user.php");

function is_exists_opendb_rss_feeds() {
	return is_not_empty_array ( get_opendb_rss_feeds () );
}

function get_opendb_rss_feeds() {
	$feeds_r = array ();
	
	if (is_user_granted_permission ( PERM_VIEW_ANNOUNCEMENTS )) {
		$feeds_r [] = array (
				'feed' => 'announcements',
				'title' => get_opendb_lang_var ( 'announcements' ) );
	}
	
	if (is_user_granted_permission ( PERM_VIEW_LISTINGS )) {
		$feeds_r [] = array (
				'feed' => 'new_items',
				'title' => get_opendb_lang_var ( 'new_items_added' ) );
	}
	
	return $feeds_r;
}

function get_opendb_rss_feed_config($feed) {
	$feeds_r = get_opendb_rss_feeds ();
	reset ( $feeds_r );
	
	$buffer = '';
	foreach ($feeds_r as $feed_r) {
		if ($feed_r ['feed'] == $feed) {
			return $feed_r;
		}
	}
	//else
	return FALSE;
}

function get_opendb_rss_feeds_links($browser = 'firefox') {
	$feeds_r = get_opendb_rss_feeds ();
	reset ( $feeds_r );
	
	$buffer = "\n";

	foreach ($feeds_r as $feed_r) {
		$buffer .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . $feed_r ['title'] . "\" href=\"./rss.php?feed=" . $feed_r ['feed'] . "\">\n";
	}
	return $buffer;
}

?>
