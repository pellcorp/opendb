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

function fetch_role_rs() {
	$query = "SELECT DISTINCT role_name, description, signup_avail_ind FROM s_role ORDER BY priority desc";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_role_permission_rs($role_name) {
	$query = "SELECT p.permission_name, s.remember_me_ind, p.description, s.role_name
			FROM s_permission p
			LEFT JOIN s_role_permission s ON s.permission_name = p.permission_name AND
			s.role_name = '$role_name' ";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function update_role_permissions($role_name, $permissions_r) {
	$role_name = addslashes($role_name);
	
	db_query("DELETE FROM s_role_permission WHERE role_name = '$role_name'");

	if (strlen($role_name) > 0 && is_array($permissions_r)) {
		reset($permissions_r);
		foreach ($permissions_r as $permission_name => $permission_r) {
			$enabled_ind = validate_ind_column($permission_r['enabled_ind']);
			$remember_me_ind = validate_ind_column($permission_r['remember_me_ind']);
			
			if ($enabled_ind == 'Y') {
				$query = "INSERT INTO s_role_permission(role_name, permission_name, remember_me_ind) 
					VALUES('$role_name', '$permission_name', '".$remember_me_ind."')";
				
				$insert = db_query($query);
				if ($insert && db_affected_rows () > 0) {
					opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, NULL, array (
					$role_name,
					$permission_name,
					$remember_me_ind ) );
					
				} else {
					opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), 
					array (
					$role_name,
					$permission_name,
					$remember_me_ind ) );
				}
			}
		}
	}
}
?>
