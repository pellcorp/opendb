<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

	@author Doug Meyers <dgmyrs@users.sourceforge.net>
*/

$_OPENDB_RSS_FEEDS = array(
	array(feed=>'new_items', langvar=>'new_items_added'),
	array(feed=>'announcements', langvar=>'announcements')
);

/*
	This is the script for the announcement table.
*/
include_once("./functions/database.php");
include_once("./functions/user.php");

/**
*/
function get_opendb_rss_feeds()
{
	global $_OPENDB_RSS_FEEDS;
	
	$feeds_r = NULL;
	reset($_OPENDB_RSS_FEEDS);
	while(list(,$feed_r) = each($_OPENDB_RSS_FEEDS))
	{
		$feeds_r[] = array(feed=>$feed_r['feed'], title=>get_opendb_lang_var($feed_r['langvar']));
	}
	
	return $feeds_r;	
}

function get_opendb_rss_feed_config($feed)
{
	$feeds_r = get_opendb_rss_feeds();
	reset($feeds_r);

	$buffer = '';
	while(list(,$feed_r) = each($feeds_r))
	{
		if($feed_r['feed'] == $feed)
		{
			return $feed_r;
		}
	}
	//else
	return FALSE;
}

function get_opendb_rss_feeds_links($browser = 'firefox')
{
	$feeds_r = get_opendb_rss_feeds();
	reset($feeds_r);

	$buffer = "\n";
	
	while(list(,$feed_r) = each($feeds_r))
	{
		$buffer .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".$feed_r['title']."\" href=\"./rss.php?feed=".$feed_r['feed']."\">\n";
	}
	return $buffer;
}

?>