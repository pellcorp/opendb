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

include_once('./lib/item_type.php');
include_once('./lib/item_attribute.php');

function get_s_attribute_type_tooltip_array($s_attribute_type_list_rs) {
	$arrayOfAttributes = "arrayOfSystemAttributeTypeTooptips = new Array(" . count($s_attribute_type_list_rs) . ");\n";
	$count = 0;
	reset($s_attribute_type_list_rs);
	foreach ($s_attribute_type_list_rs as $s_attribute_type_r) {
		$arrayOfAttributes .= "arrayOfSystemAttributeTypeTooptips[$count] = new SystemAttributeTypeTooltip('" . addslashes($s_attribute_type_r['s_attribute_type']) . "', '" . addslashes($s_attribute_type_r['description']) . "', '" . addslashes($s_attribute_type_r['prompt']) . "', '"
				. addslashes($s_attribute_type_r['input_type']) . "', '" . addslashes($s_attribute_type_r['display_type']) . "', '" . addslashes($s_attribute_type_r['s_field_type']) . "', '" . addslashes($s_attribute_type_r['site_type']) . "');\n";
		$count++;
	}

	return "<script language=\"JavaScript\">" . $arrayOfAttributes . "</script>";
}

function check_item_type_structure($s_item_type, &$error) {
	if (is_exists_item_type($s_item_type)) {
		$missing_s_field_types = NULL;

		if (!fetch_sfieldtype_item_attribute_type($s_item_type, 'TITLE'))
			$missing_s_field_types[] = 'TITLE';

		if (!fetch_sfieldtype_item_attribute_type($s_item_type, 'STATUSTYPE'))
			$missing_s_field_types[] = 'STATUSTYPE';

		if (!fetch_sfieldtype_item_attribute_type($s_item_type, 'STATUSCMNT'))
			$missing_s_field_types[] = 'STATUSCMNT';

		if (!fetch_sfieldtype_item_attribute_type($s_item_type, 'CATEGORY'))
			$missing_s_field_types[] = 'CATEGORY';

		if (get_opendb_config_var('borrow', 'enable') !== FALSE && get_opendb_config_var('borrow', 'duration_support') !== FALSE) {
			if (!fetch_sfieldtype_item_attribute_type($s_item_type, 'DURATION'))
				$missing_s_field_types[] = 'DURATION';
		}

		if (is_not_empty_array($missing_s_field_types)) {
			$error = array('error' => 'The following Field Type attribute relationships are missing.', 'detail' => $missing_s_field_types);

			return FALSE;
		} else {
			// No errors so no problem.
			return TRUE;
		}
	} else {
		// no message if s_item_type does not even exist.
		return FALSE;
	}
}

/**
 * If any items found with the specified s_item_type, then
 * the s_item_type is not deletable.
 */
function is_s_item_type_deletable($s_item_type) {
	$query = "SELECT 'x' FROM item i WHERE i.s_item_type='" . $s_item_type . "'";
	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return FALSE;
	}

	//else
	return TRUE;
}

function is_exists_non_instance_item_attributes($s_item_type, $s_attribute_type, $order_no) {
	$query = "SELECT 'x' FROM item i, item_attribute ia " . "WHERE i.id = ia.item_id AND i.s_item_type = '$s_item_type' AND " . "ia.s_attribute_type = '$s_attribute_type' AND ia.order_no = '$order_no' AND instance_no = 0 ";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

/**
 * If any item_attributes found with the specified s_item_type, s_attribute_type
 * and order_no then the s_item_attribute_type record is not deletable.
 */
function is_s_item_attribute_type_deletable($s_item_type, $s_attribute_type, $order_no) {
	$query = "SELECT 'x' FROM item i, item_attribute ia " . "WHERE i.id = ia.item_id AND i.s_item_type = '$s_item_type' AND " . "ia.s_attribute_type = '$s_attribute_type' AND ia.order_no = '$order_no'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return FALSE;
	}

	//else
	return TRUE;
}

function is_s_item_attribute_type_with_order_no($s_item_type, $s_attribute_type, $order_no) {
	$query = "SELECT 'x' FROM s_item_attribute_type siat " . "WHERE siat.s_item_type = '$s_item_type' AND " . "siat.s_attribute_type = '$s_attribute_type' AND " . "siat.order_no = '$order_no'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

/**
 */
function is_exists_item_attribute_type_field_type($s_item_type, $s_field_type) {
	$query = "SELECT 'x' FROM s_item_attribute_type siat, s_attribute_type sat " . "WHERE siat.s_attribute_type = sat.s_attribute_type AND siat.s_item_type = '$s_item_type' AND sat.s_field_type = '$s_field_type'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

/**
    Fetch a list of all s_attribute_type's including reserved list.
 */
function fetch_item_type_s_attribute_type_rs($orderby = "s_attribute_type", $order = "asc") {
	$query = "SELECT s_attribute_type, s_field_type, description, prompt, input_type, display_type, site_type FROM s_attribute_type " . "WHERE (s_field_type IS NULL OR s_field_type NOT IN ('ADDRESS', 'RATING')) " . "ORDER BY $orderby $order";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

/*
 * Fetch a list of ALL s_attribute_type's
 */
function fetch_sfieldtype_attribute_type_rs($s_field_type, $orderby = "s_attribute_type", $order = "asc") {
	$query = "SELECT s_attribute_type, s_field_type, description, prompt, input_type, display_type, site_type FROM s_attribute_type ";

	if (is_array($s_field_type))
		$query .= "WHERE s_field_type IN(" . format_sql_in_clause($s_field_type) . ") ";
	else
		$query .= "WHERE s_field_type = '$s_field_type' ";

	$query .= "ORDER BY $orderby $order";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_type_rs($orderby = "order_no", $order = "asc") {
	$query = "SELECT s_item_type, order_no, description, image FROM s_item_type ORDER BY $orderby $order";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_attribute_type_rs($s_item_type) {
	$query = "SELECT siat.s_attribute_type, siat.order_no, siat.prompt, siat.instance_attribute_ind, siat.compulsory_ind, sat.s_field_type, sat.site_type, siat.rss_ind, siat.printable_ind FROM s_item_attribute_type siat, s_attribute_type sat WHERE siat.s_attribute_type = sat.s_attribute_type AND siat.s_item_type = '$s_item_type' ORDER BY siat.order_no ASC";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_item_type_r($s_item_type) {
	$query = "SELECT s_item_type, order_no, description, image FROM s_item_type WHERE s_item_type = '$s_item_type'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}

	//else
	return FALSE;
}

/*
 * This function will insert the initial s_item_type only, no reference to the
 * s_item_attribute_type's which will come later.
 */ 
function insert_s_item_type($s_item_type, $order_no, $description, $image) {
	$description = addslashes(trim(strip_tags($description)));

	$query = "INSERT INTO s_item_type (s_item_type, order_no, description, image) " . "VALUES ('$s_item_type', " . (is_numeric($order_no) ? "'$order_no'" : "NULL") . ", '$description', '$image')";
	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $order_no, $description, $image));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $order_no, $description, $image));
		return FALSE;
	}
}

function update_s_item_type($s_item_type, $order_no, $description, $image) {
	$description = addslashes(trim(strip_tags($description)));

	$query = "UPDATE s_item_type " . "SET " . ($order_no !== FALSE ? " order_no = " . (is_numeric($order_no) ? "'$order_no', " : "NULL, ") : "") . "description = '$description' " . ", image = '$image' " . "WHERE s_item_type = '$s_item_type'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $order_no, $description, $image));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $order_no, $description, $image));
		return FALSE;
	}
}

function delete_s_item_type($s_item_type) {
	$query = "DELETE FROM s_item_type " . "WHERE s_item_type = '$s_item_type'";

	$delete = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($delete && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type));
		return FALSE;
	}
}

function insert_s_item_attribute_type($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind) {
	$prompt = addslashes(trim(strip_tags($prompt)));
	$s_field_type = strtoupper(trim($s_field_type));

	// Ensure we have a valid instance_attribute_ind value.
	$instance_attribute_ind = strtoupper(trim($instance_attribute_ind));
	if ($instance_attribute_ind != 'Y')
		$instance_attribute_ind = 'N';

	// Ensure we have a valid compulsory_ind value.	
	$compulsory_ind = strtoupper(trim($compulsory_ind));
	if ($compulsory_ind != 'Y')
		$compulsory_ind = 'N';

	// Ensure we have a valid rss_ind value.	
	$rss_ind = strtoupper(trim($rss_ind));
	if ($rss_ind != 'Y')
		$rss_ind = 'N';

	// Ensure we have a valid printable_ind value.	
	$printable_ind = strtoupper(trim($printable_ind));
	if ($printable_ind != 'Y')
		$printable_ind = 'N';

	$query = "INSERT INTO s_item_attribute_type (s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, rss_ind, printable_ind) " . "VALUES ('$s_item_type', '$s_attribute_type', " . (is_numeric($order_no) ? "'$order_no'" : "0")
			. ", '$prompt', '$instance_attribute_ind', '$compulsory_ind', '$rss_ind', '$printable_ind')";
	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind));
		return FALSE;
	}
}

function update_s_item_attribute_type_order_no($s_item_type, $s_attribute_type, $old_order_no, $order_no) {
	$query = "UPDATE s_item_attribute_type " . "SET order_no = '$order_no' " . "WHERE s_item_type = '$s_item_type' AND s_attribute_type = '$s_attribute_type' AND order_no = '$old_order_no'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $s_attribute_type, $old_order_no, $order_no));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $old_order_no, $order_no));
		return FALSE;
	}
}

function update_s_item_attribute_type($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind) {
	$prompt = addslashes(trim(strip_tags($prompt)));

	// Ensure we have a valid instance_attribute_ind value.
	$instance_attribute_ind = strtoupper(trim($instance_attribute_ind));
	if ($instance_attribute_ind != 'Y')
		$instance_attribute_ind = 'N';

	// Ensure we have a valid compulsory_ind value.
	$compulsory_ind = strtoupper(trim($compulsory_ind));
	if ($compulsory_ind != 'Y')
		$compulsory_ind = 'N';

	// Ensure we have a valid rss_ind value.
	$rss_ind = strtoupper(trim($rss_ind));
	if ($rss_ind != 'Y')
		$rss_ind = 'N';

	// Ensure we have a valid printable_ind value.
	$printable_ind = strtoupper(trim($printable_ind));
	if ($printable_ind != 'Y')
		$printable_ind = 'N';

	$query = "UPDATE s_item_attribute_type " . "SET prompt = '$prompt', " . "instance_attribute_ind = '$instance_attribute_ind', " . "compulsory_ind = '$compulsory_ind', " . "rss_ind = '$rss_ind', " . "printable_ind = '$printable_ind' "
			. "WHERE s_item_type = '$s_item_type' AND s_attribute_type = '$s_attribute_type' AND order_no = '$order_no'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no, $prompt, $instance_attribute_ind, $compulsory_ind, $rss_ind, $printable_ind));
		return FALSE;
	}
}

function delete_s_item_attribute_type($s_item_type, $s_attribute_type, $order_no) {
	$query = "DELETE FROM s_item_attribute_type " . "WHERE s_item_type = '$s_item_type'";

	if (strlen($s_attribute_type) > 0) {
		$query .= " AND s_attribute_type = '$s_attribute_type'";
	}

	if (is_numeric($order_no)) {
		$query .= " AND order_no = '$order_no'";
	}

	$delete = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($delete && $rows_affected !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $s_attribute_type, $order_no));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no));
		return FALSE;
	}
}

function delete_item_attribute_order_no($s_item_type, $s_attribute_type, $order_no) {
	// have to use alias to lock table! -- http://dev.mysql.com/doc/mysql/en/LOCK_TABLES.html
	if (db_query("LOCK TABLES item AS i WRITE, item_attribute AS ia WRITE, item_attribute WRITE")) {
		$results = db_query("SELECT DISTINCT ia.item_id " . "FROM item i, item_attribute ia " . "WHERE i.id = ia.item_id AND " . "i.s_item_type = '$s_item_type' AND " . "ia.s_attribute_type = '$s_attribute_type' AND " . "ia.order_no = $order_no");

		if ($results) {
			while ($item_attribute_r = db_fetch_assoc($results)) {
				$update = db_query("DELETE FROM item_attribute " . "WHERE item_id = " . $item_attribute_r['item_id'] . " AND s_attribute_type = '$s_attribute_type' AND order_no = '$order_no'");

				// We should not treat updates that were not actually updated because value did not change as failures.
				$rows_affected = db_affected_rows();
				if ($update && $rows_affected !== -1) {
					if ($rows_affected > 0)
						opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type, $s_attribute_type, $order_no));
				} else {
					db_query("UNLOCK TABLES");

					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no));
					return FALSE;
				}
			}
			db_free_result($results);

			db_query("UNLOCK TABLES");
			return TRUE;
		} else {
			db_query("UNLOCK TABLES");

			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no));
			return FALSE;
		}
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type, $s_attribute_type, $order_no));
		return FALSE;
	}
}
?>
