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
	global $ADMIN_TYPE;
	
	if($pageid == 'admin')
		$pageTitle = get_opendb_title_and_version(). " System Admin Tools";
	else if($pageid == 'install')
		$pageTitle = get_opendb_title_and_version(). " Installation";
	else
		$pageTitle = get_opendb_title();
			
	echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">".
		"\n<html>".
		"\n<head>".
		"\n<title>".$pageTitle.(!empty($title)?" - $title":"")."</title>".
		"\n<meta http-equiv=\"Content-Type\" content=\"".get_content_type_charset()."\">".
		"\n<link rel=\"icon\" href=\""._theme_image_src("icon.gif")."\" type=\"image/gif\" />".
		"\n<link rel=\"search\" type=\"application/opensearchdescription+xml\" title=\"".get_opendb_title()." Search\" href=\"./searchplugins.php\">".
		get_theme_css($pageid, $mode).
		get_opendb_rss_feeds_links().
		get_theme_javascript($pageid).
		"</head>".
		"\n<body>");

	echo("<div id=\"header\">");
	echo("<h1><a href=\"index.php\">".$pageTitle."</a></h1>");
	
	if($include_menu)
	{
		echo("<ul class=\"headerLinks\">");

		$help_page = get_opendb_help_page($pageid);
		if($help_page!=NULL)
		{
			echo("<li class=\"help\"><a href=\"help.php?page=".$help_page."\" target=\"_new\" title=\"".get_opendb_lang_var('help')."\">"._theme_image("help.png")."</a></li>");
		}
		
		$print_link_r = get_printable_link_r($pageid);
		if(is_array($print_link_r))
		{
			echo("<li><a href=\"".$print_link_r['url']."\" target=\"_new\" title=\"".$print_link_r['title']."\"><img src=\"".$print_link_r['image']."\" alt=\"".$print_link_r['title']."\"></a></li>");
		}
	
		if(is_exists_my_reserve_basket($user_id))
		{
			echo("<li><a href=\"borrow.php?op=my_reserve_basket\">"._theme_image("basket.png", get_opendb_lang_var('item_reserve_list'))."</a></li>");
		}
		
		echo("<li><form class=\"quickSearch\" action=\"listings.php\">".
			"<input type=\"hidden\" name=\"search_list\" value=\"y\">".
			//"<input type=\"hidden\" name=\"attribute_type\" value=\"UPC_ID\">".
			//"<input type=\"hidden\" name=\"attr_match\" value=\"partial\">".
			//"<input type=\"text\" name=\"attribute_val\" size=\"10\">".
			"<input type=\"hidden\" name=\"title_match\" value=\"partial\">".
			"<input type=\"text\" class=\"text\" name=\"title\" size=\"10\">".
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
		

		echo("</ul>");
	}
			
	echo("</div>");
	
	echo("<div id=\"content\" class=\"${pageid}Content\">");

	if($include_menu)
	{
		echo("<div id=\"menu\">");
		echo get_menu_options_list(get_menu_options($user_id, $user_type));
		echo("\n</div>");
	}
}

function theme_footer($pageid, $user_id, $user_type)
{
	echo("</div>");
	
	if($pageid != 'install')
		echo("<div id=\"footer\"><a href=\"http://opendb.iamvegan.net/\">".get_opendb_lang_var('powered_by_site', 'site', get_opendb_title_and_version())."</a></div>");
		
	echo("</body></html>");
}

$_OPENDB_THEME_CSS_MAP = array(
	'borrow'=>array('listings', 'item_display'),
	'item_borrow'=>array('listings', 'item_display'),
	'quick_checkout'=>array('listings', 'item_display'),
	'import'=>array('listings', 'item_display', 'item_input'),
	'item_display'=>array('listings'),
	'item_input'=>array('listings'),
	'user_listing'=>array('listings'),
	'admin'=>array('listings', 'item_input'),
	'export'=>array('item_input'),
	'search'=>array('item_review', 'item_input'),
	'item_review'=>array('item_input')
);

function theme_css_map($pageid)
{
	global $_OPENDB_THEME_CSS_MAP;
	return $_OPENDB_THEME_CSS_MAP[$pageid];
}
?>