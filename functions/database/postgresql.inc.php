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
	
	MYSQL_ api wrapper - a one - to - one wrapper around each of the mysql_ functions, no opendb specific
	code, such as db configuration, etc, these will be handled by a separate function, called from
	include/begin.inc.php
*/


//		'mysql_connect', 'mysql_select_db', 'mysql_close', 'mysql_error', 'mysql_errno', 'mysql_query', 
//		'mysql_affected_rows', 'mysql_free_result',	'mysql_fetch_assoc', 'mysql_fetch_row', 'mysql_field_name', 
//		'mysql_num_rows', 'mysql_num_fields', 'mysql_insert_id');

/**
	$host - hostname:port, hostname:socket, socket
	
	@param $cache_link - if TRUE, save reference to link for reuse.
*/
function pgsql_db_connect($host, $user, $passwd, $dbname, $cache_link = TRUE)
{
	global $_opendb_dblink;
	
        $connection_string = "host=$host dbname=$dbname user=$user password=$passwd" ;
	$link = pg_connect($connection_string);
	if($link!==FALSE)
	{
		if($cache_link)
		{
			$_opendb_dblink = $link;
		}
		return $link;
	}
	
	//else
	return FALSE;
}

function pgsql_db_ping($link = NULL)
{
	global $_opendb_dblink;
	
	return @pg_ping($link!=NULL?$link:$_opendb_dblink);
}

/**
remove reference to cached link upon close
*/
function pgsql_db_close($link = NULL)
{
	global $_opendb_dblink;
	
	if($link == NULL)
	{
		$link = $_opendb_dblink;
		$_opendb_dblink = NULL;
	}
	
	return @pg_close($link);
}

function pgsql_db_error($link = NULL)
{
	global $_opendb_dblink;
	
	return @pg_last_error($link!=NULL?$link:$_opendb_dblink);
}

function pgsql_db_errno($link = NULL)
{
	global $_opendb_dblink;
	
	return 0;
}

function pgsql_db_query($sql, $link = NULL)
{
	global $_opendb_dblink;

	// expand any prefixes, display any debugging, etc
	$sql = opendb_pre_query($sql);
	
	return pg_query($link!=NULL?$link:$_opendb_dblink, $sql);
}

function pgsql_db_affected_rows($result,$link = NULL)
{
	global $_opendb_dblink;
	
	return @pg_affected_rows($result);
}

/** 
 * As postgres doesn't support auto increment, the last inserted ID cannot be retrieved...
 * one should never use this as it is a specific feature of mysql
 */
function pgsql_db_insert_id($link = NULL)
{
	global $_opendb_dblink;
	
	return 0;
}

function pgsql_db_free_result($result)
{
	return @pg_free_result($result);
}

function pgsql_db_fetch_assoc($result)
{
	return @pg_fetch_assoc($result);
}

function pgsql_db_fetch_row($result)
{
	return @pg_fetch_row($result);
}

function pgsql_db_field_name($result, $field_offset)
{
	return @pg_field_name($result, $field_offset);
}

function pgsql_db_num_rows($result)
{
	return @pg_num_rows($result);
}

function pgsql_db_num_fields($result)
{
	return @pg_num_fields($result);
}

function pgsql_db_get_table_info($table)
{
	$query = "select * from pg_tables where tablename='$table';" ;
	$result = pgsql_db_query($query) ;
	if (pgsql_db_num_rows($result))
	{
		pgsql_db_free_result($result) ;
		return(TRUE) ;
	}
	else
	{
		pgsql_db_free_result($result) ;
		return(FALSE) ;
	}
}
?>