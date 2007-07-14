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
include_once("./functions/SniffBrowser.php");

$_OPENDB_SUPPORTED_BROWSERS = array(
	array('name'=>'Firefox 1.5, 2.0', 'url'=>'http://www.mozilla.com/firefox/', 'icon'=>'firefox.jpg'),
	array('name'=>'Internet Explorer 7.0', 'url'=>'http://www.microsoft.com/windows/products/winfamily/ie/default.mspx', 'icon'=>'icon_ie7.gif'),
	array('name'=>'Internet Explorer 6.0', 'url'=>'http://www.microsoft.com/windows/ie/ie6/default.mspx', 'icon'=>'ie6.gif'),
);

function get_opendb_supported_browsers()
{
	global $_OPENDB_SUPPORTED_BROWSERS;
	
	reset($_OPENDB_SUPPORTED_BROWSERS);
	return $_OPENDB_SUPPORTED_BROWSERS;	
}

/**
	Taken from phpMyAdmin libraries/defines.lib.php

	Determines platform (OS)
	Based on a phpBuilder article:
		see http://www.phpbuilder.net/columns/tim20000821.php
*/
function get_user_browser_os()
{
	$http_user_agent = get_http_env('HTTP_USER_AGENT');

    // 1. Platform
    if (strstr($http_user_agent, 'Win'))
		return 'Win';
	else if (strstr($http_user_agent, 'Mac'))
		return 'Mac';
	else if (strstr($http_user_agent, 'Linux'))
		return 'Linux';
	else if (strstr($http_user_agent, 'Unix'))
		return 'Unix';
	else if (strstr($http_user_agent, 'OS/2'))
		return 'OS/2';
	else
		return 'Other';
}

/**
	Taken from phpMyAdmin libraries/common.lib.php
*/
function get_user_browser_crlf()
{
	$browser_os = get_user_browser_os();
	
	if ($browser_os == 'Win')// Win case
		return "\r\n";
	else if (PMA_USR_OS == 'Mac')// Mac case
		return "\r";
	else // Others
		return "\n";
}

/**
	Browser currently not supported include:
		IE 5.0 or less on windows
		IE on the macintosh
		Netscape 4 or less
*/
function is_browser_supported()
{
	$userAgent = get_http_env('HTTP_USER_AGENT');
	$browser = SniffBrowser($userAgent);
	if($browser)
	{
		if($browser['type'] == 'Netscape')
		{
			if(substr($browser['version'],0,1) == '4')
				return FALSE;
		}
		else if($browser['type'] == 'Explorer')
		{
			// no support for IE at all on the Mac
			if($browser['platform'] == 'Mac')
			{
				return FALSE;
			}
			else if (substr($browser['version'],0,1) == '4' || substr($browser['version'],0,1) == '5')
			{
				return FALSE;
			}
		}
	}
	
	//else
	return TRUE;
}
?>