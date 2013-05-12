<?php
/*
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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
function theme_header($pageid, $title, $include_menu, $mode, $user_id) {
	global $PHP_SELF;
	global $HTTP_VARS;
	global $ADMIN_TYPE;

	if ($pageid == 'install') {
		$pageTitle = get_opendb_title_and_version() . " Installation";
	} else {
		$pageTitle = get_opendb_title();
	}

	echo ("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">" . "\n<html>" . "\n<head>" . "\n<title>" . $pageTitle . (!empty($title) ? " - $title" : "") . "</title>" . "\n<meta http-equiv=\"Content-Type\" content=\"" . get_content_type_charset() . "\">"
			. "\n<link rel=\"icon\" href=\"" . theme_image_src("icon-16x16.gif") . "\" type=\"image/gif\" />" . "\n<link rel=\"search\" type=\"application/opensearchdescription+xml\" title=\"" . get_opendb_title() . " Title Search\" href=\"./searchplugins.php?type=title\">"
			. "\n<link rel=\"search\" type=\"application/opensearchdescription+xml\" title=\"" . get_opendb_title() . " UPC Search\" href=\"./searchplugins.php?type=upc\">" 
				. get_theme_css($pageid, $mode) 
				. get_opendb_rss_feeds_links() 
				. get_theme_javascript($pageid) 
			. "</head>" 
			. "\n<body>");

	echo ("<div id=\"header\">");
	echo ("<h1><a href=\"index.php\">" . $pageTitle . "</a></h1>");

	if ($include_menu) {
		echo ("<ul class=\"headerLinks\">");

		$help_page = get_opendb_help_page($pageid);
		if ($help_page != NULL) {
			echo ("<li class=\"help\"><a href=\"help.php?page=" . $help_page . "\" target=\"_new\" title=\"" . get_opendb_lang_var('help') . "\">" . theme_image("help.png") . "</a></li>");
		}

		$printable_page_url = get_printable_page_url($pageid);
		if ($printable_page_url != NULL) {
			echo ("<li><a href=\"" . $printable_page_url . "\" target=\"_new\" title=\"" . get_opendb_lang_var('printable_version') . "\">" . theme_image("printable.gif") . "</a></li>");
		}

		if (is_exists_my_reserve_basket($user_id)) {
			echo ("<li><a href=\"borrow.php?op=my_reserve_basket\">" . theme_image("basket.png", get_opendb_lang_var('item_reserve_list')) . "</a></li>");
		}

		if (is_user_granted_permission(PERM_VIEW_LISTINGS)) {
			echo ("<li><form class=\"quickSearch\" action=\"listings.php\">" . "<input type=\"hidden\" name=\"search_list\" value=\"y\">" . 
					//"<input type=\"hidden\" name=\"attribute_type\" value=\"UPC_ID\">".
					//"<input type=\"hidden\" name=\"attr_match\" value=\"partial\">".
					//"<input type=\"text\" class=\"text\" name=\"attribute_val\" size=\"10\" value=\"UPC Search\" onfocus=\"if(this.value=='UPC Search'){this.value='';this.style.color='black';}\" onblur=\"if(this.value==''){this.value='UPC Search';this.style.color='gray';}\">".
					"<input type=\"hidden\" name=\"title_match\" value=\"partial\">"
					. "<input type=\"text\" class=\"text\" name=\"title\" size=\"10\" value=\"Title Search\" onfocus=\"if(this.value=='Title Search'){this.value='';this.style.color='black';}\" onblur=\"if(this.value==''){this.value='Title Search';this.style.color='gray';}\">" . "</form></li>");
		}

		if (is_user_granted_permission(PERM_VIEW_ADVANCED_SEARCH)) {
			echo ("<li><a href=\"search.php\" title=\"" . get_opendb_lang_var('advanced_search') . "\">" . get_opendb_lang_var('advanced') . "</a></li>");
		}

		if (strlen($user_id) > 0) {
			echo ("<li class=\"login\"><a href=\"logout.php\">" . get_opendb_lang_var('logout', 'user_id', $user_id) . "</a></li>");
		} else {
			echo ("<li class=\"login\"><a href=\"login.php?op=login\">" . get_opendb_lang_var('login') . "</a></li>");
		}

		echo ("</ul>");
	}

	echo ("</div>");

	echo ("<div id=\"content\" class=\"${pageid}Content\">");

	if ($include_menu) {
		if ($pageid == 'admin') {
			echo ("\n<div id=\"admin-menu\" class=\"menuContainer toggleContainer\" onclick=\"return toggleVisible('admin-menu');\">
                <span id=\"admin-menu-toggle\" class=\"menuToggle toggleHidden\">" . get_opendb_lang_var('admin_tools') . "</span>
                <div id=\"admin-menu-content\" class=\"menuContent elementHidden\">
                <h2 class=\"menu\">Admin Tools</h2>");

			$menu_options_rs = get_system_admin_tools_menu();
			echo get_menu_options_list($menu_options_rs);
			echo ("\n</div>");
			echo ("\n</div>");
		}

		echo ("\n<div id=\"menu\" class=\"menuContainer toggleContainer\" onclick=\"return toggleVisible('menu');\">");
		echo ("<span id=\"menu-toggle\" class=\"menuToggle toggleHidden\">" . get_opendb_lang_var('main_menu') . "</span>");
		echo ("<div id=\"menu-content\" class=\"menuContent elementHidden\">");
		echo ("<h2 class=\"menu\">" . get_opendb_lang_var('main_menu') . '</h2>');
		echo get_menu_options_list(get_menu_options($user_id));
		echo ("\n</div>");
		echo ("\n</div>");
	}
}

function theme_footer($pageid, $user_id) {
	echo ("</div>");

	if ($pageid != 'install')
		echo ("<div id=\"footer\"><a href=\"http://opendb.iamvegan.net/\">" . get_opendb_lang_var('powered_by_site', 'site', get_opendb_title_and_version()) . "</a></div>");

	echo ("</body></html>");
}

function theme_css_map($pageid) {
	$themeCssMap = array(
			'borrow' => array('listings', 'item_display'), 
			'item_borrow' => array('listings', 'item_display'), 
			'quick_checkout' => array('listings', 'item_display'), 
			'import' => array('listings', 'item_display', 'item_input'), 
			'item_display' => array('listings'),
			'item_input' => array('listings'), 
			'user_listing' => array('listings'), 
			'admin' => array('listings', 'item_input'), 
			'export' => array('item_input'), 
			'search' => array('item_review', 'item_input'), 
			'item_review' => array('item_input'));

	if (isset($themeCssMap[$pageid])) {
		return $themeCssMap[$pageid];
	} else {
		return NULL;
	}
}
?>