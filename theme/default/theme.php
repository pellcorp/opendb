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
*/
function theme_header($pageid, $title, $include_menu, $mode, $user_id, $user_type)
{
	global $_OPENDB_THEME;
	global $PHP_SELF;
	global $HTTP_VARS;
	
	echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">".
		"\n<html>".
		"\n<head>".
		"\n	<title>".get_opendb_title().(!empty($title)?" - $title":"")."</title>".
		"\n<link rel=\"icon\" href=\""._theme_image_src("icon.gif")."\" type=\"image/gif\" />".
		get_theme_css($pageid, $mode).
		get_opendb_rss_feeds_links().
		"\n</head>".
		"\n<body>");

	echo("<div id=\"header\">");
	
	echo("<h1><a href=\"index.php\">".get_opendb_title()."</a></h1>");
	
	echo("<ul class=\"headerLinks\">");
	
	$help_page = get_opendb_help_page($pageid);
	if($help_page!=NULL)
	{
		echo("<li class=\"help\"><a target=\"_new\" href=\"help.php?page=".$help_page."\" onclick=\"popup('help.php?page=$help_page', 800,600); return false;\" title=\"".get_opendb_lang_var('help')."\">".get_opendb_lang_var('help')."</a></li>");
	}
	
	$print_link_r = get_printable_link_r($pageid);
	if(is_array($print_link_r))
	{
		echo("<li><a target=\"_new\" href=\"".$print_link_r['url']."\" title=\"".$print_link_r['title']."\"><img src=\"".$print_link_r['image']."\" alt=\"".$print_link_r['title']."\"></a></li>");
	}
	
	if($include_menu)
	{
		echo("<li><form class=\"quickSearch\" action=\"listings.php\">".
			"<input type=\"hidden\" name=\"search_list\" value=\"y\">".
			//"<input type=\"hidden\" name=\"attribute_type\" value=\"UPC_ID\">".
			//"<input type=\"hidden\" name=\"attr_match\" value=\"partial\">".
			//"<input type=\"text\" name=\"attribute_val\" size=\"10\">".
			"<input type=\"hidden\" name=\"title_match\" value=\"partial\">".
			"<input type=\"text\" name=\"title\" size=\"10\">".
			"<input type=\"submit\" value=\"".get_opendb_lang_var('search')."\">".
			"</form></li>");

		echo("<li><a href=\"search.php\">".get_opendb_lang_var('advanced')."</a></li>");

		if(strlen($user_id)>0)
		{
			echo("<li class=\"login\"><a href=\"logout.php\">".get_opendb_lang_var('logout', 'user_id', $user_id)."</a></li>");
		}
		else
		{
			echo("<li class=\"login\"><a href=\"login.php?op=login\">".get_opendb_lang_var('login')."</a></li>");
		}

		if(is_exists_my_reserve_basket($user_id))
		{
			echo("<li><a href=\"borrow.php?op=my_reserve_basket\">"._theme_image("basket.png", NULL, get_opendb_lang_var('item_reserve_list'))."</a></li>");
		} 
	}
	echo("</ul>");
		
	echo("</div>");
	
	echo("<div id=\"content\" class=\"${pageid}Content\">");
	
	if($include_menu)
	{
		echo("<div id=\"menu\">");
		echo get_menu_options_list(get_menu_options($user_id, $user_type));
		echo("\n</div>");
	}
}

function theme_footer($user_id, $user_type)
{
	echo("</div>");
	echo("<div id=\"footer\"><a href=\"http://opendb.iamvegan.net/\">".get_opendb_lang_var('powered_by_site', 'site', get_opendb_title_and_version())."</a></div>");
	echo("</body></html>");
}
?>
