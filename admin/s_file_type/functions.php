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

include_once("./lib/file_type.php");

function fetch_s_file_type_content_group_rs() {
	$query = "SELECT content_group " . "FROM s_file_type_content_group " . "ORDER BY content_group";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_file_type_rs() {
	$query = "SELECT sft.content_type, sfte.extension, sft.content_group, sft.description, sft.image, sft.thumbnail_support_ind " . "FROM s_file_type sft, " . "s_file_type_extension sfte " . "WHERE sft.content_type = sfte.content_type AND " . "sfte.default_ind = 'Y' " . "ORDER BY sft.content_type";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_s_file_type_alt_extension_r($content_type) {
	$query = "SELECT extension " . "FROM s_file_type_extension " . "WHERE content_type = '$content_type' AND default_ind <> 'Y' " . "ORDER BY extension";

	$results = db_query($query);
	if ($results && db_num_rows($results) > 0) {
		$alt_extensions_r = array();
		while ($extension_r = db_fetch_assoc($results)) {
			$alt_extensions_r[] = $extension_r['extension'];
		}
		db_free_result($results);

		return $alt_extensions_r;
	} else {
		return FALSE;
	}
}

function insert_s_file_type($content_type, $content_group, $extension, $alt_extensions_r, $description, $image, $thumbnail_support_ind) {
	$content_type = validate_content_type($content_type);
	if (strlen($content_type) > 0 && !is_exists_file_type($content_type)) {
		$content_group = strtoupper($content_group);
		if (is_exists_file_type_content_group($content_group)) {
			$description = addslashes(trim(strip_tags($description)));
			$thumbnail_support_ind = validate_ind_column($thumbnail_support_ind);

			$query = "INSERT INTO s_file_type ( content_type, description, content_group, image, thumbnail_support_ind)" . "VALUES ('$content_type', '$description', '$content_group', '$image', '$thumbnail_support_ind')";

			$insert = db_query($query);
			$rows_affected = db_affected_rows();
			if ($insert && $rows_affected !== -1) {
				if ($rows_affected > 0)
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($extension, $description, $content_group, $content_type, $image, $thumbnail_support_ind));

				return insert_s_file_type_extensions($content_type, $extension, $alt_extensions_r);
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($extension, $description, $content_group, $content_type, $image, $thumbnail_support_ind));
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/*
 */
function update_s_file_type($content_type, $content_group, $extension, $alt_extensions_r, $description, $image, $thumbnail_support_ind) {
	$content_type = validate_content_type($content_type);
	if (is_exists_file_type($content_type)) {
		$content_group = strtoupper($content_group);
		if (is_exists_file_type_content_group($content_group)) {
			$description = addslashes(trim(strip_tags($description)));
			$thumbnail_support_ind = validate_ind_column($thumbnail_support_ind);

			$query = "UPDATE s_file_type " . "SET description = '$description', " . "content_group = '$content_group', " . "image = '$image', " . "thumbnail_support_ind = '$thumbnail_support_ind' " . " WHERE content_type = '$content_type'";

			$update = db_query($query);

			// We should not treat updates that were not actually updated because value did not change as failures.
			$rows_affected = db_affected_rows();
			if ($update && $rows_affected !== -1) {
				if ($rows_affected > 0)
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($content_type, $content_group, $extension, $alt_extensions_r, $description, $image, $thumbnail_support_ind));

				insert_s_file_type_extensions($content_type, $extension, $alt_extensions_r);
				return TRUE;
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($content_type, $content_group, $extension, $alt_extensions_r, $description, $image, $thumbnail_support_ind));
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
@param $extension - its a hack, but we pass this in, so its easier to filter it out of the alternate extensions list.
 */
function insert_s_file_type_extensions($content_type, $default_extension, $alt_extensions_r) {
	$content_type = validate_content_type($content_type);
	if (is_exists_file_type($content_type)) {
		$default_extension = strtolower(trim($default_extension));

		if (strlen($default_extension) > 0) {
			if (delete_s_file_type_extensions($content_type)) {
				if (is_array($alt_extensions_r))
					$extensions_r = array_merge(array($default_extension), $alt_extensions_r);
				else
					$extensions_r[] = $default_extension;

				foreach ($extensions_r as $extension) {
					$extension = strtolower(trim($extension));
					if (strlen($extension) > 0) {
						$query = "INSERT INTO s_file_type_extension ( content_type, extension, default_ind )" . "VALUES ('$content_type', '" . $extension . "', '" . ($extension == $default_extension ? 'Y' : 'N') . "')";

						$insert = db_query($query);
						$rows_affected = db_affected_rows();
						if ($insert && $rows_affected !== -1) {
							if ($rows_affected > 0)
								opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($content_type, $default_extension, $extensions_r));
						} else {
							$errno = db_errno();
							if ($errno != 1062) { // ignore duplicate row exception
								opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($content_type, $default_extension, $extensions_r));
								return FALSE;
							}
						}
					}
				}
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	} else {
		return FALSE;
	}
}

function delete_s_file_type($content_type) {
	$content_type = validate_content_type($content_type);
	if (is_exists_file_type($content_type)) {
		if (delete_s_file_type_extensions($content_type)) {
			$query = "DELETE FROM s_file_type " . "WHERE content_type = '$content_type'";
			$delete = db_query($query);

			// We should not treat updates that were not actually updated because value did not change as failures.
			$rows_affected = db_affected_rows();
			if ($delete && $rows_affected !== -1) {
				if ($rows_affected > 0)
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($content_type));
				return TRUE;
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($content_type));
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function delete_s_file_type_extensions($content_type) {
	$content_type = validate_content_type($content_type);
	if (is_exists_file_type($content_type)) {
		$query = "DELETE FROM s_file_type_extension " . "WHERE content_type = '$content_type'";

		$delete = db_query($query);
		$rows_affected = db_affected_rows();
		if ($delete && $rows_affected !== -1) {
			if ($rows_affected > 0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($content_type));

			return TRUE;
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($content_type));
			return FALSE;
		}
	} else {
		return FALSE;
	}
}
?>
