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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/browser.php");

$browserSupported = is_browser_supported();

if($browserSupported)
	$pageTitle = get_opendb_lang_var('browser_supported');
else
	$pageTitle = get_opendb_lang_var('browser_not_supported');

echo _theme_header($pageTitle, FALSE);
echo("<h1>".$pageTitle."</h1>");

if($browserSupported)
{
	$footer_links_r[] = array(url=>"login.php?op=login",text=>get_opendb_lang_var('return_to_login_page'));
	echo format_footer_links($footer_links_r);
}
else
{
	echo("<p class=\"error\">".
		get_opendb_lang_var('browser_not_supported_text').
		"</p>");

	$supportedBrowsers = get_opendb_supported_browsers();
	
	echo("<ul class=\"browsers\">");
	while(list(,$browser_r) = each($supportedBrowsers))
	{
		if(file_exists('./images/browsers/'.$browser_r['icon']))
			$browser_r['icon'] = './images/browsers/'.$browser_r['icon'];
		else
			$browser_r['icon'] = NULL;
				
			echo("<li><a href=\"".$browser_r['url']."\" title=\"".$browser_r['name']."\"><img src=\"".$browser_r['icon']."\"> ".$browser_r['name']."</a></li>");
	}
	echo("</ul>");
}

echo _theme_footer();

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>