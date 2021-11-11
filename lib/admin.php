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
include_once ('./lib/utils.php');
include_once ('./lib/http.php');

/**
	Taken from phpMyAdmin libraries/defines.lib.php

	Determines platform (OS)
	Based on a phpBuilder article:
		see http://www.phpbuilder.net/columns/tim20000821.php
*/
function get_user_browser_os() {
	$http_user_agent = get_http_env ( 'HTTP_USER_AGENT' );
	
	// 1. Platform
	if (strstr ( $http_user_agent, 'Win' ))
		return 'Win';
	else if (strstr ( $http_user_agent, 'Mac' ))
		return 'Mac';
	else if (strstr ( $http_user_agent, 'Linux' ))
		return 'Linux';
	else if (strstr ( $http_user_agent, 'Unix' ))
		return 'Unix';
	else if (strstr ( $http_user_agent, 'OS/2' ))
		return 'OS/2';
	else
		return 'Other';
}

/**
	Taken from phpMyAdmin libraries/common.lib.php
*/
function get_user_browser_crlf() {
	$browser_os = get_user_browser_os ();
	
	if ($browser_os == 'Win') // Win case
		return "\r\n";
	else if ($browser_os == 'Mac') // Mac case
		return "\r";
	else // Others
		return "\n";
}

/**
*/
function get_overflow_tooltip_column($columntext, $size = NULL) {
	if (is_numeric ( $size ) && strlen ( $columntext ) > $size) {
		return "<div onmouseover=\"show_tooltip('" . addslashes ( $columntext ) . "');\" onmouseout=\"return hide_tooltip();\">" . substr ( $columntext, 0, $size - 3 ) . '...' . "</div>";
	} else {
		return $columntext;
	}
}

function get_admin_tools_r() {
	$handle = opendir ( './admin' );
	while ( $file = readdir ( $handle ) ) {
		if ( (strpos($file, '.') === FALSE) && file_exists ( './admin/' . $file . '/index.php' )) {
			$adminlist [] = $file;
		}
	}
	closedir ( $handle );
	
	if (is_array ( $adminlist ) && count ( $adminlist ) > 0)
		return $adminlist;
	else // empty array as last resort.
		return array ();
}

function is_legal_admin_type($type) {
	if (strlen ( $type ) > 0 && file_exists ( './admin/' . $type . '/index.php' ))
		return true;
	else
		return false;
}

function get_system_admin_tools_menu($admin_type = NULL) {
	$admin_menu_rs = array (
			'config' => array (
					'link' => 'Configuration' ),
			'logfile' => array (
					'link' => 'Log File' ),
			'backup' => array (
					'link' => 'Backup Database' ),
			'http_cache' => array (
					'link' => 'HTTP Cache Admin' ),
			'item_cache' => array (
					'link' => 'Item Cache Admin' ),
			's_language' => array (
					'link' => 'Language Configuration' ),
			's_file_type' => array (
					'link' => 'Supported File Types' ),
			's_title_display_mask' => array (
					'link' => 'Title Display Mask Configuration' ),
			's_item_listing_conf' => array (
					'link' => 'Item Listing Configuration' ),
			's_status_type' => array (
					'link' => 'System Status Types' ),
			's_item_type_group' => array (
					'link' => 'System Item Type Groups' ),
			's_item_type' => array (
					'link' => 'System Item Types' ),
			's_attribute_type' => array (
					'link' => 'System Attribute Types' ),
			's_address_type' => array (
					'link' => 'System Address Types' ),
			's_site_plugin' => array (
					'link' => 'Site Plugins' ),
			'patch_facility' => array (
					'link' => 'Miscellaneous Patches' ),
			'announcements' => array (
					'link' => 'Announcements' ),
			's_role' => array (
					'link' => 'Role Permissions' ) );
	
	if ($admin_type != NULL) {
		if (is_array ( $admin_menu_rs [$admin_type] ))
			return $admin_menu_rs [$admin_type];
		else
			return NULL;
	} else {
		$menu_options_rs = array ();
		foreach ($admin_menu_rs as $id => $menu_r ) {
			$menu_r ['url'] = 'admin.php?type=' . $id;
			$menu_options_rs ['admin'] [] = $menu_r;
		}
		return $menu_options_rs;
	}
}

function execute_sql_install($ADMIN_TYPE, $sqlfile, &$errors) {
	$sqlfile = basename ( $sqlfile );
	$sqlfile = './admin/' . $ADMIN_TYPE . '/sql/' . $sqlfile;
	if (file_exists ( $sqlfile )) {
		if (exec_install_sql_file ( $sqlfile, $errors )) {
			return TRUE;
		} else {
			//$errors[] = $error;
			return FALSE;
		}
	} else {
		$errors [] = array (
				'error' => 'SQL file not found' );
		return FALSE;
	}
}

/**
 * @param unknown_type $ADMIN_TYPE
 * @param unknown_type $typeName
 * @param unknown_type $sqlRegexp - if provided, assumes that the first group is the type
 * and the second is a description.  Will display both hypen separated.
 * @param unknown_type $is_not_exists_function
 */
function generate_sql_list($ADMIN_TYPE, $typeName, $sqlRegexp, $is_not_exists_function) {
	$filelist = get_file_list ( './admin/' . $ADMIN_TYPE . '/sql/', 'sql' );
	$sitelist = NULL;
	$sqllist = NULL;
	if (is_not_empty_array ( $filelist )) {
		for($i = 0; $i < count ( $filelist ); $i ++) {
			$parsedfile_r = parse_file ( $filelist [$i] );
			
			$type = NULL;
			$description = NULL;
			if (strlen ( $sqlRegexp ) > 0) {
				if (preg_match ( $sqlRegexp, $parsedfile_r ['name'], $matches )) {
					$type = strtoupper ( $matches [1] );
					$description = str_replace ( '_', ' ', $matches [2] );
				}
			} else {
				$type = strtoupper ( $parsedfile_r ['name'] );
			}
			
			if ($is_not_exists_function ( $type )) {
				$sqllist [] = array (
						'sqlfile' => $filelist [$i],
						'type' => $type,
						'description' => $description );
			}
		}
		
		if (is_not_empty_array ( $sqllist )) {
			echo ("<table class=\"sqlList\">");
			echo ("<tr class=\"navbar\">" . "<th>" . $typeName . "</th>" . "<th>SQL File</th>" . "<th>&nbsp;</th>" . "</tr>");
			
			for($i = 0; $i < count ( $sqllist ); $i ++) {
				echo ("<tr class=\"oddRow\">" . "<td>" . $sqllist [$i] ['type'] . (strlen ( $sqllist [$i] ['description'] ) > 0 ? " - " . $sqllist [$i] ['description'] : "") . "</td>" . "<td>" . $sqllist [$i] ['sqlfile'] . "</td>" . "<td><a href=\"admin.php?type=$ADMIN_TYPE&op=installsql&sqlfile=" . $sqllist [$i] ['sqlfile'] . "\">Install</a></td>" . "</tr>");
			}
			echo ("</table>");
		}
	}
}

?>
