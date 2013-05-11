<?php
/* 	Open Media Collectors Database
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

include_once("./lib/utils.php");
include_once("./lib/fileutils.php");

function get_welcome_block_plugin_r()
{
	$handle=opendir('./welcome');
	while ($file = readdir($handle))
    {
		// Ensure valid plugin name.
		if ( !preg_match("/^\./",$file) && preg_match("/(.*).class.php$/",$file,$regs))
		{
			$welcome[] = $regs[1];
		}
	}
	closedir($handle);
    
    if(is_array($welcome) && count($welcome)>0)
		return $welcome;
	else // empty array as last resort.
		return array();
}

function renderWelcomeBlockPlugin($pluginName, $userid, $lastvisit) {
	include('./welcome/'.$pluginName.'.class.php');
	$plugin = new $pluginName;
	return $plugin->render($userid, $lastvisit);
}
?>