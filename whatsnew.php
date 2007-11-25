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

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/whatsnew.php");
include_once("./functions/scripts.php");

/**
 * This class is designed to execute under a public access enabled site if you want to embed it in
 * other sites.
 * 
 * You must copy the following javascript to your site and put it into a script/ directory:
 * 
 * 	script/common.js
 *  script/marquee.js
 * 
 * You can use the following PHP to embed this into your site (assuming you have Snoopy.class.php
 * available):
 * 
 *	<?php 
 *	include_once("./Snoopy.class.php"); 
 *	$snoopy = new Snoopy(); 
 *	$snoopy->fetch("http://127.0.0.1/jason/opendb/whatsnew.php"); 
 *	if($snoopy->status >= 200 && $snoopy->status<300) 
 *	{ 
 *		echo $snoopy->results; 
 *	} 
 *	?>
 */
if(is_site_enabled())
{
	if (is_opendb_valid_session() || is_site_public_access())
	{
		$HTTP_VARS['op'] = ifempty($HTTP_VARS['op'], 'marquee');
		
		if($HTTP_VARS['op'] == 'marquee')
		{
			echo(get_javascript('common.js'));
			echo(get_javascript('marquee.js'));
			
			echo("\n<div id=\"lastitemlist-container\">".
				get_last_item_list_marquee(
					get_last_item_list(
						get_opendb_config_var('login.last_items_list', 'total_num_items'),
						NULL, //$owner_id
						NULL, //$s_item_type
						NULL, //$update_on
						NULL, //$not_owner_id
						get_site_url(), //$site_url_prefix
						TRUE)).  //$is_popup_item_display
			"\n</div>");
						
			echo("\n<script language=\"JavaScript\">
			addEvent(
				window, 
				'load', 
				function(){startMarquee('lastitemlist-container', 'lastitemlist-item', 2000);} );
			</script>");
		}
	}
	else
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())
else
{
	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo("<p class=\"error\">".get_opendb_lang_var('site_is_disabled')."</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>