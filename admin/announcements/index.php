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

	@author Doug Meyers <dgmyrs@users.sourceforge.net>
*/

if(!defined('OPENDB_ADMIN_TOOLS'))
{
	die('Admin tools not accessible directly');
}

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/user.php");
include_once("./lib/announcement.php");
include_once("./lib/datetime.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/listutils.php");

function get_edit_announcement_input_form($announcement_r, $HTTP_VARS=NULL)
{
	global $PHP_SELF;
				  
	$buffer .= "<form action=\"$PHP_SELF\" method=\"POST\">";
	
	$buffer .= "\n<input type=\"hidden\" name=\"type\" value=\"announcements\">";
	
	if(is_array($announcement_r))
	{
		$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"update\">".
			"\n<input type=\"hidden\" name=\"announcement_id\" value=\"".$announcement_r['announcement_id']."\">";
	}
	else
	{
	    $buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"insert\">";
	}
	
	$buffer .= "<table>";
		
	$buffer .= get_input_field("title",
				NULL, //s_attribute_type
				'Title',
				"text(50,500)", //input type
				"Y", //compulsory!
				ifempty($announcement_r['title'], $HTTP_VARS['title']),
				TRUE);

	$buffer .= get_input_field("content",
				NULL, //s_attribute_type
				'Announcement',
				"htmlarea(60,15)", //input type
				"Y", //compulsory!
				ifempty($announcement_r['content'], $HTTP_VARS['content']),
				TRUE);

	$buffer .= get_input_field("display_days",
				NULL, //s_attribute_type
				'Display Days',
				"number(10,10)", //input type
				"Y", //compulsory!
				ifempty($announcement_r['display_days'], $HTTP_VARS['display_days']),
				TRUE);

    if(is_array($announcement_r))
	{
		$buffer .= get_input_field("closed_ind",
			NULL, //s_attribute_type
			'Closed',
		 	"checkbox(Y,N)", //input type
		 	"N", //compulsory!
		 	ifempty($announcement_r['closed_ind'], $HTTP_VARS['closed_ind']),
		 	TRUE);
	}

	$buffer .= "</table>";
	
	$help_r[] = array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field'), id=>'compulsory');
    $help_r[] = array('text'=>'A zero in Display Days indicates the announcment will never expire.');
    $help_r[] = array('text'=>'No validation is performed on HTML entered in the Announcement text field.');

    $buffer .= format_help_block($help_r);

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";
					
	$buffer .= "<input type=\"button\" class=\"button\" onclick=\"$onclick_event\" value=\"Save\">";

	$buffer .= "\n</form>";

	return $buffer;
}

if(strlen($HTTP_VARS['op'])==0)
	$HTTP_VARS['op'] = 'list';

if($HTTP_VARS['op'] == 'update') //update an existing announcement
{
    if(strlen($HTTP_VARS['title'])>0 && strlen($HTTP_VARS['content'])>0 && is_numeric($HTTP_VARS['display_days']))
    {
		if(!update_announcement($HTTP_VARS['announcement_id'], $HTTP_VARS['title'], $HTTP_VARS['content'], $HTTP_VARS['display_days'], $HTTP_VARS['closed_ind']))
		{
			$errors[] = array('error'=>'Announcement not updated','detail'=>db_error());
		}
		$HTTP_VARS['op'] = 'list';
	}
	else
	{
	    $errors[] = array('error'=>'Title, Content and Display Days are required');
	    $HTTP_VARS['op'] = 'edit';
	}
}
else if($HTTP_VARS['op'] == 'insert') //insert new announcement
{
    if(strlen($HTTP_VARS['title'])>0 && strlen($HTTP_VARS['content'])>0 && is_numeric($HTTP_VARS['display_days']))
    {
		if(!insert_announcement($HTTP_VARS['title'], $HTTP_VARS['content'], $HTTP_VARS['display_days']))
		{
			$errors[] = array('error'=>'Announcement not added','detail'=>db_error());
		}
		$HTTP_VARS['op'] = 'list';
	}
	else
	{
	    $errors[] = array('error'=>'Title, Content and Display Days are required');
	    $HTTP_VARS['op'] = 'new';
	}
}
else if($HTTP_VARS['op'] == 'delete') //delete an existing announcement
{
	if($HTTP_VARS['confirmed'] == 'false')
	{
		$HTTP_VARS['op'] = 'list';
	}
	else if($HTTP_VARS['confirmed'] == 'true')
	{
		if(!delete_announcement($HTTP_VARS['announcement_id']))
		{
			$errors[] = array('error'=>'Announcement not deleted','detail'=>db_error());
		}
		$HTTP_VARS['op'] = 'list';
	}
	else
	{
		echo("<h3>Delete Announcement</h3>");
		echo get_op_confirm_form($PHP_SELF, 'Are you sure you want to permanently delete announcement "'.fetch_announcement_title($HTTP_VARS['announcement_id']).'"?', $HTTP_VARS);
	}
}
	
if($HTTP_VARS['op'] == 'list')
{
	echo("<p>[<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new\">New Announcement</a>]</p>");
		
	if(is_not_empty_array($errors))
		echo format_error_block($errors);
			
	$result = fetch_announcement_rs(); 
	if($result)
	{	
	    $submitted_datetime_mask = get_opendb_config_var('welcome.announcements', 'datetime_mask');
	    echo("<table>");
		echo("<tr class=\"navbar\">"
		."<th>Title</th>"
		."<th>Content</th>"
		."<th>Submitted</th>"
		."<th>Display Days</th>"
		."<th>Closed</th>"
		."<th></th>"
		."</tr>");
		
		while ($announcement_r = db_fetch_assoc($result))
		{
			echo("<tr>");

			echo("\n<td class=\"data\">".$announcement_r['title']."");
			echo("\n<td class=\"data\">".nl2br($announcement_r['content'])."</td>");
				
			echo("<td class=\"data\">".get_localised_timestamp($submitted_datetime_mask, $announcement_r['submit_on']).'</td>'.
				"<td class=\"data\">".$announcement_r['display_days'].'</td>'.
				"<td class=\"data\">".$announcement_r['closed_ind'].'</td>');

			echo("<td class=\"data\"><a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&announcement_id=".$announcement_r['sequence_number']."\">Edit</a>");
			echo(" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&announcement_id=".$announcement_r['sequence_number']."\">Delete</a></td>");
				
			echo("</tr>");
		}
		db_free_result($result);
		echo("</table>");
	} //if($result)
	else
	{
		echo("\n<p class=\"error\">No Announcements Found</p>");
	}
}
else if($HTTP_VARS['op'] == 'new') //display new announcement form.
{
	echo("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list\">Back to Main</a>]</p>");
            
	echo("<h3>New Announcement</h3>");
			
	if(is_not_empty_array($errors))
		echo format_error_block($errors);
			
	echo(get_edit_announcement_input_form(NULL, $HTTP_VARS));
}
else if($HTTP_VARS['op'] == 'edit') //Display edit announcement form.
{
	echo("<p>[<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=list\">Back to Main</a>]</p>");
           
	echo("<h3>Edit Announcement</h3>");
			
	if(is_not_empty_array($errors))
		echo format_error_block($errors);
				
	$announcement_r = fetch_announcement_r($HTTP_VARS['announcement_id']);
	echo(get_edit_announcement_input_form($announcement_r, $HTTP_VARS));
}
?>