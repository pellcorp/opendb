<?php
/* 	
 	OpenDb Media Collector Database
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
include_once("./functions/database.php");
include_once("./functions/logging.php");
include_once("./functions/status_type.php");

/*
* Fetch a complete list of item records, which have at least one
* item instance, for the specified owner_id.
*/
function fetch_export_item_rs($s_item_type, $owner_id)
{
	$query = "SELECT DISTINCT i.id as item_id, i.title, i.s_item_type ".
			"FROM user u, item i, item_instance ii, s_status_type sst ".
			"WHERE u.user_id = ii.owner_id AND sst.s_status_type = ii.s_status_type AND i.id = ii.item_id ";

	if(strlen($owner_id)>0)
		$query .= "AND ii.owner_id = '$owner_id' ";
	
	// can only export items for active users.
	$query .= "AND u.active_ind = 'Y' ";
	
	// Restrict certain status types, to specified user types.
	$user_type_r = get_min_user_type_r(get_opendb_session_var('user_type'));
	if(is_not_empty_array($user_type_r))
	{
		$query .= "AND ( ii.owner_id = '".get_opendb_session_var('user_id')."' OR ".
				" LENGTH(IFNULL(sst.min_display_user_type,'')) = 0 OR ".
				" sst.min_display_user_type IN(".format_sql_in_clause($user_type_r).") ) ";
	}
		
	if(strlen($s_item_type)>0)
		$query .= "AND i.s_item_type = '$s_item_type'";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
    	return $result;
	else
		return FALSE;	
}

/*
* Return a list of records owned by the specified $owner_id 
* AND visible to the current user.
*/
function fetch_export_item_instance_rs($s_item_type, $owner_id)
{
	$query = "SELECT i.id as item_id, ii.instance_no, i.title, i.s_item_type, ii.owner_id, ii.borrow_duration, ii.s_status_type, ii.status_comment ".
			"FROM user u, item i, item_instance ii, s_status_type sst ".
			"WHERE u.user_id = ii.owner_id AND sst.s_status_type = ii.s_status_type AND i.id = ii.item_id ";

	if(strlen($s_item_type)>0)
		$query .= "AND i.s_item_type = '$s_item_type'";
	
	// can only export items for active users.
	$query .= "AND u.active_ind = 'Y' ";
	
	// Restrict certain status types, to specified user types.
	$user_type_r = get_min_user_type_r(get_opendb_session_var('user_type'));
	if(is_not_empty_array($user_type_r))
	{
		$query .= " AND ( ii.owner_id = '".get_opendb_session_var('user_id')."' OR ".
				" LENGTH(IFNULL(sst.min_display_user_type,'')) = 0 OR ".
				" sst.min_display_user_type IN(".format_sql_in_clause($user_type_r).") ) ";
	}
	
	if(strlen($owner_id)>0)
		$query .= " AND ii.owner_id = '$owner_id' ";

	$query .= "ORDER by i.id, ii.instance_no";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
    	return $result;
	else
		return FALSE;
}

function is_export_plugin($plugin)
{
	if(strlen($plugin)>0 && file_exists('./export/'.$plugin.'.class.php'))
		return TRUE;
	else
		return FALSE;
}

/**
	Generate a list of export plugins
	
	Returns an array of the following format:
*/
function get_export_r()
{
	$handle=opendir('./export');
	while ($file = readdir($handle))
    {
		// Ensure valid plugin name.
		if ( !preg_match("/^\./",$file) && preg_match("/(.*).class.php$/",$file,$regs))
		{
			$export[] = $regs[1];
		}
	}
	closedir($handle);
    
    if(is_array($export) && count($export)>0)
		return $export;
	else // empty array as last resort.
		return array();
}

function &get_export_plugin($pluginName) {
	if(is_export_plugin($pluginName)) {
		include_once("./export/".$pluginName.".class.php");
		$exportPlugin = new $pluginName();
		return $exportPlugin;
	} else {
		return NULL;
	}
}

function get_export_plugin_list_r() {
	$pluginList = NULL;
	
	$export_type_r = get_export_r();
	if(is_array($export_type_r))
	{
		while(list(,$pluginRef) = @each($export_type_r))
		{
			include_once("./export/".$pluginRef.".class.php");
			$exportPlugin = new $pluginRef();
			if($exportPlugin !== NULL)
			{
				if(strcasecmp($pluginRef, get_class($exportPlugin)) === 0)
				{
					$pluginList[] = array(name=>$pluginRef, description=>$exportPlugin->get_display_name());
				}
				else
				{
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Export class is not valid', array($pluginRef));
				}
			}
		}
	}
	
	return $pluginList;
}
?>
