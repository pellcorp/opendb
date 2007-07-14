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

include_once('./functions/config.php');
include_once("./functions/item_type.php");
include_once("./functions/item_type_group.php");
include_once("./admin/s_item_type/functions.php");

session_start();
if(is_opendb_valid_session())
{
    if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
	    if($HTTP_VARS['op'] == 'update')
	    {
            if(($HTTP_VARS['s_item_type_group'] == '*' || is_exists_item_type_group($HTTP_VARS['s_item_type_group'])) &&
					($HTTP_VARS['s_item_type'] == '*' || is_exists_item_type($HTTP_VARS['s_item_type'])))
			{
            	$results = fetch_s_title_display_mask_rs();
				if($results)
				{
					while($title_mask_r = db_fetch_assoc($results))
					{
						$display_mask = NULL;
						if(is_array($HTTP_VARS['display_mask']) && isset($HTTP_VARS['display_mask'][$title_mask_r['id']]))
                            $display_mask = $HTTP_VARS['display_mask'][$title_mask_r['id']];

						if(strlen($display_mask)>0)
						{
                            if(is_exists_s_title_display_mask_item($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type']))
							{
                                if(!update_s_title_display_mask_item($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type'], $display_mask))
									$errors[] = array('error'=>'Title Display Mask Item not updated','detail'=>db_error());
							}
							else
							{
                                if(!insert_s_title_display_mask_item($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type'], $display_mask))
									$errors[] = array('error'=>'Title Display Mask Item not inserted','detail'=>db_error());
							}
						}
						else
						{
                            if(is_exists_s_title_display_mask_item($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type']))
							{
                            	if(!delete_s_title_display_mask_item($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type']))
									$errors[] = array('error'=>'Title Display Mask Item not deleted','detail'=>db_error());
							}
						}
					}
                    db_free_result($results);
				}

                $HTTP_VARS['op'] = 'edit';
			}
			else
			{
                $HTTP_VARS['op'] = '';
			}
	    }
	    
	    if($HTTP_VARS['op'] == 'edit')
	    {
	        if(($HTTP_VARS['s_item_type_group'] == '*' || is_exists_item_type_group($HTTP_VARS['s_item_type_group'])) &&
					($HTTP_VARS['s_item_type'] == '*' || is_exists_item_type($HTTP_VARS['s_item_type'])))
			{
				echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

				if($HTTP_VARS['s_item_type_group'] != '*')
					echo("\n<h3>Edit Item Type Group ".$HTTP_VARS['s_item_type_group']." Title Display Masks</h3>");
				else if($HTTP_VARS['s_item_type'] != '*')
                    echo("\n<h3>Edit Item Type ".$HTTP_VARS['s_item_type']." Title Display Masks</h3>");
				else
                    echo("\n<h3>Edit Default Title Display Masks</h3>");

				if(is_not_empty_array($errors))
					echo format_error_block($errors);

                
				echo("\n<form name=\"s_title_display_mask\" action=\"$PHP_SELF\" method=\"POST\">");
				echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");
                echo("\n<input type=\"hidden\" name=\"op\" value=\"update\">");
                echo("\n<input type=\"hidden\" name=\"s_item_type_group\" value=\"".$HTTP_VARS['s_item_type_group']."\">");
                echo("\n<input type=\"hidden\" name=\"s_item_type\" value=\"".$HTTP_VARS['s_item_type']."\">");

                echo("<table>");
                echo("<tr class=\"navbar\">"
				."<th>Type</th>"
				."<th>Title Display Mask</th>"
				."</tr>");

	        	$results = fetch_s_title_display_mask_rs();
	        	if($results)
				{
					while($title_mask_r = db_fetch_assoc($results))
					{
                        echo("<tr><td class=\"data\" align=center nowrap>".$title_mask_r['description']."</td>");
                        $title_mask_items_r = fetch_title_mask_items_r($title_mask_r['id'], $HTTP_VARS['s_item_type_group'], $HTTP_VARS['s_item_type']);
                        echo("<td class=\"data\" align=center><textarea WRAP=OFF cols=125 rows=3 name=\"display_mask[".$title_mask_r['id']."]\">".htmlspecialchars($title_mask_items_r['display_mask'])."</textarea></td>");
						echo("</tr>");
					}
					db_free_result($results);
				}

			    echo("\n<tr><td colspan=2>");
				echo("\n<input type=button onclick=\"this.form.op.value='update'; this.form.submit();\" value=\"Update\">");
				echo("\n</td></tr>");

				echo("</form>");
				echo("</table>");
			}
			else
			{
			    // error
			}
	    }

		if($HTTP_VARS['op'] == '')
	    {
            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo("<form name=\"navigate\" action=\"$PHP_SELF\" method=\"GET\">".
				"<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">".
				"<input type=\"hidden\" name=\"op\" value=\"\">".
				"<input type=\"hidden\" name=\"s_item_type_group\" value=\"\">".
				"<input type=\"hidden\" name=\"s_item_type\" value=\"\">".
				"</form>");

			echo("<table cellspacing=2 border=0 width=200>");
			echo("\n<form name=\"s_title_display_mask\" action=\"$PHP_SELF\" method=\"POST\">");
			echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");

			echo("<tr class=\"navbar\"><th colspan=2>Item Type Groups</th></tr>");
					
            $results = fetch_item_type_group_rs();
			if($results)
			{
				while($item_type_group_r = db_fetch_assoc($results))
				{
				    echo("\n<tr><td class=\"data\" align=center>".$item_type_group_r['s_item_type_group']."</td>");
				    echo("\n<td class=\"data\" align=center>");
					echo("\n<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_item_type_group=".$item_type_group_r['s_item_type_group']."&s_item_type=*\">Edit</a>");
					echo("\n</td></tr>");
				}
				db_free_result($results);
			}
	
	        echo("<tr class=\"navbar\"><th colspan=2>Item Types</th></tr>");

			$results = fetch_s_item_type_rs('s_item_type');
			if($results)
			{
				while($item_type_r = db_fetch_assoc($results))
				{
					echo("\n<tr><td class=\"data\" align=center>".$item_type_r['s_item_type']."</td>");
				    echo("\n<td class=\"data\" align=center>");
					echo("\n<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_item_type_group=*&s_item_type=".$item_type_r['s_item_type']."\">Edit</a>");
					echo("\n</td></tr>");
				}
				db_free_result($results);
			}
			
			echo("\n<tr><td class=\"data\" align=center><strong>Default</strong></td>");
			echo("\n<td class=\"data\" align=center>");
			echo("\n<a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_item_type_group=*&s_item_type=*\">Edit</a>");
			echo("\n</td></tr>");
		
			echo("</form>");
			echo("</table>");
	    }
	}
}//if(is_opendb_valid_session())
?>
