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

$_OVRD_OPENDB_THEME = 'default';
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
	if(is_opendb_valid_session() && !is_site_public_access_enabled())
	{
		if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
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
				$menu_option_r = get_system_admin_tools_menu($HTTP_VARS['type']);
				$title = 'System Admin Tools - '.$menu_option_r['alt'];
				
				echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">".
						"\n<html>".
						"\n<head>");
				
				if($xajax) {
					$xajax->printJavascript();
				}
				
				echo("\n<title>".get_opendb_title_and_version()." - $title"."</title>".
						"\n<link rel=\"icon\" href=\"images/icon.gif\" type=\"image/gif\" />".
						get_theme_css('admin'));
				
                   echo('<script language="JavaScript" type="text/javascript" src="./scripts/overlibmws/overlibmws.js"></script>
		            <script language="JavaScript" type="text/javascript" src="./scripts/overlibmws/overlibmws_function.js"></script>
		            <script language="JavaScript" type="text/javascript" src="./scripts/overlibmws/overlibmws_iframe.js"></script>
		            <script language="JavaScript" type="text/javascript" src="./scripts/overlibmws/overlibmws_hide.js"></script>
                    <script language="JavaScript" type="text/javascript" src="./admin/tooltips.js"></script>
			    	<script language="JavaScript">
 					OLpageDefaults(BGCLASS, \'tooltip\', FGCLASS, \'tooltip\', TEXTFONTCLASS, \'tooltip\', CGCLASS, \'tooltip-caption\', CAPTIONFONTCLASS, \'tooltip-caption\', WRAP, WRAPMAX, 400);
					</script>');
				echo("\n</head>".
						"\n<body>");

				echo("<div id=\"header\">");
				
				echo("<h1>".get_opendb_title_and_version()." Admin Tools</h1>");
				
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
				echo("</div>");
				
				echo("<div id=\"content\" class=\"adminContent\">");
			}

			include_once("./admin/".$ADMIN_TYPE."/index.php");
			
			if($HTTP_VARS['mode'] != 'job')
			{
				echo("</div>");
				echo("<div id=\"footer\"><a href=\"http://opendb.iamvegan.net/\">".get_opendb_lang_var('powered_by_site', 'site', get_opendb_title_and_version())."</a></div>");
				echo("</body></html>");
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