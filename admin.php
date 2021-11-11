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

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/utils.php");
include_once("./lib/parseutils.php");
include_once("./lib/widgets.php");
include_once("./lib/admin.php");

define('OPENDB_ADMIN_TOOLS', 'true');

if (is_site_enabled()) {
	if (is_opendb_valid_session()) {
		if (is_user_granted_permission(PERM_ADMIN_TOOLS)) {
			$HTTP_VARS['type'] = ifempty($HTTP_VARS['type'] ?? NULL, 'config');

			$ADMIN_TYPE = $HTTP_VARS['type'];
			$ADMIN_DIR = './admin/' . $ADMIN_TYPE;

			if (file_exists("./admin/" . $ADMIN_TYPE . "/functions.php")) {
				include_once("./admin/" . $ADMIN_TYPE . "/functions.php");
			}

			if (file_exists("./admin/" . $ADMIN_TYPE . "/ajaxjobs.php")) {
				require_once("./lib/xajax/xajax_core/xajax.inc.php");

				$xajax = new xajax("admin.php?type=$ADMIN_TYPE");
				$xajax->configure('javascript URI', 'lib/xajax/');
				$xajax->configure('debug', false);
				$xajax->configure('statusMessages', true);
				$xajax->configure('waitCursor', true);

				include_once("./admin/" . $ADMIN_TYPE . "/ajaxjobs.php");

				$xajax->processRequest();
			}

			if ($HTTP_VARS['mode'] != 'job') {
				$menu_option_r = get_system_admin_tools_menu($ADMIN_TYPE);
				$title = $menu_option_r['link'] . " Admin Tool";

				_theme_header($title);

				// todo - this should really be in the <head>...</head> - does it matter?
				if (isset($xajax)) {
					$xajax->printJavascript();
				}

				echo ("<h2>" . $title . "</h2>");
			}

			include_once("./admin/" . $ADMIN_TYPE . "/index.php");

			if ($HTTP_VARS['mode'] != 'job') {
				echo _theme_footer();
			}
		} else { //not an administrator or own user.
 			opendb_not_authorised_page(PERM_ADMIN_TOOLS, $HTTP_VARS);
		}
	} else { //not a valid session.
 		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS, 'admin');
	}
} else {//if(is_site_enabled())
 	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
