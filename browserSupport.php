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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

if($_OpendbBrowserSniffer->isBrowserSupported())
	$pageTitle = get_opendb_lang_var('browser_supported');
else
	$pageTitle = get_opendb_lang_var('browser_not_supported');

echo _theme_header($pageTitle, FALSE);
echo("<h1>".$pageTitle."</h1>");

if(!$_OpendbBrowserSniffer->isBrowserSupported())
{
	echo("<p class=\"error\">".
		get_opendb_lang_var('browser_not_supported_text').
		"</p>");
	
	$supportedBrowsers = array(
		array('url'=>'http://www.mozilla.com/firefox/', 'icon'=>'firefox.jpg'),
		array('url'=>'http://www.microsoft.com/windows/products/winfamily/ie/default.mspx', 'icon'=>'icon_ie7.gif'),
		array('url'=>'http://www.apple.com/safari/', 'icon'=>'safari.png'),
	);
	
	echo("<ul class=\"browsers\">");
	while(list(,$browser_r) = each($supportedBrowsers))
	{
		if(file_exists('./images/browsers/'.$browser_r['icon']))
			$browser_r['icon'] = './images/browsers/'.$browser_r['icon'];
		else
			$browser_r['icon'] = NULL;
				
		echo("<li><a href=\"".$browser_r['url']."\" title=\"".$browser_r['name']."\"><img src=\"".$browser_r['icon']."\"></a></li>");
	}
	echo("</ul>");
}
echo _theme_footer();

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
