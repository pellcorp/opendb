<?php
/* 	
	OpenDb Media Collector Database
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
include_once ("./lib/database.php");
include_once ("./lib/logging.php");

function is_exists_file_type($content_type) {
	$content_type = validate_content_type ( $content_type );
	if (strlen ( $content_type ) > 0) {
		$query = "SELECT 'x' FROM s_file_type WHERE content_type = '" . strtolower ( $content_type ) . "'";
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function is_exists_file_type_content_group($content_group) {
	if (strlen ( $content_group ) > 0) {
		$query = "SELECT 'x' FROM s_file_type_content_group WHERE content_group = '" . $content_group . "'";
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			db_free_result ( $result );
			return TRUE;
		}
	}
	//else
	return FALSE;
}

function fetch_file_type_for_extension($extension) {
	$extension = strtolower ( trim ( $extension ) );
	if (strlen ( $extension ) > 0) {
		$query = "SELECT DISTINCT content_type
		FROM s_file_type_extension
		WHERE extension = '" . $extension . "'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $record_r ['content_type'];
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function fetch_file_type_r($content_type) {
	$content_type = validate_content_type ( $content_type );
	if (strlen ( $content_type ) > 0) {
		$query = "SELECT DISTINCT sft.content_group, sfte.extension, sft.content_type, sft.description, sft.image, sft.thumbnail_support_ind 
		FROM s_file_type sft,
		s_file_type_extension sfte
		WHERE sft.content_type = sfte.content_type AND 
		sfte.default_ind = 'Y' AND 
		sft.content_type = '" . $content_type . "'";
		
		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$record_r = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $record_r;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
	If content type includes a ; encoding remove it before checking
*/
function validate_content_type($content_type) {
	$content_type = strtolower ( trim ( $content_type ) );
	$index = strpos ( $content_type, ';' );
	if ($index !== FALSE) {
		$content_type = substr ( $content_type, 0, $index );
	}
	
	return $content_type;
}

/**
	Its possible to specify an array of content groups
*/
function fetch_file_type_extensions_r($content_group) {
	if (! is_array ( $content_group ) && strlen ( $content_group ) > 0)
		$content_group_r [] = $content_group;
	else if (is_array ( $content_group ))
		$content_group_r = & $content_group;
	
	$query = "SELECT sfte.extension
	FROM 	s_file_type sft,
			s_file_type_extension sfte 
	WHERE	sft.content_type = sfte.content_type";
	
	if (is_array ( $content_group_r )) {
		$query .= " AND sft.content_group IN(" . format_sql_in_clause ( $content_group_r ) . ")";
	}
	
	$extensions_r = array ();
	$results = db_query ( $query );
	if ($results && db_num_rows ( $results ) > 0) {
		while ( $extensions_rs = db_fetch_assoc ( $results ) ) {
			$extensions_r [] = strtolower ( $extensions_rs ['extension'] );
		}
		
		db_free_result ( $results );
	}
	return $extensions_r;
}
?>