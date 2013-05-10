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

include_once("lib/fileutils.php");
include_once("lib/language.php");

/**
 $page should be a basename of $PHP_SELF with .php replaced with .html

 This function will determine whether a page can be found, or else a
 derivative of a page, based on a mapping algorithm.   The initial
 mapping algorithm will match borow.html against any pages that
 end in _borrow.html too
 */
function get_opendb_lang_help_page($language, $help_page) {
	$language = strtolower($language);

	$filelist_r = get_file_list('help/' . $language, 'html');
	if (is_array($filelist_r)) {
		for ($i = 0; $i < count($filelist_r); $i++) {
			if ($help_page == $filelist_r[$i]) {
				return $language . '/' . $filelist_r[$i];
			}
		}

		for ($i = 0; $i < count($filelist_r); $i++) {
			// HACK ALERT - borrow.html will be returned for item_borrow.html too
			if (ends_with($help_page, "_" . $filelist_r[$i])) {
				return $language . '/' . $filelist_r[$i];
			}
		}
	}

	return NULL;
}

/**
 Look for language specific help file, or fall back to english language help

 uri of page, minus any specific file reference.
 */
function get_opendb_help_page($pageid) {
	global $_OPENDB_LANGUAGE;

	if (strlen($_OPENDB_LANGUAGE) > 0) {
		$page = get_opendb_lang_help_page($_OPENDB_LANGUAGE, $pageid . '.html');
	}

	if ($page == NULL && $_OPENDB_LANGUAGE != 'english') {
		$page = get_opendb_lang_help_page('english', $pageid . '.html');
	}

	return $page;
}

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
	if (is_exists_language($language) && ends_with($page, ".html") && opendb_file_exists("help/$language/$page")) {
		return "help/$language/$page";
	}

	// else
	return NULL;
}
?>