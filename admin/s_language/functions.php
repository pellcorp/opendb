<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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
include_once("./functions/language.php");

define('OPENDB_LANG_INCLUDE_DEFAULT', 1);

/**
	In this case, the defaults are not the default language, but instead the 
	system table values.
*/
function fetch_system_table_column_langvar_rs($language, $table, $column, $options = NULL)
{
	if($options == OPENDB_LANG_INCLUDE_DEFAULT)
	{
		$tableconf_r = get_system_table_config($table);
		if(is_array($tableconf_r['key']))
		{
			$query = "SELECT tablename, columnname";
		
			for($i=1; $i<=count($tableconf_r['key']); $i++)
			{
				$query .= ", t.".$tableconf_r['key'][$i-1]." AS key".($i);
			}
			
			$query .= ", t.$column, stlv.value";
		
			$query .= " FROM $table t ";
				
			$query .= "LEFT OUTER JOIN s_table_language_var stlv ON 
					stlv.language = '$language' AND stlv.tablename = '$table' AND stlv.columnname = '$column' ";
		
			for($i=1; $i<=count($tableconf_r['key']); $i++)
			{
				$query .= " AND stlv.key$i = t.".$tableconf_r['key'][$i-1];
			}
		}
		else
		{
			return FALSE;
		}
	}
	else
	{
		$query = "SELECT tablename, columnname, key1, key2, key3, value
			FROM s_table_language_var
			WHERE language = '$language' AND tablename = '$table' AND columnname = '$column'";
	}

	// todo - is this legal for MySQL 4.0?	
	$query .= " ORDER BY 3"; 

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		return $result;
	}
	
	//else
	return FALSE;
}

/**
	If no options specified 
*/
function fetch_language_langvar_rs($language, $options = NULL)
{
	if($options == OPENDB_LANG_INCLUDE_DEFAULT)
	{
		$query = "SELECT dflt.value AS default_value, slv.value, dflt.varname
			FROM s_language_var dflt
			LEFT JOIN s_language_var slv
			ON slv.language = '$language' AND slv.varname = dflt.varname
			WHERE dflt.language = '".fetch_default_language()."'";
	}
	else
	{
		$query = "SELECT value, varname
			FROM s_language_var
			WHERE language = '$language'"; 
	}
	
	$query .= " ORDER BY varname ";
	
    $result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function validate_s_table($table, $key1, $key2, $key3)
{
	$tableconf_r = get_system_table_config($table);
	if(is_array($tableconf_r['key']))
	{
		if(count($tableconf_r['key']) == 1 && strlen($key1)>0 && strlen($key2)==0 && strlen($key3)==0)
			return TRUE;
		else if(count($tableconf_r['key']) == 2 && strlen($key1)>0 && strlen($key3)==0)
		{
			if(strlen($key2)>0)
				return TRUE;
			else if($tableconf_r['key'][1] == 'value' && $key2 !== NULL) // a hack for s_attribute_type_lookup
				return TRUE;
		}
		else if(count($tableconf_r['key']) == 3 && strlen($key1)>0 && strlen($key2)>0 && strlen($key3)>0)
			return TRUE;
	}
	
	//else
	return FALSE;
}

function insert_s_language_var($language, $varname, $value)
{
    if(is_exists_language($language) && strlen($varname)>0 && strlen($value)>0)
	{
		$value = addslashes($value);

    	$query = "INSERT INTO s_language_var (language, varname, value) "
				."VALUES ('$language', '$varname', '".$value."')";

		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $varname, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $varname, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function insert_s_table_language_var($language, $table, $column, $key1, $key2, $key3, $value)
{
    if(is_exists_language($language) && validate_s_table($table, $key1, $key2, $key3))
	{
		$value = addslashes($value);
	
    	$query = "INSERT INTO s_table_language_var (language, tablename, columnname, key1, key2, key3, value) "
				."VALUES ('$language', '$table', '".$column."', '".$key1."', '".$key2."', '".$key3."', '".$value."')";

		$insert = db_query($query);
		if ($insert && db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $table, $column, $key1, $key2, $key3, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $table, $column, $key1, $key2, $key3, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function update_s_language_var($language, $varname, $value)
{
    if(is_exists_language($language) && strlen($varname)>0 && strlen($value)>0)
	{
		$value = addslashes($value);
		
		$query = "UPDATE s_language_var "
			."SET value = '".$value."'"
			." WHERE language = '$language' AND "
			."varname = '$varname'";

		$update = db_query($query);

		// We should not treat updates that were not actually updated because value did not change as failures.
		if($update && ($rows_affected = db_affected_rows()) !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $varname, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $varname, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function update_s_table_language_var($language, $table, $column, $key1, $key2, $key3, $value)
{
    if(is_exists_language($language) && validate_s_table($table, $key1, $key2, $key3))
	{
		$value = addslashes($value);
		
		$query = "UPDATE s_table_language_var "
			."SET value = '".$value."'"
			." WHERE language = '$language' AND tablename = '$table' AND columnname = '$column' AND key1 = '$key1'";
			
		if(strlen($key2)>0)
			$query .= " AND key2 = '$key2'";
		else
			$query .= " AND key2 = ''";
			
		if(strlen($key3)>0)
			$query .= " AND key3 = '$key3'";
		else
			$query .= " AND key3 = ''";

		$update = db_query($query);

		// We should not treat updates that were not actually updated because value did not change as failures.
		if($update && ($rows_affected = db_affected_rows()) !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $table, $column, $key1, $key2, $key3, $value));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $table, $column, $key1, $key2, $key3, $value));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

/**
*/
function delete_s_language_var($language, $varname = NULL)
{
	if(is_exists_language($language))
	{
		$query = "DELETE FROM s_language_var ".
			"WHERE language = '$language'";

        if(strlen($varname)>0)
		{
			$query .= " AND varname = '$varname'";
		}

		$delete = db_query($query);
		// We should not treat deletes that were not actually updated because value did not change as failures.
		if($delete && ($rows_affected = db_affected_rows()) !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $varname));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $varname));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function delete_s_table_language_var($language, $table = NULL, $column = NULL, $key1 = NULL, $key2 = NULL, $key3 = NULL)
{
    if(is_exists_language($language) && validate_s_table($table, $key1, $key2, $key3))
	{
		$query = "DELETE FROM s_table_language_var ".
			"WHERE language = '$language'";
		
		if(strlen($table)>0 && strlen($column)>0)
		{	
			$query .= " AND tablename = '$table' AND columnname = '$column'";
			
			if(strlen($key1)>0)
			{
				$query .= " AND key1 = '$key1'";
				
				if(strlen($key2)>0)
					$query .= " AND key2 = '$key2'";
				else
					$query .= " AND key2 = ''";
					
				if(strlen($key3)>0)
					$query .= " AND key3 = '$key3'";
				else
					$query .= " AND key3 = ''";
			}
		}
					
		$delete = db_query($query);
		// We should not treat deletes that were not actually updated because value did not change as failures.
		if($delete && ($rows_affected = db_affected_rows()) !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language, $table, $column, $key1, $key2, $key3));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language, $table, $column, $key1, $key2, $key3));
			return FALSE;
		}
	}

	//else
	return FALSE;
}

function delete_s_language($language)
{
    if(is_exists_language($language))
	{
    	$query = "DELETE FROM s_language ".
			"WHERE language = '$language'";

		$delete = db_query($query);
		// We should not treat deletes that were not actually updated because value did not change as failures.
		if($delete && ($rows_affected = db_affected_rows()) !== -1)
		{
			if($rows_affected>0)
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($language));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($language));
			return FALSE;
		}
	}

	//else
	return FALSE;
}
?>