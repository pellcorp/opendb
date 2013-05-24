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

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

/**
    @param $help_page - language/page.html
 */
function validate_opendb_lang_help_page_url($help_page) {
	$index = strpos($help_page, "/");
	if ($index !== FALSE) {
		$language = substr($help_page, 0, $index);
		// ensure someone is not trying to download the /etc/passwd file or something by basename it back to a simple filename
		$page = basename(substr($help_page, $index + 1));
	}

	// make sure it ends in html
	if (is_exists_language($language) && ends_with($page, ".html") && @file_exists("./help/$language/$page")) {
		return "./help/$language/$page";
	}

	// else
	return NULL;
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		echo _theme_header(get_opendb_lang_var('help'), FALSE);

		if (($page_location = validate_opendb_lang_help_page_url($HTTP_VARS['page'])) != NULL) {
			$page_title = get_opendb_lang_var('site_help', 'site', get_opendb_config_var('site', 'title'));

			echo ("<h2>" . $page_title . "</h2>");
			// TODO: Add support for topic and subtopic
			include($page_location);
		} else {
			echo _theme_header(get_opendb_lang_var('no_help_available'), FALSE);
			echo ("<p class=\"error\">" . get_opendb_lang_var('no_help_available') . "</p>");
		}
		echo _theme_footer();
	} else { //not a valid session.
 		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
 	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo ("<p class=\"error\">" . get_opendb_lang_var('site_is_disabled') . "</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>