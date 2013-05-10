<?php
/* 	
    OpenDb Media Collector Database
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
include_once("lib/logging.php");
include_once("lib/utils.php");
include_once("lib/item_type.php");

function is_exists_item_type_group($s_item_type_group) {
	$query = "SELECT 'x' FROM s_item_type_group " . "WHERE s_item_type_group = '$s_item_type_group' ";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_exists_item_type_group_rltshp($s_item_type_group, $s_item_type = NULL) {
	$query = "SELECT 'x' FROM s_item_type_group_rltshp " . "WHERE s_item_type_group = '$s_item_type_group' ";

	if (strlen($s_item_type) > 0) {
		$query .= " AND s_item_type = '$s_item_type'";
	}

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function fetch_item_type_group_rs() {
	$query = "SELECT s_item_type_group, IFNULL(stlv.value, description) AS description " . "FROM s_item_type_group " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language() . "' AND
			stlv.tablename = 's_item_type_group' AND
			stlv.columnname = 'description' AND
			stlv.key1 = s_item_type_group ";

	$query .= " ORDER BY s_item_type_group";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_item_type_group_r($s_item_type_group) {
	if (strlen($s_item_type_group) > 0) {
		$s_item_type_group = strtoupper($s_item_type_group);

		$query = "SELECT s_item_type_group, description " . "FROM s_item_type_group " . "LEFT JOIN s_table_language_var stlv
				ON stlv.language = '" . get_opendb_site_language() . "' AND
				stlv.tablename = 's_item_type_group' AND
				stlv.columnname = 'description' AND
				stlv.key1 = s_item_type_group " . "WHERE s_item_type_group = '$s_item_type_group'";

		$result = db_query($query);
		if ($result && db_num_rows($result) > 0) {
			$found = db_fetch_assoc($result);
			db_free_result($result);
			return $found;
		}
	}

	//else
	return FALSE;
}

function fetch_item_type_groups_for_item_type_r($s_item_type) {
	$query = "SELECT DISTINCT sitg.s_item_type_group " . "FROM s_item_type_group sitg, s_item_type_group_rltshp sitgr " . "WHERE sitg.s_item_type_group = sitgr.s_item_type_group AND " . "sitgr.s_item_type = '" . $s_item_type . "'";

	$query .= " ORDER BY sitg.s_item_type_group";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$item_type_group_rs = NULL;

		while ($item_type_group_r = db_fetch_assoc($result))
			$item_type_group_rs[] = $item_type_group_r['s_item_type_group'];

		return $item_type_group_rs;
	} else {
		return FALSE;
	}
}

function fetch_item_type_group_rlshp_rs($s_item_type_group = NULL, $s_item_type = NULL, $order_by = FALSE) {
	$query = "SELECT sitgr.s_item_type_group, sitgr.s_item_type " . "FROM s_item_type_group sitg, s_item_type_group_rltshp sitgr " . "WHERE sitg.s_item_type_group = sitgr.s_item_type_group ";

	if (strlen($s_item_type_group) > 0)
		$query .= "AND s_item_type_group = '" . strtoupper($s_item_type_group) . "'";

	if (strlen($s_item_type) > 0)
		$query .= "AND s_item_type = '" . strtoupper($s_item_type) . "'";

	if ($order_by)
		$query .= " ORDER BY s_item_type_group, s_item_type";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_item_types_for_group_r($s_item_type_group) {
	$results = fetch_item_types_for_group_rs($s_item_type_group);
	if ($results) {
		while ($item_type_r = db_fetch_assoc($results)) {
			$item_types_r[] = $item_type_r['s_item_type'];
		}
		db_free_result($results);

		return $item_types_r;
	} else {
		return FALSE;
	}
}

function fetch_item_types_for_group_rs($s_item_type_group) {
	$query = "SELECT sit.s_item_type, IFNULL(stlv.value, sit.description) AS description, sit.order_no " . "FROM (s_item_type_group_rltshp sitgr, s_item_type sit) " . "LEFT JOIN s_table_language_var stlv
			ON stlv.language = '" . get_opendb_site_language() . "' AND
			stlv.tablename = 's_item_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = sit.s_item_type " . "WHERE sit.s_item_type = sitgr.s_item_type AND sitgr.s_item_type_group = '" . $s_item_type_group . "' " . "ORDER BY sit.order_no, sit.s_item_type";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		return $result;
	} else {
		return FALSE;
	}
}
?>