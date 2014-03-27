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

if (!defined('OPENDB_ADMIN_TOOLS')) {
	die('Admin tools not accessible directly');
}

function display_role_permissions_editor($HTTP_VARS) {
	global $ADMIN_TYPE;
	global $PHP_SELF;

	echo ("\n<form name=\"edit_role_permissions\" action=\"$PHP_SELF\" method=\"POST\">");
	echo ("\n<input type=\"hidden\" name=\"op\" value=\"" . $HTTP_VARS['op'] . "\">");
	echo ("\n<input type=\"hidden\" name=\"type\" value=\"" . $ADMIN_TYPE . "\">");
	echo ("\n<input type=\"hidden\" name=\"role_name\" value=\"" . $HTTP_VARS['role_name'] . "\">");

	echo ("<table>");
	echo ("<tr class=\"navbar\">
	<th>Permission</th>
	<th>Include</th>
	<th>Remember Me</th>
	</tr>");

	$results = fetch_role_permission_rs($HTTP_VARS['role_name']);
	if ($results) {
		while ($permission_r = db_fetch_assoc($results)) {
			echo("<tr>");
			echo("<td>");
			echo($permission_r['description']);
			echo ("</td>");
		
			$is_enabled = strlen($permission_r['role_name']) > 0;
			$remember_me_enabled = $permission_r['remember_me_ind'] == 'Y';

			echo ("<td><input type=\"checkbox\" class=\"checkbox\" name=\"".$permission_r['permission_name']."[enabled_ind]\" value=\"Y\"" .($is_enabled ? " CHECKED" : "")."></td>");
			echo ("<td><input type=\"checkbox\" class=\"checkbox\" name=\"".$permission_r['permission_name']."[remember_me_ind]\" value=\"Y\"" .($remember_me_enabled ? " CHECKED" : "")."></td>");
			
			echo ("</tr>");
		}
	}
	echo ("</table>");

	echo ("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit'; this.form.submit();\">");
	echo ("\n<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update'; this.form.submit();\">");

	echo ("</form>");
}

if ($HTTP_VARS['op'] == 'update') {
	$results = fetch_role_permission_rs($HTTP_VARS['role_name']);
	$defined_permissions_r = array();
	if ($results) {
		while ($permission_r = db_fetch_assoc($results)) {
			if (isset($HTTP_VARS[$permission_r['permission_name']]) && is_array($HTTP_VARS[$permission_r['permission_name']])) {
				$perm_r = $HTTP_VARS[$permission_r['permission_name']];
				
				$defined_permissions_r[$permission_r['permission_name']] = array(
						'enabled_ind'=>isset($perm_r['enabled_ind']) ? $perm_r['enabled_ind'] : 'N',
						'remember_me_ind'=>isset($perm_r['remember_me_ind']) ? $perm_r['remember_me_ind'] : 'N');
			}
		}
	}
	update_role_permissions($HTTP_VARS['role_name'], $defined_permissions_r);

	$HTTP_VARS['op'] = 'edit';
}

if ($HTTP_VARS['op'] == 'edit') {
	echo ("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</p>");

	echo ("\n<h3>Edit ${HTTP_VARS['role_name']} Role Permissions</h3>");

	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	display_role_permissions_editor($HTTP_VARS);
} else if ($HTTP_VARS['op'] == '') {
	if (is_not_empty_array($errors))
		echo format_error_block($errors);

	// list languages and options
	$results = fetch_role_rs();
	if ($results) {
		echo ("<table><tr class=\"navbar\">
			<th>Role</th>
			<th>Description</th>
			<th>Signup?</th>
			<th>&nbsp;</th>
			</tr>");

		while ($role_r = db_fetch_assoc($results)) {
			echo ("<tr>
				<td class=\"data\">" . $role_r['role_name'] . "</td>
				<td class=\"data\">" . $role_r['description'] . "</td>
				<td class=\"data\">" . $role_r['signup_avail_ind'] . "</td>
				<td class=\"data\"><a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=edit&role_name=${role_r['role_name']}\">Edit</a>");
		}
		echo ("</table>");

		db_free_result($results);
	}
}
?>