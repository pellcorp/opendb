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

include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/site_plugin.php");

function is_exists_any_item_type()
{
	$query = "SELECT 'x' FROM s_item_type";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		db_free_result($result);
		return TRUE;
	}
	return FALSE;
}

function is_exists_item_type($s_item_type)
{
	if(strlen($s_item_type)>0)
	{
		$query = "SELECT 'x' FROM s_item_type WHERE s_item_type = '".$s_item_type."'";
		$result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			db_free_result($result);
			return TRUE;
		}
	}
	//else
	return FALSE;
}

/*
* In order to be considered a valid s_item_type structure, several 
* s_item_attribute_type records must exist with specific s_field_type's 
* underneath it.
* 
* We are not checking that TITLE s_field_type s_item_attribute_type
* mandatory_ind is set to 'Y', because item_input.php will enforce
* that itself.  All we care about is that the item_type is actually
* structured well enough that it will work within item_input.php to
* add new records.
*/
function is_valid_item_type_structure($s_item_type)
{
	if(is_exists_item_type($s_item_type))
	{
		if(fetch_sfieldtype_item_attribute_type($s_item_type, 'TITLE'))
		{
			if(fetch_sfieldtype_item_attribute_type($s_item_type, 'STATUSTYPE'))
			{
				if(fetch_sfieldtype_item_attribute_type($s_item_type, 'STATUSCMNT'))
				{
					if(fetch_sfieldtype_item_attribute_type($s_item_type, 'CATEGORY'))
					{
						if(get_opendb_config_var('borrow', 'enable')!==FALSE && get_opendb_config_var('borrow', 'duration_support')!==FALSE)
						{
							if(fetch_sfieldtype_item_attribute_type($s_item_type, 'DURATION'))
							{
								// At this point all the required s_field_type mappings have been provided.
								return TRUE;
							}
						}
						else //No borrow duration functionality enabled.
						{
							// At this point $borrow functionality is not enabled, so we do not
							// have to do anymore testing.
							return TRUE;
						}
					}
				}
			}
		}
	}
	
	//else
	return FALSE;
}

function is_instance_item_attribute_type($s_item_type, $s_attribute_type)
{
	$query = "SELECT instance_attribute_ind FROM s_item_attribute_type WHERE s_item_type = '$s_item_type' AND s_attribute_type = '$s_attribute_type'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
	   	$found = db_fetch_assoc($result);
		db_free_result($result);
		if($found['instance_attribute_ind'] == 'Y')
		    return TRUE;
		else
		    return FALSE;
	}

	//else
	return FALSE;
}

/**
  @param is_loookup.  Indicates we are using this as a lookup result, so the s_item_type
  needs to be named "value" and the description needs to be named "display"
*/
function fetch_item_type_rs($is_lookup = FALSE)
{
	$query = "SELECT s_item_type ".($is_lookup?"as value":"").", ifnull(stlv.value, description) as ".($is_lookup?"display":"description").", image  
			FROM s_item_type 
			LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_item_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = s_item_type 
			ORDER BY order_no, s_item_type";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_item_type_for_item_types_rs($item_type_r, $is_lookup = FALSE)
{
	$query = "SELECT s_item_type".($is_lookup?" as value":"").", ifnull(stlv.value, description) ".($is_lookup?"display":"description")." 
			FROM s_item_type 
			LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_item_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = s_item_type 
			WHERE s_item_type IN (".format_sql_in_clause($item_type_r).") 
			ORDER BY order_no, s_item_type";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

//
// Return an item_record.  Currently this is:description, image
//
function fetch_item_type_r($s_item_type)
{
	$query = "SELECT ifnull(stlv.value, description) as description, image 
	FROM s_item_type
	LEFT JOIN s_table_language_var stlv
			ON stlv.language = '".get_opendb_site_language()."' AND
			stlv.tablename = 's_item_type' AND
			stlv.columnname = 'description' AND
			stlv.key1 = s_item_type 
	WHERE s_item_type = '$s_item_type'";
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}
	else
		return FALSE;
}

function fetch_site_item_type_r($site_type)
{
	$query = "SELECT DISTINCT sit.s_item_type
			FROM 	s_item_type sit,
					s_attribute_type sat,
					s_item_attribute_type siat
			WHERE 	sit.s_item_type = siat.s_item_type AND 
					sat.s_attribute_type = siat.s_attribute_type AND 
					sat.site_type = '".$site_type."'";

	$results = db_query($query);
	if($results && db_num_rows($results)>0)
	{
		$item_type_r = NULL;
		while($site_plugin_r = db_fetch_assoc($results))
		{
			$item_type_r[] = $site_plugin_r['s_item_type'];
		}
		db_free_result($results);
		
		return $item_type_r;
	}

	//else
	return FALSE;
}

/*
	Will return an array of all site-plugins that are compatible with the s_item_type.
*/
function fetch_site_type_rs($s_item_type)
{
	$query = 	"SELECT	DISTINCT sat.site_type ".
				"FROM	s_attribute_type sat,".
						"s_item_attribute_type siat ".
				"WHERE 	sat.s_attribute_type = siat.s_attribute_type AND ".
                		"length(sat.site_type)>0 AND ".
						"siat.s_item_type = '".$s_item_type."' ".
						"order by 1";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
    {
		while($site_type_r = db_fetch_assoc($result))
			$site_type[] = $site_type_r['site_type'];

        return $site_type;
	}        
	else
		return FALSE;
}

/*
	Checks if an s_item_type has any site plugins linked to it.
*/
function is_item_legal_site_type($s_item_type)
{
	$query = 	"select	sat.site_type ".
				"from 	s_attribute_type sat,".
				"		s_item_attribute_type siat ".
				"where 	sat.s_attribute_type = siat.s_attribute_type AND ".
                		"length(sat.site_type)>0 AND ".
						"siat.s_item_type = '".$s_item_type."'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		while($site_type_r = db_fetch_assoc($result))
		{
			if(is_exists_site_plugin($site_type_r['site_type']))
			{
				db_free_result($result);
				return TRUE;
			}
		}
		db_free_result($result);
	}

	//else
	return FALSE;
}
?>