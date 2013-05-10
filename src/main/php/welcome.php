<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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
require_once("include/begin.inc.php");

include_once("lib/database.php");
include_once("lib/auth.php");
include_once("lib/logging.php");

include_once("lib/admin.php");
include_once("lib/http.php");
include_once("lib/user.php");
include_once("lib/email.php");
include_once("lib/language.php");
include_once("lib/item.php");
include_once("lib/theme.php");
include_once("lib/status_type.php");
include_once("lib/borrowed_item.php");
include_once("lib/whatsnew.php");
include_once("lib/announcement.php");
include_once("lib/statsdata.php");
include_once("lib/welcome.php");

function get_announcements_block() {
	$buffer = '';

	if (is_user_granted_permission(PERM_ADMIN_ANNOUNCEMENTS)) {
		// include a login warning if user password and email are still the defaults
		if (get_opendb_session_var('user_id') == 'admin') {
			$announcements_rs = get_admin_announcements_rs();
			while (list(, $announcement_r) = each($announcements_rs)) {
				$buffer .= "<li><h4>" . $announcement_r['heading'] . "</h4>
					<p class=\"content\">" . $announcement_r['message'] . "<a class=\"adminLink\" href=\"" . $announcement_r['link'] . "\">" . $announcement_r['link_text'] . "</a></p>";
			}
		}
	}

	if (get_opendb_config_var('welcome.announcements', 'enable') !== FALSE && is_user_granted_permission(PERM_VIEW_ANNOUNCEMENTS)) {
		$results = fetch_announcement_rs('submit_on', 'DESC', 0, get_opendb_config_var('welcome.announcements', 'display_count'), 'Y', 'Y');

		if ($results) {
			while ($announcement_r = db_fetch_assoc($results)) {
				$buffer .= "<li><h4>" . $announcement_r['title'] . "</h4>";
				$buffer .= "<small class=\"submitDate\">" . get_localised_timestamp(get_opendb_config_var('welcome.announcements', 'datetime_mask'), $announcement_r['submit_on']) . "</small>";
				$buffer .= "<p class=\"content\">" . nl2br($announcement_r['content']) . "</p></li>";
			}
			db_free_result($results);
		}
	}

	if (strlen($buffer) > 0) {
		return "\n<div id=\"announcements\">" . "<h3>" . get_opendb_lang_var('announcements') . "</h3>" . "\n<ul>" . $buffer . "\n</ul></div>";
	} else {
		return NULL;
	}
}

function display_last_login_block($userid, $lastvisit) {
	$plugins_r = get_welcome_block_plugin_r();
	if (is_array($plugins_r)) {
		while (list(, $plugin) = each($plugins_r)) {
			$buffer .= renderWelcomeBlockPlugin($plugin, $userid, $lastvisit);
		}
	}

	$buffer .= get_announcements_block();

	return $buffer;
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		echo _theme_header(get_opendb_lang_var('login'));

		echo (display_last_login_block(get_opendb_session_var('user_id'), get_opendb_session_var('login_lastvisit')));

		echo (_theme_footer());
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo ("<p class=\"error\">" . get_opendb_lang_var('site_is_disabled') . "</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("include/end.inc.php");
?>