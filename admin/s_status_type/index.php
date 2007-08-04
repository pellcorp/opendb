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

include_once("./functions/user.php");
include_once("./functions/status_type.php");
include_once("./functions/install.php");

$_COLUMN_DESC = array(
			's_status_type'=>'Status Type',
			'description'=>'Description',
			'img'=>'Image',
			'insert_ind'=>'Insert',
			'update_ind'=>'Update',
			'delete_ind'=>'Delete',
			'change_owner_ind'=>'Change Owner',
			'min_display_user_type'=>'Minimum Display User',
			'min_create_user_type'=>'Minimum Create User',
			'borrow_ind'=>'Borrow',
			'status_comment_ind'=>'Status Comment',
			'default_ind'=>'Default',
			'closed_ind'=>'Closed');

$_COLUMN_HELP = array(
	'insert_ind'=>array(
		'If \'Y\', items can be directly inserted, otherwise items can '.
		'only be updated to this type (As long as update_ind = \'Y\')'),

	'update_ind'=>array(
		'If \'Y\', existing items can be updated to this type. If \'N\', then items can only '.
		'be directly inserted. (As long as insert_ind = \'Y\')'),

	'delete_ind'=>array(
		'Item instances of this type can be deleted.'),

	'change_owner_ind'=>array(
		'Item instances of this type can have their owner changed.'),

	'min_display_user_type'=>array(
		'Specifies the minimum user type, who can see list/display items of this '.
			'status.  For instance, if this column is set to \'A\', then only Admininstrator\'s '.
			'can list/display items of this status.  Items of this type owned by the user will be '.
			'visible to the user, and the user can still create items of this type, (Pursuant to any '.
			'restrictions enforced by min_create_user_type)',

		'User Types in OpenDb are hierarchical:',
		array(
			'G - Guest (Lowest)',
			'B - Borrower',
			'N - Normal',
			'A - Admin (Highest)'),

		'If a reservation is attempted for an item with a status_type, that has a '.
			'min_display_user_type set, which does not fit in with that of the current '.
			'user, the reservation will be aborted.',

		'If user is not a valid min_display_user_type or min_create_user_type user type the status will not '.
			'be displayed in Stats, Whatsnew or Search page.  This has an interesting side effect, if this '.
			'indicator is modified, after users add items of this type.  These items will no longer be '.
			'visible via Stats, but the items themselves can be found indirectly (not via status type list of values) '.
			'in listings.php.  For performance reasons no extra validation will be done, to check for the existence '.
			'of user items, if both min_display_user_type and min_create_user_type are modified to a user type above '.
			'users with items in this type.'),

	'min_create_user_type'=>array(
		'If Change Owner = \'Y\', this must be set to \'N\'.',
		'Specifies the minimum user type, who can insert items of this type, or '.
			'update existing items to this type.'.

		'User Types in OpenDb are hierarchical:',
		array(
			'G - Guest (Lowest)',
			'B - Borrower',
			'N - Normal',
			'A - Admin (Highest)')),

	'borrow_ind'=>array(
		'If \'Y\', items can be reserved/checked out/checked in.',
 		'If \'N\', items can not be reserved, but can be [quick] checked out.',
		'If \'B\', items can not be [quick] checked out.  If item checked out, it cannot be updated to '.
			'a s_status_type with borrow_ind = \'B\'',

		'If \'X\', items can not be reserved/[quick] checked out/checked in.  Items cannot have any borrowed '.
			'records (active or otherwise).  An item with borrowed item records cannot be updated to a '.
			's_status_type with borrow_ind = \'X\'',
		),

	'default_ind'=>array(
		'Only one Status Type should be \'Y\'.  Will be checked by default, for new item operations, etc'),

	'status_comment_ind'=>array(
		'If \'Y\' or \'H\', status_comment\'s can be recorded in the item_instance table, and displayed '.
			'in the item_display.php Instance Info section.',

		'If \'H\', then the comment will be shown in item_display.php, only when the owner of the '.
			'item_instance, or an administrator is viewing the record.'),

	'closed_ind'=>array(
		'This column, allows s_status_type records to be used in existing records, but no new '.
			'records can be created with this type, and existing records can not have their status '.
			'changed to types with closed_ind = \'Y\'')
	);

function display_s_status_type_row($status_type_r, $row)
{
	global $PHP_SELF;
	global $ADMIN_TYPE;
	
	echo("\n<tr>");

	echo("\n<td class=\"data\" align=center>".$status_type_r['s_status_type']."</td>");
	echo("\n<td class=\"data\" align=center>".$status_type_r['description']."</td>");

	echo("<td class=\"data\" align=center>");
	// Get the theme specific source of the image.
	if(strlen($status_type_r['img'])>0)
	{
		$src = _theme_image_src($status_type_r['img']);
	}
	if($src!==FALSE && strlen($src)>0)
		echo("<img src=\"$src\">");
	else
		echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
	echo("</td>");

	echo("\n<td class=\"data\" align=center>".ifempty($status_type_r['default_ind'], 'N')."</td>");
    echo("\n<td class=\"data\" align=center>".$status_type_r['closed_ind']."</td>");

	echo("\n<td class=\"data\">[ <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=edit&s_status_type=".$status_type_r['s_status_type']."\">Edit</a>".
		" / <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=delete&s_status_type=".$status_type_r['s_status_type']."\">Delete</a> ]</td>");

	echo("</tr>");
}

function get_edit_form_tooltip($columnname)
{
	global $_COLUMN_HELP;
	global $_COLUMN_DESC;
	
	$buffer = "return show_tooltip(";
	if(isset($_COLUMN_HELP[$columnname]))
	{
	    $block = '';
	    if(is_array($_COLUMN_HELP[$columnname]))
	    {
	        for($i=0; $i<count($_COLUMN_HELP[$columnname]); $i++)
	        {
	            if(is_array($_COLUMN_HELP[$columnname][$i]))
	            {
	            	$block .= '<ul>';
	                for($j=0; $j<count($_COLUMN_HELP[$columnname][$i]); $j++)
	                {
	                    $block .= '<li>'.addslashes($_COLUMN_HELP[$columnname][$i][$j]).'</li>';
	                }
	                $block .= '</ul>';
	            }
	            else
	            {
	                $block .= "<p>".addslashes($_COLUMN_HELP[$columnname][$i])."</p>";
	            }
	        }
	    }
	    
		$buffer .= "'".$block."'";
	}
	else
	{
	    $buffer .= "'No tooltip available'";
	}
	
	if(isset($_COLUMN_DESC[$columnname]))
	{
		$buffer .= ", '".addslashes($_COLUMN_DESC[$columnname])."'";
	}
	
	$buffer .= ");";
	
	return $buffer;
}

function display_edit_form($status_type_r, $HTTP_VARS=NULL)
{
	global $_COLUMN_DESC;
	
	if(is_array($status_type_r))
		echo get_input_field("s_status_type", NULL, $_COLUMN_DESC['s_status_type'], "readonly", "Y", $status_type_r['s_status_type'], TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('s_status_type')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	else
		echo get_input_field("s_status_type", NULL, $_COLUMN_DESC['s_status_type'], "text(1,1)", "Y", $HTTP_VARS['s_status_type'], TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('s_status_type')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	echo get_input_field("description", NULL, $_COLUMN_DESC['description'], "text(30,60)", "Y", ifempty($status_type_r['description'],$HTTP_VARS['description']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('description')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");

	$field = get_input_field("img", NULL, $_COLUMN_DESC['img'], "url(15,*,\"gif,jpg,png\",N)", "N", ifempty($status_type_r['img'],$HTTP_VARS['img']), FALSE);
	$image_src = _theme_image_src(ifempty($status_type_r['img'],$HTTP_VARS['img']));
	if($image_src!==FALSE && strlen($image_src)>0)
		$field .= " <img align=absmiddle valign=absmiddle src=\"$image_src\">";
	echo format_field($_COLUMN_DESC['img'], NULL, $field, TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('img')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	
	echo get_input_field("insert_ind", NULL, $_COLUMN_DESC['insert_ind'], "value_radio_grid('Y,N',*)", "N", ifempty($status_type_r['insert_ind'],$HTTP_VARS['insert_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('insert_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("update_ind", NULL, $_COLUMN_DESC['update_ind'], "value_radio_grid('Y,N',*)", "N", ifempty($status_type_r['update_ind'],$HTTP_VARS['update_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('update_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("delete_ind", NULL, $_COLUMN_DESC['delete_ind'], "value_radio_grid('Y,N',*)", "N", ifempty($status_type_r['delete_ind'],$HTTP_VARS['delete_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('delete_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("change_owner_ind", NULL, $_COLUMN_DESC['change_owner_ind'], "value_radio_grid('Y,N',*)", "N", ifempty($status_type_r['change_owner_ind'], ifempty($HTTP_VARS['change_owner_ind'],"N")), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('change_owner_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	
	$user_type_rs = array_merge(array(array('value'=>'', 'display'=>'')), get_user_types_rs(get_user_types_r()));
	echo format_field($_COLUMN_DESC['min_display_user_type'], NULL, custom_select("min_display_user_type", $user_type_rs, '%value% - %display%', 1, ifempty($status_type_r['min_display_user_type'],$HTTP_VARS['min_display_user_type'])), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('min_display_user_type')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo format_field($_COLUMN_DESC['min_create_user_type'], NULL, custom_select("min_create_user_type", $user_type_rs, '%value% - %display%', 1, ifempty($status_type_r['min_create_user_type'],$HTTP_VARS['min_create_user_type'])), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('min_create_user_type')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	
	echo get_input_field("borrow_ind", NULL, $_COLUMN_DESC['borrow_ind'], "value_radio_grid('Y,N,B,X',*)", "N", ifempty($status_type_r['borrow_ind'],$HTTP_VARS['borrow_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('borrow_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("status_comment_ind", NULL, $_COLUMN_DESC['status_comment_ind'], "value_radio_grid('Y,H,N',*)", "N", ifempty($status_type_r['status_comment_ind'],$HTTP_VARS['status_comment_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('status_comment_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	echo get_input_field("default_ind", NULL, $_COLUMN_DESC['default_ind'], "checkbox(Y,N)", "N", ifempty($status_type_r['default_ind'],$HTTP_VARS['default_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('default_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
	
	if(is_array($status_type_r))
		echo get_input_field("closed_ind", NULL, $_COLUMN_DESC['closed_ind'], "checkbox(Y,N)", "N", ifempty($status_type_r['closed_ind'],$HTTP_VARS['closed_ind']), TRUE, "%prompt%&nbsp;<a class=\"smlink\" href=\"#\" onmouseover=\"".get_edit_form_tooltip('closed_ind')."\" onmouseout=\"return hide_tooltip();\">(?)</a>");
}

if(is_opendb_valid_session())
{
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		if($HTTP_VARS['op'] == 'delete')
		{
			if(is_valid_s_status_type($HTTP_VARS['s_status_type']))
			{
				$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
				
				if( $status_type_r['closed_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y' && fetch_default_status_type_cnt() <= 1)
				{
					$errors[] = array('error'=>'Status type not deleted','detail'=>'Status type is default and cannot be deleted');
                    $HTTP_VARS['op'] = NULL;
				}
				else if(is_exists_items_with_status_type($HTTP_VARS['s_status_type']))// Validate that no items are attached for this status type.
				{
					$errors[] = array('error'=>'Status type not deleted','detail'=>'Status type cannot be deleted while '.$status_type_r['description'].' item instance(s) exist.');
                    $HTTP_VARS['op'] = NULL;
				}
				else
				{
					if($HTTP_VARS['confirmed'] == 'true')
					{
						if(!delete_s_status_type($HTTP_VARS['s_status_type']))
							$errors[] = array('error'=>'Status type not deleted','detail'=>db_error());

                        $HTTP_VARS['op'] = NULL;
					}
					else if($HTTP_VARS['confirmed'] != 'false')
					{
						echo("\n<h3>Delete Status type</h3>");
						echo(get_op_confirm_form($PHP_SELF, 
								"Are you sure you want to delete status type '".$HTTP_VARS['s_status_type']." - ".$status_type_r['description']."'?", 
								array('type'=>$HTTP_VARS['type'], 'op'=>'delete', 's_status_type'=>$HTTP_VARS['s_status_type'])));
					}
					else // confirmation required.
					{
						$HTTP_VARS['op'] = NULL;
					}
				}
			}
			else
			{
				$errors[] = array('error'=>'Invalid status type specified');
                $HTTP_VARS['op'] = NULL;
			}
		}
		else if($HTTP_VARS['op'] == 'update')
		{
			if(is_valid_s_status_type($HTTP_VARS['s_status_type']))
			{
				$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
				
				// If default_ind is currently 'Y', and being set to 'N', or closed_ind being set 'Y' from 'N'
				if( (($HTTP_VARS['default_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y') ||
					($HTTP_VARS['closed_ind'] == 'Y' && $status_type_r['closed_ind'] != 'Y' && $status_type_r['default_ind'] == 'Y')) &&
						fetch_default_status_type_cnt() <= 1)
				{
					$errors[] = array('error'=>'Status type not updated',
										'detail'=>'Status type is default and cannot be closed');
				}
				else if(user_type_cmp($status_type_r['min_create_user_type'], $HTTP_VARS['min_create_user_type']) > 0 && 
						is_exists_items_for_user_type_and_status_type($status_type_r['s_status_type'], $status_type_r['min_create_user_type']))
				{
					$errors[] =	array('error'=>'Status type not updated',
									'detail'=>'Item instance(s) exist for user(s) whose type, is not compatible with the new \''.$_COLUMN_DESC['min_create_user_type'].'\' (min_create_user_type) value.');
				}
				else if($HTTP_VARS['borrow_ind'] != $status_type_r['borrow_ind'] && $HTTP_VARS['borrow_ind'] != 'Y' && $HTTP_VARS['borrow_ind'] != 'N' && is_exists_borrowed_items_for_status_type($HTTP_VARS['s_status_type'], TRUE))
				{
					$errors[] = array('error'=>'Status type not updated',
									'detail'=>'Cannot update borrow_ind to \''.$HTTP_VARS['borrow_ind'].'\', as '.$status_type_r['description'].' item instance(s) exist, which are currently checked out.');
				}
				else if($HTTP_VARS['borrow_ind'] != $status_type_r['borrow_ind'] && $HTTP_VARS['borrow_ind'] != 'Y' && $HTTP_VARS['borrow_ind'] != 'N' && $HTTP_VARS['borrow_ind'] != 'B' && is_exists_borrowed_items_for_status_type($HTTP_VARS['s_status_type']))
				{
					$errors[] = array('error'=>'Status type not updated',
									'detail'=>'Cannot update borrow_ind to \''.$HTTP_VARS['borrow_ind'].'\', as '.$status_type_r['description'].' item instance(s) exist, which have borrowed item(s) attached.');
				}
				else
				{
					if(!update_s_status_type($HTTP_VARS['s_status_type'], $HTTP_VARS['description'], $HTTP_VARS['img'],
								$HTTP_VARS['insert_ind'], $HTTP_VARS['update_ind'], $HTTP_VARS['delete_ind'], 
								$HTTP_VARS['change_owner_ind'], 
								$HTTP_VARS['min_display_user_type'], $HTTP_VARS['min_create_user_type'],
								$HTTP_VARS['borrow_ind'], $HTTP_VARS['status_comment_ind'], $HTTP_VARS['default_ind'],
								$HTTP_VARS['closed_ind']))
					{
						$errors[] = array('error'=>'Status type not updated','detail'=>db_error());
					}
				}

                $HTTP_VARS['op'] = 'edit';
			}
			else
			{
                $errors[] = array('error'=>'Invalid status type specified');
                $HTTP_VARS['op'] = NULL;
			}
		}
		else if($HTTP_VARS['op'] == 'insert')
		{
			$HTTP_VARS['s_status_type'] = strtoupper(substr($HTTP_VARS['s_status_type'],0,1));
			
			if(strlen($HTTP_VARS['s_status_type'])>0 && !is_valid_s_status_type($HTTP_VARS['s_status_type']))
			{
				if(!insert_s_status_type($HTTP_VARS['s_status_type'], $HTTP_VARS['description'], $HTTP_VARS['img'],
								$HTTP_VARS['insert_ind'], $HTTP_VARS['update_ind'], $HTTP_VARS['delete_ind'], 
								$HTTP_VARS['change_owner_ind'], 
								$HTTP_VARS['min_display_user_type'], $HTTP_VARS['min_create_user_type'],
								$HTTP_VARS['borrow_ind'], $HTTP_VARS['status_comment_ind'], $HTTP_VARS['default_ind']))
				{
					$errors[] = array('error'=>'Status type ('.$HTTP_VARS['s_status_type'].') not inserted','detail'=>db_error());
				}

                $HTTP_VARS['op'] = 'edit';
			}
			else
			{
				if(strlen($HTTP_VARS['s_status_type'])>0)
				{
					$errors[] = array('error'=>'Status type exists');
				}
				else
				{
					$errors[] = array('error'=>'Invalid status type specified');
				}
                $HTTP_VARS['op'] = 'new';
			}
		}
		else if($HTTP_VARS['op'] == 'installsql')
		{
			execute_sql_install($ADMIN_TYPE, $HTTP_VARS['sqlfile'], $errors);
            $HTTP_VARS['op'] = NULL;
		}

		if($HTTP_VARS['op'] == 'new')
		{
            // Include validation javascript here.
			echo get_validation_javascript();
            
            echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

			echo("\n<h3>New Status type</h3>");

			if(is_not_empty_array($errors))
				echo format_error_block($errors);

			echo("\n<table cellspacing=2 border=0>");
			echo("\n<form name=\"s_status_type\" action=\"$PHP_SELF\" method=\"POST\">");
	
			echo("\n<input type=\"hidden\" name=\"op\" value=\"insert\">");
			echo("\n<input type=\"hidden\" name=\"type\" value=\"".$HTTP_VARS['type']."\">");
				
			display_edit_form(NULL, $HTTP_VARS);
				
			if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
			{
				echo("\n<tr><td align=left nowrap>".
				format_help_block(array(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')))).
				"</td><td>&nbsp;</td></tr>");
			}
				
			echo("\n<tr><td colspan=\"2\" align=center>");
			if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
				echo("\n<input type=button value=\"Insert\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
			else
				echo("\n<input type=button value=\"Insert\" onclick=\"this.form.submit();\">");
			echo("\n</td></tr>");

			echo("\n</form>");
			echo("\n</table>");
		}
		else if($HTTP_VARS['op'] == 'edit' && is_valid_s_status_type($HTTP_VARS['s_status_type']))
		{
            // Include validation javascript here.
			echo get_validation_javascript();

			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main</a>]</div>");

			echo("\n<h3>Edit Status type</h3>");
            if(is_not_empty_array($errors))
				echo format_error_block($errors);

			$status_type_r = fetch_status_type_r($HTTP_VARS['s_status_type']);
			if($status_type_r!==FALSE)
			{
				echo("\n<table cellspacing=2 border=0>");
				echo("\n<form name=\"s_status_type\" action=\"$PHP_SELF\" method=\"POST\">");
	
				echo("\n<input type=\"hidden\" name=\"op\" value=\"update\">");
				echo("\n<input type=\"hidden\" name=\"type\" value=\"".$HTTP_VARS['type']."\">");

				display_edit_form($status_type_r);

				if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
				{
					echo("\n<tr><td align=left nowrap>".
						format_help_block(array(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')))).
						"</td><td>&nbsp;</td></tr>");
				}
					
				echo("\n<tr><td colspan=\"2\" align=center>");
					
				if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
				{
					echo("\n<input type=button value=\"Update\" onclick=\"if(!checkForm(this.form)){return false;}else{this.form.submit();}\">");
				}
				else
				{
					echo("\n<input type=button value=\"Update\" onclick=\"this.form.submit();\">");
				}
				echo("\n</td></tr>");

				echo("\n</form>");
				echo("\n</table>");
			}
			else
			{
				echo format_error_block('Status type ('.$HTTP_VARS['s_status_type'].') not found');
			}
		}
		else if(strlen($HTTP_VARS['op'])==0)
		{
			echo("[ <a href=\"${PHP_SELF}?type=${ADMIN_TYPE}&op=new\">New Status Type</a> ]");
			
            if(is_not_empty_array($errors))
				echo format_error_block($errors);

            echo("\n<form name=\"navigate\" action=\"$PHP_SELF\" method=\"GET\">".
				"\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">".
				"\n<input type=\"hidden\" name=\"op\" value=\"\">".
				"\n<input type=\"hidden\" name=\"s_status_type\" value=\"\">".
				"\n</form>");

	        echo("\n<table cellspacing=2 border=0>");
			echo("\n<form name=\"s_status_type\" action=\"$PHP_SELF\" method=\"POST\">");

			echo("\n<input type=\"hidden\" name=\"op\" value=\"\">");
			echo("\n<input type=\"hidden\" name=\"type\" value=\"".$ADMIN_TYPE."\">");

			echo("<tr class=\"navbar\">"
				."<th>Type</th>"
				."<th>Description</th>"
				."<th>Image</th>"
                ."<th>Default</th>"
                ."<th>Closed</th>"
				."<th></th>"
				."</tr>");
			$column_count = 6;

			$results = fetch_status_type_rs();
			if($results)
			{
				// value, display, img, checked_ind, order_no
				$row = 0;
				while($status_type_r = db_fetch_assoc($results))
				{
					display_s_status_type_row($status_type_r, $row);
					$row++;
				}
				db_free_result($results);
			}

			echo("</form>");
			echo("</table>");

			function is_not_valid_s_status_type($type)
			{
				return !is_valid_s_status_type($type, FALSE);
			}
			generate_sql_list($ADMIN_TYPE, 'Status Type', "/([a-zA-Z]{1})-([^$]+)$/", 'is_not_valid_s_status_type');
			
		}//else if(strlen($HTTP_VARS['op'])==0)
	}//if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
}//if(is_opendb_valid_session())
?>
