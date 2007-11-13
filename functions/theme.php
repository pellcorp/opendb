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

include_once("./functions/user.php");
include_once("./functions/utils.php");
include_once("./functions/fileutils.php");
include_once("./functions/language.php");
include_once("./functions/cssparser/cssparser.php");
include_once("./functions/rss.php");
include_once("./functions/scripts.php");

function get_content_type_charset() {
	$contentType = "text/html";
	
	$charSet = get_opendb_config_var('themes', 'charset');
	if(strlen($charSet)>0) {
		$contentType .= ";charset=".$charSet;
	}
	
	return $contentType;
}

function _theme_header($title=NULL, $inc_menu=TRUE)
{
	global $PHP_SELF;
	global $HTTP_VARS;
	
	if(function_exists('theme_header'))
	{
	    header("Cache-control: no-store");
		header("Pragma: no-store");
		header("Expires: 0");

		$user_id = get_opendb_session_var('user_id');
		$user_type = get_opendb_session_var('user_type');
		
		if(is_site_public_access_enabled())
		{
			$user_id = NULL;
			$user_type = NULL;
		}
		
		$include_menu = ($inc_menu!==FALSE && $inc_menu!=='N'?TRUE:FALSE);
		if(!$include_menu && strlen($HTTP_VARS['mode'])==0)
		{
			$HTTP_VARS['mode'] = 'no-menu';	
		}
		
		$pageId = basename($PHP_SELF, '.php');
		
		$theme_header =
			 theme_header(
				$pageId,
				$title,
    			$include_menu,
    			$HTTP_VARS['mode'],
				$user_id,
				$user_type);

		return $theme_header;
	}
	else
	{
		return NULL;
	}
}

function _theme_footer()
{
	global $PHP_SELF;
	
	$user_id = get_opendb_session_var('user_id');
	$user_type = get_opendb_session_var('user_type');
	
	if(is_site_public_access_enabled())
	{
		$user_id = NULL;
		$user_type = NULL;
	}

	$pageId = basename($PHP_SELF, '.php');
	
	if(function_exists('theme_footer'))
	{
		return theme_footer(
			$pageId,
			$user_id,
			$user_type);
	}
	else
	{
		return NULL;
	}
}

function get_theme_javascript($pageid)
{
	$scripts[] = 'common.js';
	$scripts[] = 'date.js';
	$scripts[] = 'forms.js';
	$scripts[] = 'listings.js';
	$scripts[] = 'marquee.js';
	$scripts[] = 'popup.js';
	$scripts[] = 'search.js';
	$scripts[] = 'tabs.js';
	$scripts[] = 'validation.js';	

	if($pageid == 'admin')
	{
		$scripts[] = './scripts/overlibmws/overlibmws.js';
		$scripts[] = './scripts/overlibmws/overlibmws_function.js';
		$scripts[] = './scripts/overlibmws_iframe.js';
		$scripts[] = './scripts/overlibmws_hide.js';
		$scripts[] = './admin/tooltips.js';
	}
	
	$buffer = '';
	
	while(list(,$script) = each($scripts))
	{
		$buffer .= get_javascript($script);
	}
	
	return $buffer;
}

function get_theme_css($pageid, $mode = NULL)
{
	global $_OpendbBrowserSniffer;
	
	$buffer = "\n";
	
	$file_list = _theme_css_file_list($pageid);
	if(count($file_list)>0)
	{
		while(list(, $css_file_r) = each($file_list))
		{
			if(strlen($css_file_r['browser'])==0 || $_OpendbBrowserSniffer->isBrowser($css_file_r['browser']))
			{
				$buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css_file_r['file']."\">\n";
			}
		}
	}
	
	if($mode == 'printable' || $mode == 'no-menu')
	{
		$file_list = _theme_css_file_list($pageid, $mode);
		if(count($file_list)>0)
		{
			while(list(, $css_file_r) = each($file_list))
			{
				$buffer .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css_file_r['file']."\">\n";
			}
		}
	}
	
	return $buffer;
}

function _theme_css_map($pageid)
{
	global $_OPENDB_THEME_CSS_MAP;
	
	if(function_exists('theme_css_map'))
		return theme_css_map($pageid);
	else
		return NULL;
}

/**
 * Returns an array of css files to render for the current page.
 * 
 * 	array(file=>"./theme/$_OPENDB_THEME/$name.css")
 */
function _theme_css_file_list($pageid, $mode = NULL)
{
	global $_OPENDB_THEME;
	
	$css_file_list = array();
	
	add_css_files('style', $mode, $css_file_list);

	$css_map = _theme_css_map($pageid);
	if(is_not_empty_array($css_map))
	{
		reset($css_map);
		while(list(,$page) = each($css_map))
		{
			add_css_files($page, $mode, $css_file_list);
		}
	}
	
	add_css_files($pageid, $mode, $css_file_list);
	
	return $css_file_list;
}

function add_css_files($pageid, $mode, &$css_file_list)
{
	global $_OPENDB_THEME;
	global $_OpendbBrowserSniffer;
	
	if(strlen($mode)==0)
	{
		if(file_exists("./theme/$_OPENDB_THEME/${pageid}.css"))
		{
			$css_file_list[] = array(file=>"./theme/$_OPENDB_THEME/${pageid}.css");
		}
		
		$browsers_r = $_OpendbBrowserSniffer->getSupportedBrowsers();
		while(list(,$browser) = each($browsers_r))
		{
			$suffix = str_replace(".", NULL, $browser);
			
			if(file_exists("./theme/$_OPENDB_THEME/${pageid}_${suffix}.css"))
			{
				$css_file_list[] = array(file=>"./theme/$_OPENDB_THEME/${pageid}_${suffix}.css", browser=>$browser);
			}
		}
	}
	else if($mode == 'printable')
	{
		if(file_exists("./theme/$_OPENDB_THEME/${pageid}_print.css"))
		{
			$css_file_list[] = array(file=>"./theme/$_OPENDB_THEME/${pageid}_print.css");
		}
	}
	else if($mode == 'no-menu')
	{
		if(file_exists("./theme/$_OPENDB_THEME/${pageid}_nomenu.css"))
		{
			$css_file_list[] = array(file=>"./theme/$_OPENDB_THEME/${pageid}_nomenu.css");
		}
	}
}

function get_theme_search_dir_list()
{
	global $_OPENDB_THEME;
	global $_OPENDB_LANGUAGE;
	
	$dirPath = array();
			
	if(isset($_OPENDB_THEME) && isset($_OPENDB_LANGUAGE))
	{
		$dirPath[] = "theme/$_OPENDB_THEME/images/$_OPENDB_LANGUAGE/";
	}
	
	if(isset($_OPENDB_THEME))
	{
		$dirPath[] = "theme/$_OPENDB_THEME/images/";
		$dirPath[] = "theme/$_OPENDB_THEME/";
	}			
	
	if(isset($_OPENDB_LANGUAGE))
	{
		$dirPath[] = "theme/default/images/$_OPENDB_LANGUAGE/";
	}
	
	$dirPath[] = "theme/default/images/";
	$dirPath[] = "theme/default/";
	$dirPath[] = "images/";

	return $dirPath;
}

function get_theme_search_site_dir_list()
{
	global $_OPENDB_THEME;
	
	$dirPath = array();
	
	if(isset($_OPENDB_THEME))
	{
		$dirPath[] = "theme/$_OPENDB_THEME/images/site/";
	}
	
	$dirPath[] = "site/images/";
	
	return $dirPath;
}

function _theme_image_src($src)
{
	global $_OPENDB_THEME;
	global $_OPENDB_LANGUAGE;

	if(strlen($src)>0)
	{
		if(function_exists('theme_image_src'))
		{
			$theme_image_src = theme_image_src($src);
			if(strlen($theme_image_src)>0 && file_exists($theme_image_src))
				return $theme_image_src;
		}
		
		if(starts_with($src, 'site/images/'))
			$dirPaths = get_theme_search_site_dir_list();
		else
			$dirPaths = get_theme_search_dir_list();
		
		$file_r = parse_file(basename($src));
		$src = $file_r['name'];
		
		$extension_r = array('gif', 'png', 'jpg');
		while(list(,$dir) = each($dirPaths))
		{
			reset($extension_r);
			while(list(,$extension) = each($extension_r))
			{
				$file = './'.$dir.$src.'.'.$extension;
				if(file_exists($file))
				{
					return $file;
				}
			}			
		}
	}

	return FALSE; // no image found.
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

	1)  Checks if 'theme_image' function is defined.  This function
		should return a fully formed <img src="" ...> or a textual 
		equivalent.

		If the theme specific 'theme_image' returns FALSE, this indicates
		that the local function is not taking responsibility for displaying
		the image in this case.  We should continue as though the theme
		specific 'theme_image' function did not exist.
			
	2)	Calls _theme_image_src 

	4)	If _theme_image_src returns FALSE, then return the $src, without extension, in initcap format.
*/
function _theme_image($src, $title=NULL, $type=NULL)
{
	$file_r = parse_file(basename($src));
	$alt = ucfirst($file_r['name']);
		
	if(function_exists('theme_image') && ($value = theme_image($src, $title, $type))!==FALSE)
		return $value;
	else if ( ($src = _theme_image_src($src)) !== FALSE)
	{
		return "<img src=\"$src\""
			.(strlen($alt)>0?" alt=\"".$alt."\"":"")
			.(strlen($title)>0?" title=\"$title\"":"")
			.(strlen($type)>0?" class=\"$type\"":"")
			.">";
	}
	else if($type == "action") // Special type, that if not handled, will be handled back at caller instead!
	{
		return FALSE;
	}
	else
	{
		return $alt;
	}	
}

/**
 * assumes a stats.css exists for every theme that wants to render stats graphs.
 *
 * @return unknown
 */
function _theme_graph_config()
{
	global $_OPENDB_THEME;

	$cssParser =& new cssparser(FALSE);
	if($cssParser->Parse("./theme/$_OPENDB_THEME/stats.css"))
	{
		$stats_graph_config_r = $cssParser->GetSection('.OpendbStatsGraphs');
		return $stats_graph_config_r;
	}
	
	return FALSE;
}

function is_exists_theme($theme)
{
	if(strlen($theme)<=20 && file_exists("./theme/$theme/theme.php"))
		return TRUE;
	else
		return FALSE;
}

/**
	Generate a list of user themes.
*/
function get_user_theme_r()
{
	$handle=opendir('./theme');
	while ($file = readdir($handle))
    {
		if(!ereg("^[.]",$file) && is_dir("./theme/$file"))
		{
			if(is_exists_theme($file))
			{
				$themes[] = $file;
			}
		}
	}
	closedir($handle);

	if(is_not_empty_array($themes))
		return $themes;
	else // empty array as last resort.
		return array();
}
?>