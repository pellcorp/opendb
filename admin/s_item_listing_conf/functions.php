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
include_once("./lib/item_type_group.php");
include_once("./lib/item_type.php");
include_once("./lib/item_attribute.php");

function get_ilcc_derived_prompt($item_listing_column_conf_r) {
	switch ($item_listing_column_conf_r['column_type']) {
	case 's_field_type':
		switch ($item_listing_column_conf_r['s_field_type']) {
		case 'ITEMTYPE':
			return get_opendb_lang_var('type');

		case 'ITEM_ID':
			$v_attribute_type_r = fetch_attribute_type_r('S_ITEM_ID');
			return $v_attribute_type_r['prompt'];
			break;

		case 'TITLE':
			return get_opendb_lang_var('title');
			break;

		case 'OWNER':
			return get_opendb_lang_var('owner');
			break;

		case 'CATEGORY':
			return get_opendb_lang_var('category');
			break;

		case 'STATUSTYPE':
			return get_opendb_lang_var('status');
			break;

		case 'STATUSCMNT':
			return get_opendb_lang_var('status_comment');
			break;

		case 'RATING':
			$v_attribute_type_r = fetch_attribute_type_r('S_RATING');
			return $v_attribute_type_r['prompt'];
			break;
		}
		break;

	case 'action_links':
		return get_opendb_lang_var('action');
		break;

	case 'borrow_status':
		return get_opendb_lang_var('borrow_status');
		break;

	case 's_attribute_type':
		if (strlen($item_listing_column_conf_r['s_attribute_type']) > 0) {
			$v_attribute_type_r = fetch_attribute_type_r($item_listing_column_conf_r['s_attribute_type']);
			return $v_attribute_type_r['prompt'];
		} else {
			return NULL;
		}
		break;
	}

	//else
	return NULL;
}

/**
If called from insert process, the table has been locked
 */
function is_exists_s_item_listing_conf($silc_id) {
	if (strlen($silc_id) > 0) {
		$query = "SELECT 'x' FROM s_item_listing_conf WHERE id = $silc_id";
		$result = db_query($query);
		if ($result && db_num_rows($result) > 0) {
			db_free_result($result);
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function insert_s_item_listing_conf($s_item_type_group, $s_item_type) {
	if (strlen($s_item_type_group) > 0 && strlen($s_item_type) > 0) {
		if (($s_item_type_group == '*' || is_exists_item_type_group($s_item_type_group)) && ($s_item_type == '*' || is_exists_item_type($s_item_type))) {
			$query = "INSERT INTO s_item_listing_conf (s_item_type_group, s_item_type) " . "VALUES ('$s_item_type_group', '$s_item_type')";

			$insert = db_query($query);
			if ($insert && db_affected_rows() > 0) {
				$new_sequence_number = db_insert_id();

				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_item_type_group, $s_item_type));
				return $new_sequence_number;
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_item_type_group, $s_item_type));
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function validate_orderby_datatype($datatype) {
	if (strcasecmp($datatype, 'numeric') === 0)
		return 'numeric';
	else
		//if(strcasecmp($datatype, 'alpha') === 0)
		return 'alpha';
}

function validate_orderby_sort_order($sortorder) {
	if (strcasecmp($sortorder, 'asc') === 0)
		return 'asc';
	else
		return 'desc';
}

function validate_s_attribute_type($s_attribute_type) {
	$s_attribute_type = strtoupper($s_attribute_type);
	if (strlen($s_attribute_type) > 0) {
		if (!is_exists_attribute_type($s_attribute_type)) {
			return FALSE;
		}
	}

	//else
	return $s_attribute_type;
}

function validate_column_type($column_type) {
	$column_types_r = array('s_field_type', 's_attribute_type', 'action_links', 'borrow_status');

	$column_type = strtolower($column_type);
	if (in_array($column_type, $column_types_r) !== FALSE) {
		return $column_type;
	} else {
		return FALSE;
	}
}

function validate_s_field_type($s_field_type) {
	$field_types_r = array('RATING', 'STATUSTYPE', 'STATUSCMNT', 'ITEM_ID', 'CATEGORY', 'ITEMTYPE', 'OWNER', 'INTEREST', 'TITLE');

	$s_field_type = strtoupper($s_field_type);
	if (in_array($s_field_type, $field_types_r) !== FALSE) {
		return $s_field_type;
	} else {
		return FALSE;
	}
}

function validate_item_column_conf_r(&$column_conf_r, &$error) {
	return validate_item_column_conf($column_conf_r['column_no'], $column_conf_r['column_type'], $column_conf_r['s_field_type'], $column_conf_r['s_attribute_type'], $column_conf_r['override_prompt'], $column_conf_r['printable_support_ind'], $column_conf_r['orderby_support_ind'],
			$column_conf_r['orderby_datatype'], $column_conf_r['orderby_default_ind'], $column_conf_r['orderby_sort_order'], $error);
}

function validate_item_column_conf(&$column_no, &$column_type, &$s_field_type, &$s_attribute_type, &$override_prompt, &$printable_support_ind, &$orderby_support_ind, &$orderby_datatype, &$orderby_default_ind, &$orderby_sort_order, &$error) {
	$column_type = validate_column_type($column_type);
	if ($column_type !== FALSE) {
		if ($column_type == 'borrow_status' || $column_type == 'action_links') {
			$s_field_type = NULL;
			$s_attribute_type = NULL;
			$orderby_support_ind = 'N';
			$orderby_default_ind = 'N';
			$orderby_datatype = NULL;
			$orderby_sort_order = NULL;
		} else {
			if ($column_type == 's_field_type') {
				$s_field_type = validate_s_field_type($s_field_type);
				if ($s_field_type === FALSE) {
					$error = 'Invalid System Field Type';
					return FALSE;
				}

				if ($s_field_type == 'STATUSCMNT' || $s_field_type == 'RATING') {
					$orderby_support_ind = 'N';
				}
			} else {
				$s_field_type = NULL;
			}

			if ($column_type == 's_attribute_type') {
				$s_attribute_type = validate_s_attribute_type($s_attribute_type);
				if ($s_attribute_type === FALSE) {
					$error = 'Invalid System Attribute Type';
					return FALSE;
				}
			} else {
				$s_attribute_type = NULL;
			}

			$orderby_support_ind = validate_ind_column($orderby_support_ind);
			if ($orderby_support_ind == 'Y')
				$orderby_datatype = validate_orderby_datatype($orderby_datatype);
			else
				$orderby_datatype = NULL;

			$orderby_default_ind = validate_ind_column($orderby_default_ind);
			if ($orderby_default_ind == 'Y')
				$orderby_sort_order = validate_orderby_sort_order($orderby_sort_order);
			else
				$orderby_sort_order = NULL;

			$printable_support_ind = validate_ind_column($printable_support_ind);
		}

		//else
		return TRUE;
	} else {
		$error = 'Invalid Column Type';
		return FALSE;
	}
}

/**
    WARNING: Will not perform any data validations
 */
function insert_new_column_conf_set($silc_id, $column_conf_rs) {
	if (db_query("LOCK TABLES s_item_listing_column_conf WRITE, s_item_listing_conf WRITE")) {
		if (delete_s_item_listing_column_conf($silc_id)) {
			foreach ($column_conf_rs as $column_conf_r) {
				// there is not a whole lot we can do if this fails, so keep going
				insert_s_item_listing_column_conf($silc_id, $column_conf_r['column_no'], $column_conf_r['column_type'], $column_conf_r['s_field_type'], $column_conf_r['s_attribute_type'], $column_conf_r['override_prompt'], $column_conf_r['printable_support_ind'],
						$column_conf_r['orderby_support_ind'], $column_conf_r['orderby_datatype'], $column_conf_r['orderby_default_ind'], $column_conf_r['orderby_sort_order'], TRUE); //assumes validation already performed
			}

			db_query("UNLOCK TABLES");
			return TRUE;
		} else {
			db_query("UNLOCK TABLES");
			return FALSE;
		}

	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error());
		return FALSE;
	}
}

function insert_s_item_listing_column_conf($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order, $skip_validations = FALSE) {
	if (is_numeric($column_no) > 0 && strlen($column_type) > 0) {
		// ensure parent record exists
		if ($skip_validations == TRUE || is_exists_s_item_listing_conf($silc_id)) {
			if ($skip_validations == TRUE || validate_item_column_conf($column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order, $error)) {
				$query = 'INSERT INTO s_item_listing_column_conf (' . 'silc_id, ' . 'column_no, ' . 'column_type, ' . 's_field_type, ' . 's_attribute_type, ' . 'override_prompt, ' . 'printable_support_ind, ' . 'orderby_support_ind, ' . 'orderby_datatype, ' . 'orderby_default_ind, '
						. 'orderby_sort_order) ' . 'VALUES (' . "'$silc_id', " . "$column_no, " . "'$column_type', " . "'$s_field_type', " . "'$s_attribute_type', " . "'" . addslashes(trim(strip_tags($override_prompt))) . "', " . "'$printable_support_ind', " . "'$orderby_support_ind', "
						. "'$orderby_datatype', " . "'$orderby_default_ind', " . "'$orderby_sort_order') ";

				$insert = db_query($query);
				if ($insert && db_affected_rows() > 0) {
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order));
					return TRUE;
				} else {
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order));
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Parent s_item_listing_conf not found',
					array($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
 */
function update_s_item_listing_column_conf($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order, $skip_validations = FALSE) {
	if (is_numeric($column_no) > 0 && strlen($column_type) > 0) {
		// ensure parent record exists
		if ($skip_validations == TRUE || is_exists_s_item_listing_conf($silc_id)) {
			if ($skip_validations == TRUE || validate_item_column_conf($column_no, $column_type, $s_field_type, $s_attribute_type, $override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order, $error)) {
				$query = "UPDATE s_item_listing_column_conf " . "SET column_type = '$column_type', " . "s_field_type = '$s_field_type', " . "s_attribute_type = '$s_attribute_type', " . "override_prompt = '$override_prompt', " . "printable_support_ind = '$printable_support_ind', "
						. "orderby_support_ind = '$orderby_support_ind', " . "orderby_datatype = '$orderby_datatype', " . "orderby_default_ind = '$orderby_default_ind', " . "orderby_sort_order = '$orderby_sort_order' " . "WHERE silc_id = $silc_id AND column_no = $column_no";

				$update = db_query($query);

				// We should not treat updates that were not actually updated because value did not change as failures.
				if ($update && ($rows_affected = db_affected_rows()) !== -1) {
					if ($rows_affected > 0)
						opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order));
					return TRUE;
				} else {
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($silc_id, $column_no, $column_type, $s_field_type, $s_attribute_type, override_prompt, $printable_support_ind, $orderby_support_ind, $orderby_datatype, $orderby_default_ind, $orderby_sort_order));
					return FALSE;
				}
			}//if(is_exists_s_title_display_mask_item($stdm_id, $s_item_type_group, $s_item_type))
		}
	}

	//else
	return FALSE;
}

/**
 */
function delete_s_item_listing_column_conf($silc_id, $column_no = NULL) {
	// ensure parent record exists
	if (is_exists_s_item_listing_conf($silc_id)) {
		$query = "DELETE FROM s_item_listing_column_conf " . " WHERE silc_id = '$silc_id' ";

		if (is_numeric($column_no)) {
			$query .= "column_no = $column_no ";
		}

		$delete = db_query($query);
		if ($delete && ($rows_affected = db_affected_rows()) !== -1) {
			if ($rows_affected > 0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($silc_id, $column_no));
			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($silc_id, $column_no));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function delete_s_item_listing_conf($silc_id) {
	$query = "DELETE FROM s_item_listing_conf " . " WHERE id = '$silc_id' ";

	$delete = db_query($query);
	if ($delete && ($rows_affected = db_affected_rows()) !== -1) {
		if ($rows_affected > 0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($silc_id));
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($silc_id));
		return FALSE;
	}
	//else
	return FALSE;
}
?>
