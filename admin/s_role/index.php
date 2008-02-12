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

if(!defined('OPENDB_ADMIN_TOOLS'))
{
	die('Admin tools not accessible directly');
}

function display_role_permissions_editor($HTTP_VARS)
{
	global $ADMIN_TYPE;
	global $PHP_SELF;

	$results = fetch_role_permission_rs($HTTP_VARS['role_name']);
	if($results)
	{
		while($permission_r = db_fetch_assoc($results))
		{
			if(strlen($permission_r['role_name'])>0)
				$exists_rs[] = $permission_r;
			else
				$not_exists_rs[] = $permission_r;
		}
		db_free_result($results);
	}
	
	echo('<script src="./admin/select.js" language="JavaScript" type="text/javascript"></script>');
	
	echo("\n<form name=\"edit_role_permissions\" action=\"$PHP_SELF\" method=\"POST\">");
	echo("\n<input type=\"hidden\" name=\"op\" value=\"".$HTTP_VARS['op']."\">");
	echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");
	echo("\n<input type=\"hidden\" name=\"role_name\" value=\"".$HTTP_VARS['role_name']."\">");
	
	echo("<table>");
	echo("<tr class=\"navbar\">
	<th>Exclude</th>
	<th></th>
	<th>Include</th>
	</tr>");
	
	echo("<tr><td><select name=\"excluded_permissions\" size=\"15\" MULTIPLE>");
	echo("<option value=\"\" onClick=\"this.selected=false;\">-----------------------------------------\n");
	while(list(,$permission_r) = @each($not_exists_rs))
	{
		echo("<option value=\"".$permission_r['permission_name']."\">".$permission_r['description']."\n");
	}
	echo("</select></td>");
	
	echo("<td>");
	echo("<input type=\"button\" class=\"button\" value=\">\" onClick=\"moveOptions(this.form, 's_role_permission', this.form['excluded_permissions'], this.form['included_permissions']);\">".
		"<input type=\"button\" class=\"button\" value=\">>\" onClick=\"moveAllOptions(this.form, 's_role_permission', this.form['excluded_permissions'], this.form['included_permissions']);\">");
		
	echo("<input type=\"button\" class=\"button\" value=\"<\" onClick=\"moveOptions(this.form, 's_role_permission', this.form['included_permissions'], this.form['excluded_permissions']);\">".
		"<input type=\"button\" class=\"button\" value=\"<<\" onClick=\"moveAllOptions(this.form, 's_role_permission', this.form['included_permissions'], this.form['excluded_permissions']);\">");
		
	echo("</td>");
		
	echo("<td><select name=\"included_permissions\" size=\"15\" MULTIPLE>");
	echo("<option value=\"\" onClick=\"this.selected=false;\">-----------------------------------------\n");
	while(list(,$permission_r) = @each($exists_rs))
	{
		echo("<option value=\"".$permission_r['permission_name']."\">".$permission_r['description']."\n");
	}
	echo("</select></td>");
	echo("</table>");
	
	@reset($not_exists_rs);
	while(list(,$permission_r) = @each($not_exists_rs))
	{
		echo("\n<input type=\"hidden\" name=\"s_role_permission[".$permission_r['permission_name']."]\" value=\"exclude\">");
	}
	
	@reset($exists_rs);
	while(list(,$permission_r) = @each($exists_rs))
	{
		echo("\n<input type=\"hidden\" name=\"s_role_permission[".$permission_r['permission_name']."]\" value=\"include\">");
	}
	
	echo("<input type=\"button\" class=\"button\" value=\"Refresh\" onclick=\"this.form['op'].value='edit'; this.form.submit();\">");
	echo("\n<input type=\"button\" class=\"button\" value=\"Update\" onclick=\"this.form['op'].value='update'; this.form.submit();\">");

	echo("</form>");
}

if($HTTP_VARS['op'] == 'update')
{
	$included_permissions_r = array();
	while(list($permission_name,$value) = each($HTTP_VARS['s_role_permission'])) 
	{
		if($value == 'include')
		{
			$included_permissions_r[] = $permission_name;
		}
	}
	
	update_role_permissions($HTTP_VARS['role_name'], $included_permissions_r);
	
	$HTTP_VARS['op'] = 'edit';
}

if($HTTP_VARS['op'] == 'edit')
{
	echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main List</a>]</div>");
	
	echo("\n<h3>Edit ${HTTP_VARS['role_name']} Role Permissions</h3>");
	
	if(is_not_empty_array($errors))
		echo format_error_block($errors);
	
	
	display_role_permissions_editor($HTTP_VARS);
}

else if($HTTP_VARS['op'] == '')
{
	if(is_not_empty_array($errors))
		echo format_error_block($errors);

	// list languages and options
	$results = fetch_role_rs();
	if($results)
	{
		echo("<table><tr class=\"navbar\">
			<th>Role</th>
			<th>Description</th>
			<th>Signup?</th>
			<th>&nbsp;</th>
			</tr>");
			
		while($role_r = db_fetch_assoc($results))
		{
			echo("<tr>
				<td class=\"data\">".$role_r['role_name']."</td>
				<td class=\"data\">".$role_r['description']."</td>
				<td class=\"data\">".$role_r['signup_avail_ind']."</td>
				<td class=\"data\">[ <a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=edit&role_name=${role_r['role_name']}\">Edit</a> ]");
		}
		echo("</table>");
		
		db_free_result($results);
	}
}
?>