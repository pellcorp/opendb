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

$_opendb_dblink = NULL;
$_opendb_dbtype = NULL ;
//		'mysql_connect', 'mysql_select_db', 'mysql_close', 'mysql_error', 'mysql_errno', 'mysql_query', 
//		'mysql_affected_rows', 'mysql_free_result',	'mysql_fetch_assoc', 'mysql_fetch_row', 'mysql_field_name', 
//		'mysql_num_rows', 'mysql_num_fields', 'mysql_insert_id');

/**
	$host - hostname:port, hostname:socket, socket
	
	@param $cache_link - if TRUE, save reference to link for reuse.
*/
function db_connect($host, $user, $passwd, $dbname, $dbtype, $cache_link = TRUE)
{
	global $_opendb_dblink,$_opendb_dbtype;
        $_opendb_dbtype = $dbtype ;
	switch($dbtype) 
        {
             case mysql:
                $link = mysql_db_connect($host, $user, $passwd, $dbname, $cache_link) ;
                break ;
            case postgresql:
                $link = @pgsql_db_connect($host, $user, $passwd, $dbname, $cache_link) ;
                break ;
	
        }
	if($link!==FALSE)
	{
                switch($dbtype)
                {
                    case mysql:
		          if($cache_link)
		          {
				$_opendb_dblink = $link;
		          }
		          return $link ;

                          break ;
                    case postgresql:
                          if($cache_link)
		          {
				$_opendb_dblink = $link;
		          }
                        return $link ;
                        break ;
                }
	}
	
	//else
	return FALSE;
}

function db_ping($link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:
                return @mysql_db_ping($link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:
                return @pgsql_db_ping($link!=NULL?$link:$_opendb_dblink);
                break ;
        }
}

/**
remove reference to cached link upon close
*/
function db_close($link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	
	if($link == NULL)
	{
		$link = $_opendb_dblink;
		$_opendb_dblink = NULL;
	}
	switch ($_opendb_dbtype) 
        {
            case mysql:
	        return @mysql_db_close($link);
                break ;
            case postgresql:
	        return @pgsql_db_close($link);
                break ;
        }
}

function db_error($link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:
	        return @mysql_db_error($link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:
	        return @pgsql_db_error($link!=NULL?$link:$_opendb_dblink);
                break ;
        }
}

function db_errno($link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_errno($link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:	
	        return @pgsql_db_errno($link!=NULL?$link:$_opendb_dblink);
                break ;
        }

}

function db_query($sql, $link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	// expand any prefixes, display any debugging, etc
	$sql = opendb_pre_query($sql);
	switch ($_opendb_dbtype) 
        {
            case mysql:
	        return mysql__db_query($sql, $link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:
		// try to make some query to work with postgres
		// attention !!! heavy use of hack here !!!

		// this one is quotes user table as user is a special keyword in postgresql
		$sql = preg_replace("/\buser\b/", "\"user\"", $sql) ;

		// This one is for locking tables
		if (preg_match("/^LOCK\s*TABLES\s*([a-zA-Z_]*)\s*([a-zA-Z]*)/", $sql, $matches)) {
			$newsql = "LOCK TABLE ".$matches[1]." ";
			switch(strtoupper($matches[2])) {
				case WRITE:
					$newsql .= "IN EXCLUSIVE MODE" ;
					break;
				case READ:
				default:
					$newsql .= "IN SHARE MODE" ;
					break ;
			}
			$sql = $newsql ;
		}
		// This one is for unlocking tables
		if (preg_match("/^UNLOCK\s*TABLES/", $sql)) {
			$alltablesquery = "select tablename from pg_tables where tablename not like 'pg_%';" ;
			$alltables = db_query($alltablesquery, $link) ;
			while($row = db_fetch_row($alltables)) {
				$unlockquery = "LOCK TABLE ".$row[0] ." IN SHARE MODE" ;
				db_query($unlockquery, $link) ;
			}
			return(true) ;
		}
	        return @pgsql_db_query($sql, $link!=NULL?$link:$_opendb_dblink);
                break ;
        }
	
}

function db_affected_rows($link = NULL, $result = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_affected_rows($link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:
		// may remove once running
/*
		if ($result == NULL) 
		{
			echo "DEBUG :Needs a result when getting affected rows in postgres" ;
			print_r(apd_callstack());
		}
*/
	        return @pgsql_db_affected_rows($result);
                break ;
        }
	
}

function db_insert_id($link = NULL)
{
	global $_opendb_dblink,$_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_insert_id($link!=NULL?$link:$_opendb_dblink);
                break ;
            case postgresql:
	        return @pgsql_db_insert_id($link!=NULL?$link:$_opendb_dblink);
                break ;
        }
}

function db_free_result($result)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_free_result($result);
                break ;
            case postgresql:
	        return @pgsql_db_free_result($result);
                break ;
        }
}

function db_fetch_assoc($result)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_fetch_assoc($result);
                break ;
            case postgresql:
	        return @pgsql_db_fetch_assoc($result);
                break ;
        }
	
}

function db_fetch_row($result)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_fetch_row($result);
                break ;
            case postgresql:
	        return @pgsql_db_fetch_row($result);
                break ;
        }	
}

function db_field_name($result, $field_offset)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_field_name($result, $field_offset);
                break ;
            case postgresql:
	        return @pgsql_db_field_name($result, $field_offset);
                break ;
        }	
}

function db_num_rows($result)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_num_rows($result);
                break ;
            case postgresql:
	        return @pgsql_db_num_rows($result);
                break ;
        }
}

function db_num_fields($result)
{
        global $_opendb_dbtype;
	switch ($_opendb_dbtype) 
        {
            case mysql:	
	        return @mysql_db_num_fields($result);
                break ;
            case postgresql:
	        return @pgsql_db_num_fields($result);
                break ;
        }
}

/**
 * Description: get table information
 */
function db_get_table_info($table)
{
	global $_opendb_dbtype ;
	switch ($_opendb_dbtype) 
	{
		case mysql:
			return @mysql_db_get_table_info($table);
			break ;
		case postgresql:
			return @pgsql_db_get_table_info($table);
			break ;
	}
}	
?>