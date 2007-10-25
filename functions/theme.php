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

/**
	Do not return anything if not defined.
*/
function _theme_header($title=NULL, $inc_menu=TRUE)
{
	global $PHP_SELF;
	global $HTTP_VARS;
	
	if(function_exists('theme_header'))
	{
		// todo - revisit this!
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
		
		$theme_header =
			 theme_header(
				basename($PHP_SELF, '.php'),
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

/**
	Do not return anything if not defined.
*/
function _theme_menu()
{
	if(function_exists('theme_menu'))
		return theme_menu(get_opendb_session_var('user_id'), get_opendb_session_var('user_type'));
	else
		return NULL;
}

function get_theme_javascript($pageid)
{
	return get_javascript('common.js').
			get_javascript('date.js').
			get_javascript('forms.js').
			get_javascript('listings.js').
			get_javascript('marquee.js').
			get_javascript('popup.js').
			get_javascript('search.js').
			get_javascript('tabs.js').
			get_javascript('validation.js');					
}

/**
 * will render the css links for a page
 *
 * @param $include_menu where include_menu = N, will need to activate special page to
 * remove menu.
 */
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

/**
 * Any additional CSS that should be included in addition to a css of the same name
 * 
 * Note that this map is not recursive, so if you have say borrow that needs
 * listings, and listings needs item display, borrow will not automatically get
 * item display included.
 */
$_OPENDB_THEME_CSS_MAP = array(
	'borrow'=>array('listings', 'item_display'),
	'item_borrow'=>array('listings', 'item_display'),
	'quick_checkout'=>array('listings', 'item_display'),
	'import'=>array('listings', 'item_display', 'item_input'),
	'item_display'=>array('listings'),
	'user_listing'=>array('listings'),
	'admin'=>array('listings'),
	'search'=>array('item_review', 'item_input'),
	'item_review'=>array('item_input')
);

function _theme_css_map($pageid)
{
	global $_OPENDB_THEME_CSS_MAP;
	
	if(function_exists('theme_css_map'))
		return theme_css_map($pageid);
	else
		return $_OPENDB_THEME_CSS_MAP[$pageid];
}

/**
 * Returns an array of css files to render for the current page.
 * 
 * 	array(file=>"./theme/$_OPENDB_THEME/$name.css", media=>'print')
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

/**
	Do not return anything if not defined.
*/
function _theme_footer()
{
	if(function_exists('theme_footer'))
		return theme_footer(get_opendb_session_var('user_id'), get_opendb_session_var('user_type'));
	else
		return NULL;
}

/**
	@param $src		The image.ext without any path information.

	Checks for $src in the following directories:
		theme/$_OPENDB_THEME/images/$_OPENDB_LANGUAGE/
		theme/$_OPENDB_THEME/images/
		theme/$_OPENDB_THEME/

		theme/$_OPENDB_DEFAULT_THEME/images/$_OPENDB_LANGUAGE/
		theme/$_OPENDB_DEFAULT_THEME/images/
		theme/$_OPENDB_DEFAULT_THEME/

		images/$_OPENDB_LANGUAGE/
		images/

	Otherwise return FALSE indicating image was not found.
*/
function _theme_image_src($src)
{
	global $_OPENDB_THEME;
	global $_OPENDB_DEFAULT_THEME;
	global $_OPENDB_LANGUAGE;

	if(strlen($src)>0)
	{
		// Allow a theme to point at a specific theme image, perhaps in another theme location.
		if(function_exists('theme_image_src'))
		{
			$theme_image_src = theme_image_src($src);
			if(strlen($theme_image_src)>0 && file_exists($theme_image_src))
				return $theme_image_src;
		}
		
		if(starts_with($src, 'theme/') || starts_with($src, 'images/') && file_exists($src)) // in case we have already expanded with _theme_image_src previously.
		{
			return $src;
		}
		else if(starts_with($src, 'site/images/'))
		{
			return theme_site_image_src($src);
		}
		else
		{
			if(isset($_OPENDB_THEME) && isset($_OPENDB_LANGUAGE) && file_exists("./theme/$_OPENDB_THEME/images/$_OPENDB_LANGUAGE/$src"))
				return "theme/$_OPENDB_THEME/images/$_OPENDB_LANGUAGE/$src";
			else if(isset($_OPENDB_THEME) && file_exists("./theme/$_OPENDB_THEME/images/$src"))
				return "theme/$_OPENDB_THEME/images/$src";
			else if(isset($_OPENDB_THEME) && file_exists("./theme/$_OPENDB_THEME/$src"))
				return "theme/$_OPENDB_THEME/$src";
			else if(isset($_OPENDB_DEFAULT_THEME)&& isset($_OPENDB_LANGUAGE) && file_exists("theme/$_OPENDB_DEFAULT_THEME/images/$_OPENDB_LANGUAGE/$src"))
				return "theme/$_OPENDB_DEFAULT_THEME/images/$_OPENDB_LANGUAGE/$src";
			else if(isset($_OPENDB_DEFAULT_THEME) && file_exists("theme/$_OPENDB_DEFAULT_THEME/images/$src"))
				return "theme/$_OPENDB_DEFAULT_THEME/images/$src";
			else if(isset($_OPENDB_DEFAULT_THEME) && file_exists("theme/$_OPENDB_DEFAULT_THEME/$src"))
				return "theme/$_OPENDB_DEFAULT_THEME/$src";
			else if(isset($_OPENDB_LANGUAGE) && file_exists("./theme/default/images/$_OPENDB_LANGUAGE/$src"))
				return "theme/default/images/$_OPENDB_LANGUAGE/$src";
			else if(file_exists("./theme/default/images/$src"))
				return "theme/default/images/$src";
			else if(file_exists("./theme/default/$src"))
				return "theme/default/$src";
			else if(isset($_OPENDB_LANGUAGE) && file_exists("./images/$_OPENDB_LANGUAGE/$src"))
				return "images/$_OPENDB_LANGUAGE/$src";
			else if(file_exists("./images/$src"))
				return "images/$src";
		}
	}

	//else
	return FALSE; // no image found.
}

function theme_site_image_src($src)
{
	global $_OPENDB_THEME;
	global $_OPENDB_DEFAULT_THEME;
	
	$src = basename($src);
	
	if(isset($_OPENDB_THEME) && file_exists("./theme/$_OPENDB_THEME/images/site/$src"))
		return "./theme/$_OPENDB_THEME/images/site/$src";
	else if(isset($_OPENDB_DEFAULT_THEME) && file_exists("./theme/$_OPENDB_DEFAULT_THEME/images/site/$src"))
		return "./theme/$_OPENDB_DEFAULT_THEME/images/site/$src";
	else
		return "./site/images/$src";
}

/**
	Will format a complete image url.

	@param $src		The image.ext without any path information.
	@param $title	The tooltip to include in the image.
	@param $type	Specifies the origin of the image.  Current types being
					used are:
						s_item_type - for 's_item_type' table images.
						borrowed_item - Borrow system status images.
						action - Item operation (input,borrow actions)
								
	These are the steps it uses to work out which image to display:

	1)  Checks if 'theme_image' function is defined.  This function
		should return a fully formed <img src="" ...> or a textual 
		equivalent.

		If the theme specific 'theme_image' returns FALSE, this indicates
		that the local function is not taking responsibility for displaying
		the image in this case.  We should continue as though the theme
		specific 'theme_image' function did not exist.
			
	2)	Checks for $src in the following directories:
			theme/$_OPENDB_THEME/images/$_OPENDB_LANGUAGE/
			theme/$_OPENDB_THEME/images/
			theme/$_OPENDB_THEME/
			images/$_OPENDB_LANGUAGE/
			images/

	4)	Otherwise return the $src, without extension, in initcap format.
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

/**
	All we need to do is check if there is a matching theme/$theme/index.php
	script.
*/
function is_legal_theme($theme)
{
	if(strlen($theme) && file_exists("./theme/$theme/theme.php"))
		return true;
	else
		return false;
}

/**
	All we need to do is check if there is a matching theme/$theme/index.php
	script.
*/
function is_legal_user_theme($theme)
{
	if(strlen($theme)<=20 && file_exists("./theme/$theme/theme.php"))
		return true;
	else
		return false;
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
			if(is_legal_user_theme($file))
			{
				$themes[] = $file;
			}
		}
	}
	closedir($handle);
    
    if(is_array($themes) && count($themes)>0)
		return $themes;
	else // empty array as last resort.
		return array();
}
?>
