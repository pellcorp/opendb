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

include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/utils.php");

/*
 * function: db_session_open()
 * 
 * Does nothing.
 */
function db_session_open($session_path, $session_name)
{
	return TRUE;
}

/*
 * function: db_session_close()
 */
function db_session_close()
{
	return TRUE;
}

/*
 * function: db_session_read()
 * 
 * Reads the session data from the database
 */
function db_session_read($SID)
{
	$query = "SELECT value FROM php_session ".
			" WHERE SID = '$SID' AND ".
			" expiration > ". time();

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);

		return $found['value'];
	}
	else
	{
		return '';
	}
}

/*
 * function: db_session_write()
 * 
 * This function writes the session data to the database. If that SID 
 * already exists, then the existing data will be updated.
 */
function db_session_write($SID, $value)
{
	// hack - it seems that the db gets closed before the session info is written
	if(!db_ping())
	{
		init_db_connection();
	}
	
	$expiration = time() + get_cfg_var('session.gc_maxlifetime');
	
	$query = "INSERT INTO php_session (SID, expiration, value)".
			" VALUES('$SID', '$expiration', '$value')";

	$result = db_query($query);
	if($result!==FALSE && db_affected_rows() > 0)
	{
		return TRUE;
	}
	else //if (! $result)
	{
		$query = "UPDATE php_session ".
			" SET expiration = '$expiration',".
			" value = '$value' WHERE ".
			" SID = '$SID' "; //AND expiration >". time();

		$result = db_query($query);
		if($result && db_affected_rows() > 0)
			return TRUE;
		else
			return FALSE;
	}
}

/*
 * function: db_session_destroy()
 * 
 * Deletes all session information having input SID (only one row)
 */
function db_session_destroy($SID)
{
	// hack - it seems that the db gets closed before the session info is written
	if(!db_ping())
	{
		init_db_connection();
	}
	
	$query = "DELETE FROM php_session ".
      " WHERE SID = '$SID'";

	$result = db_query($query);
	if($result && db_affected_rows() > 0)
		return TRUE;
	else
		return FALSE;
}

/*
 * function: db_session_gc()
 * 
 * Deletes all sessions that have expired.
*/
function db_session_gc($maxlifetime)
{
	// hack - it seems that the db gets closed before the session info is written
	if(!db_ping())
	{
		init_db_connection();
	}
	
	$query = "DELETE FROM php_session ".
			" WHERE expiration < ".time() - $maxlifetime;
			
	$result = db_query($query);
	if($result && db_affected_rows() > 0)
		return TRUE;
	else
		return FALSE;
}
?>
