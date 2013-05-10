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
include_once("lib/database.php");
include_once("lib/fileutils.php");
include_once("lib/Configuration.class.php");

// these are defined here - so they can be overriden by downstream packagers
// as required.  they will no longer be exposed via configuration.
define('OPENDB_IMPORT_CACHE_DIRECTORY', 'importcache');
define('OPENDB_ITEM_CACHE_DIRECTORY', 'itemcache');
define('OPENDB_ITEM_UPLOAD_DIRECTORY', 'upload');
define('OPENDB_HTTP_CACHE_DIRECTORY', 'httpcache');

function is_gzip_compression_enabled($php_self) {
	$page = basename($php_self, '.php');

	if (get_opendb_config_var('site.gzip_compression', 'enable') === TRUE) {
		// hard code disable for installer and url as most images already compressed
		// so is superfluous.
		if ($page != 'install' && $page != 'url' && !in_array($page, get_opendb_config_var('site.gzip_compression', 'disabled'))) {
			return TRUE;
		}
	}

	//else
	return FALSE;
}

function get_opendb_image_type() {
	return strlen(get_opendb_config_var('site', 'image_type')) > 0 ? get_opendb_config_var('site', 'image_type') : "auto";
}

/**
 * @return unknown
 */
function is_show_login_menu_enabled() {
	return get_opendb_config_var('login', 'show_menu') !== FALSE;
}

/**
    The current opendb distribution version, which takes into account
    the release and patch.
 */
function get_opendb_version() {
	return __OPENDB_RELEASE__;
}

/**
 * Enter description here...
 *
 * @param unknown_type $override_title
 * @return unknown
 */
function get_opendb_title($override_default_title = TRUE) {
	if ($override_default_title) {
		return ifempty(get_opendb_config_var('site', 'title'), __OPENDB_TITLE__);
	} else {
		return __OPENDB_TITLE__;
	}
}

function get_opendb_title_and_version() {
	return get_opendb_title(FALSE) . " " . get_opendb_version();
}

function set_opendb_config_ovrd_var($group, $id, $var) {
	global $OPENDB_CONFIGURATION;
	return $OPENDB_CONFIGURATION->setGroupVar($groupid, $id, $value);
}

function get_opendb_config_var($group, $id = NULL, $keyid = NULL) {
	global $OPENDB_CONFIGURATION;
	return $OPENDB_CONFIGURATION->getGroupVar($group, $id, $keyid);
}

function fetch_title_display_mask_rs($stdm_id) {
	$query = "SELECT stdmi.display_mask, " . "stdmi.s_item_type," . "stdmi.s_item_type_group " . "FROM s_title_display_mask_item stdmi " . "WHERE stdmi.stdm_id = '$stdm_id'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		return $result;
	} else {
		return FALSE;
	}
}
?>