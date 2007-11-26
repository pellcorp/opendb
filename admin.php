<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001-2006 by Jason Pell

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

$_OVRD_OPENDB_LANGUAGE = 'english';

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");

include_once("./functions/utils.php");
include_once("./functions/parseutils.php");
include_once("./functions/widgets.php");
include_once("./functions/admin.php");

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		if(is_opendb_user_permitted(PERM_OPENDB_ADMIN_TOOLS))
		{
			$ADMIN_TYPE = ifempty($HTTP_VARS['type'], 'config');
			$ADMIN_DIR = './admin/'.$ADMIN_TYPE;
			
			if(file_exists("./admin/".$ADMIN_TYPE."/functions.php"))
			{
				include_once("./admin/".$ADMIN_TYPE."/functions.php");
			}
			
			if(file_exists("./admin/".$ADMIN_TYPE."/ajaxjobs.php"))
			{
				require_once("./lib/xajax/xajax_core/xajax.inc.php");

				$xajax = new xajax("admin.php?type=$ADMIN_TYPE");
				$xajax->configure('javascript URI', 'lib/xajax/');
				$xajax->configure('debug', false);
				$xajax->configure('statusMessages', true);
				$xajax->configure('waitCursor', true);
				
				include_once("./admin/".$ADMIN_TYPE."/ajaxjobs.php");
				
				$xajax->processRequest();
			}

			if($HTTP_VARS['mode'] != 'job')
			{
				$menu_option_r = get_system_admin_tools_menu($ADMIN_TYPE);
				$title = $menu_option_r['link'];
				
				_theme_header($title, FALSE);

				// todo - this should really be in the <head>...</head> - does it matter?
				if($xajax) {
					$xajax->printJavascript();
				}
				
				$system_admin_tools_menu_options = get_system_admin_tools_menu();
				if($HTTP_VARS['inc_menu'] != 'N')
				{
					echo('<form id="toolType" action="admin.php"><select name="type" onChange="this.form.submit();">');
						
					reset($system_admin_tools_menu_options);
					while(list($tool, $menu_option_r) = each($system_admin_tools_menu_options))
					{
					    $admin_options[] = $menu_option_r;
					    
					    echo("\n<option value=\"".$tool."\"".($ADMIN_TYPE==$tool?" SELECTED":"").">".$menu_option_r['link']."</option>");
					}
					echo("\n</select></form>");
				}
				echo("<h2>".$system_admin_tools_menu_options[$ADMIN_TYPE]['link']."</h2>");
			}

			include_once("./admin/".$ADMIN_TYPE."/index.php");
			
			if($HTTP_VARS['mode'] != 'job')
			{
				echo _theme_footer();
			}
		}
		else //not an administrator or own user.
		{
			echo _theme_header(get_opendb_lang_var('not_authorized_to_page'), FALSE);
			echo("<p class=\"error\">".get_opendb_lang_var('not_authorized_to_page')."</p>");
			echo _theme_footer();
		}
	}
	else//not a valid session.
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS, 'admin');
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