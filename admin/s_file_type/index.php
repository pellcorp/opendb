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
include_once('./functions/file_type.php');

function display_s_file_type_row($file_type_r, $row)
{
	global $PHP_SELF;
	global $ADMIN_TYPE;
	
	echo("\n<tr>");
	
	if(is_not_empty_array($file_type_r))
	{
		echo("\n<td align=\"center\" class=\"data\">".get_input_field("content_type[$row]", NULL, NULL, "readonly", "N", $file_type_r['content_type'], FALSE).
			"<input type=hidden name=\"exists_ind[$row]\" value=\"Y\">".
			"</td>");
	}
	else
	{
		echo("\n<td align=\"center\" class=\"data\">".get_input_field("content_type[$row]", NULL, NULL, "text(20,100)", "Y", $file_type_r['content_type'], FALSE).
			"<input type=hidden name=\"exists_ind[$row]\" value=\"N\">".
			"</td>");
	}
	
	$groups = array();
	$results = fetch_s_file_type_content_group_rs();
	if($results)
	{
		while($content_group_r = db_fetch_assoc($results))
		{
			$groups[] = $content_group_r['content_group'];
		}
	}
	
	echo("<td class=\"data\">".custom_select("content_group[$row]", $groups, "%value%", 1, $file_type_r['content_group'], "value")."</td>");

	//description
	echo("\n<td class=\"data\">".get_input_field("description[$row]", NULL, NULL, "text(20,255)", "N", $file_type_r['description'], FALSE)."</td>");
	
	echo("\n<td align=\"center\" class=\"data\">".get_input_field("extension[$row]", NULL, "Extension", "text(10,10)", "Y",  $file_type_r['extension'], FALSE)."</td>");
	
	$alt_extensions = '';
	if(is_not_empty_array($file_type_r))
	{
		$alt_extensions_r = fetch_s_file_type_alt_extension_r($file_type_r['content_type']);
		if(is_array($alt_extensions_r))
		{
			$alt_extensions = implode(', ', $alt_extensions_r);
		}
	}
	
	// convert array of extensiosn to a string
	echo("\n<td class=\"data\">".get_input_field("alt_extensions[$row]", NULL, "Alternate Extensions", "text(20,255)", "N", $alt_extensions, FALSE)."</td>");
	
	echo("<td class=\"data\" align=center>");
	// Get the theme specific source of the image.
	if(strlen($file_type_r['image'])>0)
	{
		$src = _theme_image_src($file_type_r['image']);
	}
	if($src!==FALSE && strlen($src)>0)
		echo("<img src=\"$src\">");
	else
		echo("&nbsp;");
	echo("</td>");

	echo("\n<td class=\"data\">".get_input_field("image[$row]", NULL, NULL, "url(15,*,\"gif,jpg,png\",N)", "N", $file_type_r['image'], FALSE)."</td>");
	
	echo ("\n<td class=\"data\" align=\"middle\">".get_input_field("thumbnail_support_ind[$row]", NULL, NULL, "simple_checkbox(".(strtoupper($file_type_r['thumbnail_support_ind'])== "Y"?"CHECKED":"").")", "N", "Y", FALSE)."</td>");
	
    echo("\n<td class=\"data\">");
   	if(is_not_empty_array($file_type_r))
		echo("<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&content_type=".$file_type_r['content_type']."\">Delete</a>");
	else
		echo("&nbsp;");
	echo("\n</td>");

 	echo("</tr>");
}
// #################################################################################
// Main Process
// #################################################################################
if(is_opendb_valid_session())
{
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		if($HTTP_VARS['op'] == 'delete')
		{
			if($HTTP_VARS['confirmed'] == 'true')
			{
				if(!delete_s_file_type($HTTP_VARS['content_type']))
					$errors[] = array('error'=>'File Type not deleted','detail'=>db_error());

                $HTTP_VARS['op'] = NULL;
			}
			else if($HTTP_VARS['confirmed'] != 'false')
			{
				echo("\n<h3>Delete File type</h3>");
				echo(get_op_confirm_form($PHP_SELF, 
						"Are you sure you want to delete file type '".$HTTP_VARS['content_type']."'?", 
						array('type'=>$HTTP_VARS['type'], 'op'=>'delete', 'content_type'=>$HTTP_VARS['content_type'])));
			}
			else // confirmation required.
			{
				$HTTP_VARS['op'] = NULL;
			}
		}
		else if($HTTP_VARS['op'] == 'update')
		{
			if(is_not_empty_array($HTTP_VARS['content_type']))
			{
				for($i=0; $i<count($HTTP_VARS['content_type']); $i++)
				{
					if($HTTP_VARS['exists_ind'][$i] == 'Y')
					{
						if(is_exists_file_type($HTTP_VARS['content_type'][$i]))
						{
							if(!update_s_file_type(
								$HTTP_VARS['content_type'][$i],
								$HTTP_VARS['content_group'][$i],
								$HTTP_VARS['extension'][$i], 
								trim_explode(',', $HTTP_VARS['alt_extensions'][$i]),
								$HTTP_VARS['description'][$i],  
								$HTTP_VARS['image'][$i],
								$HTTP_VARS['thumbnail_support_ind'][$i]))
							{
								$errors[] = array('error'=>'File Type "'.$HTTP_VARS['content_type'][$i].'" not updated.','detail'=>db_error());
							}
						}
					}
					else if(strlen($HTTP_VARS['content_type'][$i])>0)
					{
						if(!is_exists_file_type($HTTP_VARS['content_type'][$i]))
						{
							if(!insert_s_file_type(
								$HTTP_VARS['content_type'][$i],
								$HTTP_VARS['content_group'][$i],
								$HTTP_VARS['extension'][$i], 
								trim_explode(',', $HTTP_VARS['alt_extensions'][$i]), 
								$HTTP_VARS['description'][$i], 
								$HTTP_VARS['image'][$i],
								$HTTP_VARS['thumbnail_support_ind'][$i]))
							{
								$errors[] = array('error'=>'File Type "'.$HTTP_VARS['content_type'][$i].'" not inserted.','detail'=>db_error());
							}
						}	
					}
				}
			}
			
			$HTTP_VARS['op'] = '';
		}
		
		if(strlen($HTTP_VARS['op'])==0)
		{
            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo("\n<form name=\"s_file_type\" action=\"$PHP_SELF\" method=\"POST\">");

			echo("\n<input type=\"hidden\" name=\"op\" value=\"\">");
			echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");

			echo("\n<table>");
			echo("<tr class=\"navbar\">"
				."<th>Content Type</th>"
				."<th>Content Group</th>"
				."<th>Description</th>"
				."<th>Extension</th>"
				."<th>Alternate Extensions</th>"
                ."<th colspan=2>Image</th>"
                ."<th>Thumbnail<br />Support</th>"
				."<th></th>"
				."</tr>");
			$column_count = 9;

			$results = fetch_s_file_type_rs();
			if($results)
			{
				$row = 0;
				while($file_type_r = db_fetch_assoc($results))
				{
					display_s_file_type_row($file_type_r, $row);
					$row++;
				}
				db_free_result($results);
			}

			if(is_numeric($HTTP_VARS['blank_rows']))
				$blank_rows = (int)$HTTP_VARS['blank_rows'];
			else
				$blank_rows = 5;

			for($i=$row; $i<$row+$blank_rows; $i++)
			{
				echo display_s_file_type_row(array(), $i);
			}

			echo("</tr></table>");
			
			echo(get_input_field("blank_rows", NULL, NULL, "value_select(\"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20\",1)",  
								"N", $blank_rows, FALSE, NULL, "this.form.submit();"));
			
			echo("<input type=button value=\"Refresh\" onclick=\"this.form['op'].value=''; this.form.submit();\">".
				"<input type=button value=\"Update\" onclick=\"this.form['op'].value='update'; this.form.submit();\">");

			echo("</form>");
		}
	}
}
?>
