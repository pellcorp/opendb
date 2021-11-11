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
include_once("./lib/user.php");
include_once("./lib/utils.php");
include_once("./lib/fileutils.php");
include_once("./lib/language.php");
include_once("./lib/cssparser/cssparser.php");
include_once("./lib/rss.php");
include_once("./lib/javascript.php");

function get_opendb_site_theme() {
	global $_OPENDB_THEME;
	
	return $_OPENDB_THEME;
}

function get_content_type_charset() {
	$contentType = "text/html";
	
	$charSet = get_opendb_config_var ( 'themes', 'charset' );
	if (strlen ( $charSet ) > 0) {
		$contentType .= ";charset=" . $charSet;
	}
	
	return $contentType;
}


function _theme_header($title = NULL, $inc_menu = TRUE) {
	global $PHP_SELF;
	global $HTTP_VARS;
	
	if (function_exists ( 'theme_header' )) {
		header ( "Cache-control: no-store" );
		header ( "Pragma: no-store" );
		header ( "Expires: 0" );
		
		if (is_site_public_access ()) {
			$user_id = NULL;
		} else {
			$user_id = get_opendb_session_var ( 'user_id' );
		}
		
		$include_menu = ($inc_menu !== FALSE && $inc_menu !== 'N' ? TRUE : FALSE);
		if (! $include_menu && strlen($HTTP_VARS['mode'] ) == 0) {
			$HTTP_VARS['mode'] = 'no-menu';
		}
		
		$pageId = basename ( $PHP_SELF, '.php' );
		
		return theme_header( $pageId, $title, $include_menu, $HTTP_VARS['mode'], $user_id );
	} else {
		return NULL;
	}
}

function _theme_footer() {
	global $PHP_SELF;
	
	$user_id = get_opendb_session_var ( 'user_id' );
	
	if (is_site_public_access ()) {
		$user_id = NULL;
	}
	
	$pageId = basename ( $PHP_SELF, '.php' );
	
	if (function_exists ( 'theme_footer' )) {
		return theme_footer ( $pageId, $user_id );
	} else {
		return NULL;
	}
}

function get_theme_javascript($pageid) {
    $scripts [] = 'jquery.js';
	$scripts [] = 'common.js';
	$scripts [] = 'date.js';
	$scripts [] = 'forms.js';
	$scripts [] = 'listings.js';
	$scripts [] = 'marquee.js';
	$scripts [] = 'search.js';
	$scripts [] = 'tabs.js';
	$scripts [] = 'validation.js';
	
	if ($pageid == 'admin') {
		$scripts [] = 'overlibmws/overlibmws.js';
		$scripts [] = 'overlibmws/overlibmws_function.js';
		$scripts [] = 'overlibmws/overlibmws_iframe.js';
		$scripts [] = 'overlibmws/overlibmws_hide.js';
		$scripts [] = 'admin/tooltips.js';
	}
	
	$buffer = '';

	foreach ($scripts as $script) {
		$buffer .= get_javascript ( $script );
	}
	
	return $buffer;
}

function get_theme_css($pageid, $mode = NULL) {
	global $_OpendbBrowserSniffer;
	
	$buffer = "\n";
	
	$file_list = _theme_css_file_list( $pageid );
	if (count ( $file_list ) > 0) {
		foreach ($file_list as $css_file_r) {
			if (!isset($css_file_r['browser'] ) || $_OpendbBrowserSniffer->isBrowser ( $css_file_r['browser'] )) {
				$buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $css_file_r['file'] . "\">\n";
			}
		}
	}

	if ($mode == 'printable' || $mode == 'no-menu') {
		$file_list = _theme_css_file_list ( $pageid, $mode );
		if (count ( $file_list ) > 0) {
			foreach ($file_list as $css_file_r) {
				$buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $css_file_r ['file'] . "\">\n";
			}
		}
	}

	return $buffer;
}

function _theme_css_map($pageid) {
	if (function_exists ( 'theme_css_map' ))
		return theme_css_map ( $pageid );
	else
		return NULL;
}

function _theme_css_file_list($pageid, $mode = NULL) {
	$css_file_list = array ();
	
	add_css_files ( 'style', $mode, $css_file_list );
	
	$css_map = _theme_css_map ( $pageid );
	if (is_not_empty_array ( $css_map )) {
		reset ( $css_map );
		foreach ($css_map as $page) {
			add_css_files ( $page, $mode, $css_file_list );
		}
	}
	
	add_css_files ( $pageid, $mode, $css_file_list );
	
	return $css_file_list;
}

function add_css_files($pageid, $mode, &$css_file_list) {
	global $_OpendbBrowserSniffer;
	
	$theme = get_opendb_site_theme ();
	
	if (strlen ( $mode ) == 0) {
		if (strlen ( $theme ) > 0 && file_exists ( "./theme/$theme/${pageid}.css" )) {
			$css_file_list [] = array (
				'file' => "./theme/$theme/${pageid}.css" );
		}
		
		$browsers_r = $_OpendbBrowserSniffer->getSupportedBrowsers ();
		foreach ($browsers_r as $browser) {
			$suffix = str_replace ( ".", NULL, $browser );
			
			if (strlen ( $theme ) > 0 && file_exists ( "./theme/$theme/${pageid}_${suffix}.css" )) {
				$css_file_list [] = array (
						'file' => "./theme/$theme/${pageid}_${suffix}.css",
						'browser' => $browser );
			}
		}
	} else if ($mode == 'printable') {
		if (strlen ( $theme ) > 0 && file_exists ( "./theme/$theme/${pageid}_print.css" )) {
			$css_file_list [] = array (
					'file' => "./theme/$theme/${pageid}_print.css" );
		}
	} else if ($mode == 'no-menu') {
		if (strlen ( $theme ) > 0 && file_exists ( "./theme/$theme/${pageid}_nomenu.css" )) {
			$css_file_list [] = array (
					'file' => "./theme/$theme/${pageid}_nomenu.css" );
		}
	}
}

function get_theme_img_search_dir_list() {
	$theme = get_opendb_site_theme ();
	$language = strtolower ( get_opendb_site_language () );
	
	$dirPath = array ();
	
	if (strlen ( $theme ) > 0 && strlen ( $language ) > 0) {
		$dirPath [] = "images/$theme/$language";
	}
	
	if (strlen ( $theme ) > 0) {
		$dirPath [] = "images/$theme";
	}
	
	if (strlen ( $language ) > 0) {
		$dirPath [] = "images/default/$language";
	}
	
	$dirPath [] = "images/default/images";
	$dirPath [] = "images";
	
	return $dirPath;
}

function get_theme_img_search_site_dir_list() {
	$theme = get_opendb_site_theme ();
	
	$dirPath = array ();
	
	if (strlen ( $theme ) > 0) {
		$dirPath [] = "images/$theme/site";
	}
	
	$dirPath [] = "images/site";
	
	return $dirPath;
}

function theme_image_src($src) {
	if (strlen ( $src ) > 0) {
		if (starts_with ( $src, 'images/site/' ))
			$dirPaths = get_theme_img_search_site_dir_list ();
		else
			$dirPaths = get_theme_img_search_dir_list ();
		
		$src = safe_filename ( $src );
		$file_r = parse_file ( $src );
		$src = $file_r ['name'];

		// temporary until we fix up the theme image calls to use the actual images that exist.
		$extension_r = array($file_r['extension'], 'png', 'jpg', 'gif');

		foreach($dirPaths as $dir) {
			reset ( $extension_r );
			foreach ($extension_r as $extension) {
				$file = './' . $dir . '/' . $src . '.' . $extension;
				if (file_exists ( $file )) {
					return $file;
				}
			}
		}
	}
	
	return FALSE; // no image found.
}

/**
 * Guarantees that any image sources referenced are relative to opendb and currently
 * to make this validation simpler, only images which have at most one directory
 * level deep are supported, all others have their directory information removed.
 *
 * @param unknown_type $src
 * @return unknown
 */
function safe_filename($src) {
	// ensure dealing with only one path separator!
	$src = str_replace ( "\\", "/", $src );
	
	$file = basename ( $src );
	
	$dir = dirname ( $src );
	if ($dir == '/' || $dir == '.')
		$dir = NULL;
	
	if (strpos ( $dir, "/" ) !== FALSE)
		return $file; // return basename as illegal pathname - more than one directory path - only one level supported currently!
	else if (strlen ( $dir ) > 0)
		return $dir . '/' . $file;
	else
		return $file;
}

/**
	Will format a complete image url.

	@param $src		The image.ext without any path information.
	@param $title	The tooltip to include in the image.
	@param $type	Specifies the origin of the image.  Current types being
					used are:
						s_item_type - for 's_item_type' images.
						borrowed_item - Borrow system status images.
						action - Item operation (edit, delete, etc)
								
	These are the steps it uses to work out which image to display:

	1)	Calls theme_image_src 

	2)	If theme_image_src returns FALSE, then return the $src, without extension, in initcap format.
*/
function theme_image($src, $title = NULL, $type = NULL) {
	$file_r = parse_file ( basename ( $src ) );
	$alt = ucfirst ( $file_r ['name'] );
	
	if (($src = theme_image_src ( $src )) !== FALSE) {
		return "<img src=\"$src\"" . (strlen ( $alt ) > 0 ? " alt=\"" . $alt . "\"" : "") . (strlen ( $title ) > 0 ? " title=\"$title\"" : "") . (strlen ( $type ) > 0 ? " class=\"$type\"" : "") . ">";
	} else if ($type == "action") 	// Special type, that if not handled, will be handled back at caller instead!
{
		return FALSE;
	} else {
		return $alt;
	}
}

/**
 * assumes a stats.css exists for every theme that wants to render stats graphs.
 *
 * @return unknown
 */
function _theme_graph_config() {
	$theme = get_opendb_site_theme ();
	
	$cssParser = new cssparser ( FALSE );
	if (strlen ( $theme ) > 0 && $cssParser->Parse ( "./theme/$theme/stats.css" )) {
		$stats_graph_config_r = $cssParser->GetSection ( '.OpendbStatsGraphs' );
		return $stats_graph_config_r;
	}
	
	return FALSE;
}

function is_exists_theme($theme) {
	if (strlen( $theme ) <= 20 && file_exists( "./theme/$theme/theme.php" ))
		return TRUE;
	else
		return FALSE;
}

/**
	Generate a list of user themes.
*/
function get_user_theme_r() {
	$handle = opendir ( './theme' );
	while ( $file = readdir ( $handle ) ) {
		if (! preg_match ( "/^[.]/", $file ) && is_dir ( "./theme/$file" )) {
			if (is_exists_theme ( $file )) {
				$themes [] = $file;
			}
		}
	}
	closedir ( $handle );
	
	if (is_not_empty_array ( $themes ))
		return $themes;
	else // empty array as last resort.
		return array ();
}

function opendb_operation_not_available() {
	echo _theme_header ( get_opendb_lang_var ( 'operation_not_available' ) );
	echo ("<p class=\"error\">" . get_opendb_lang_var('operation_not_available') . "</p>");
	echo _theme_footer ();
}

function opendb_site_disabled($inc_menu = TRUE) {
	echo _theme_header (get_opendb_lang_var ('site_is_disabled') , $inc_menu);
	echo ("<p class=\"error\">" . get_opendb_lang_var('site_is_disabled') . "</p>");
	echo _theme_footer ();
}

function opendb_not_authorised_page($permission = NULL, $HTTP_VARS = NULL) {
	global $PHP_SELF;
	
	if ($permission != NULL && is_permission_disabled_for_remember_me($permission)) {
		redirect_login($PHP_SELF, $HTTP_VARS, TRUE);
	} else {
		echo _theme_header ( get_opendb_lang_var ( 'not_authorized_to_page' ) );
		echo ("<p class=\"error\">" . get_opendb_lang_var('not_authorized_to_page') . "</p>");
		echo _theme_footer ();
	}
}
?>
