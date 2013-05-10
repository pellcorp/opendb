<?php
/* 	Open Media Collectors Database
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

include_once("lib/utils.php");
include_once("lib/fileutils.php");

function get_welcome_block_plugin_r() {
	$filelist = get_file_list('lib/welcome');

	$welcome = array();
	while (list(, $file) = @each($filelist)) {
		if (preg_match("/(.*).class.php$/", $file, $regs)) {
			$welcome[] = $regs[1];
		}
	}
	return $welcome;
}

function renderWelcomeBlockPlugin($pluginName, $userid, $lastvisit) {
	include('lib/welcome/' . $pluginName . '.class.php');
	$plugin = new $pluginName;
	return $plugin->render($userid, $lastvisit);
}

function get_admin_announcements_rs() {
	$announcements_rs = array();

	$user_cnt = fetch_user_cnt(NULL, INCLUDE_ROLE_PERMISSIONS, EXCLUDE_CURRENT_USER, INCLUDE_ACTIVATE_USER);
	if ($user_cnt > 0) {
		$announcements_rs[] = array(heading => get_opendb_lang_var('activate_users'), message => get_opendb_lang_var('there_are_no_of_users_awaiting_activation', array('no_of_users' => $user_cnt)), link => "user_listing.php?restrict_active_ind=X", link_text => get_opendb_lang_var('activate_users'));
	}

	if (validate_user_passwd(get_opendb_session_var('user_id'), 'admin')) {
		$announcements_rs[] = array(heading => get_opendb_lang_var('change_admin_user_password'), message => get_opendb_lang_var('change_admin_user_password_msg'), link => "user_admin.php?op=change_password&user_id=" . get_opendb_session_var('user_id'), link_text => get_opendb_lang_var(
				'change_my_password'));
	}

	if (fetch_user_email(get_opendb_session_var('user_id')) == 'opendb@iamvegan.net') {
		$announcements_rs[] = array(heading => get_opendb_lang_var('change_admin_user_email'), message => get_opendb_lang_var('change_admin_user_email_msg'), link => "user_admin.php?op=edit&user_id=" . get_opendb_session_var('user_id'), link_text => get_opendb_lang_var('edit_my_info'));
	}

	if (!is_exists_any_item_type()) {
		$admin_type_r = get_system_admin_tools_menu('s_item_type');
		$announcements_rs[] = array(heading => get_opendb_lang_var('no_item_types'), message => get_opendb_lang_var('add_new_item_type_msg'), link => "admin.php?type=s_item_type", link_text => $admin_type_r['link'] . ' Admin Tool');
	}

	if (!is_exists_any_site_plugin()) {
		$admin_type_r = get_system_admin_tools_menu('s_site_plugin');
		$announcements_rs[] = array(heading => get_opendb_lang_var('no_site_plugins'), message => get_opendb_lang_var('add_new_site_plugin_msg'), link => "admin.php?type=s_site_plugin", link_text => $admin_type_r['link'] . ' Admin Tool');
	}

	return $announcements_rs;
}

function display_last_login_block($userid, $lastvisit) {
	$buffer = '';

	$plugins_r = get_welcome_block_plugin_r();
	while (list(, $plugin) = each($plugins_r)) {
		$buffer .= renderWelcomeBlockPlugin($plugin, $userid, $lastvisit);
	}

	$buffer .= get_announcements_block();

	return $buffer;
}
?>