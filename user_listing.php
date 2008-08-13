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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");
include_once("./functions/email.php");
include_once("./functions/user.php");
include_once("./functions/datetime.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/listutils.php");
include_once("./functions/HTML_Listing.class.inc");

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		if(is_user_granted_permission(PERM_ADMIN_USER_LISTING))
		{
		    if(strlen($HTTP_VARS['override_page_title'])>0)
		        $page_title = $HTTP_VARS['override_page_title'];
			else if($HTTP_VARS['restrict_active_ind'] == 'X')
			    $page_title = get_opendb_lang_var('activate_users');
			else
				$page_title = get_opendb_lang_var('user_list');

			echo(_theme_header($page_title));
			echo("<h2>".$page_title."</h2>");
			
			$listingObject =& new HTML_Listing($PHP_SELF, $HTTP_VARS, $HTTP_VARS['mode']);
			
			$listingObject->setNoRowsMessage(get_opendb_lang_var('no_users_found'));

			if($HTTP_VARS['restrict_active_ind'] == 'X')
			    $restrict_users_mode = INCLUDE_ACTIVATE_USER;
			else if($HTTP_VARS['show_deactivated_users'] == 'Y')
				$restrict_users_mode = INCLUDE_DEACTIVATED_USER;
			else
				$restrict_users_mode = EXCLUDE_DEACTIVATED_USER;
			
			$listingObject->startListing();
			
			if(is_numeric($listingObject->getItemsPerPage()))
			{
				$listingObject->setTotalItems(fetch_user_cnt(NULL, INCLUDE_ROLE_PERMISSIONS, INCLUDE_CURRENT_USER, $restrict_users_mode));
				if($listingObject->getTotalItemCount()>0)
				{
					$result = fetch_user_rs(
							NULL, //$user_role_permissions
							INCLUDE_ROLE_PERMISSIONS,
							INCLUDE_CURRENT_USER,
							$restrict_users_mode,
							$listingObject->getCurrentOrderBy(),
							$listingObject->getCurrentSortOrder(),
							$listingObject->getStartIndex(),
							$listingObject->getItemsPerPage());
				}
			}
			else
			{
				$result = fetch_user_rs(
							NULL, //$user_role_permissions
							INCLUDE_ROLE_PERMISSIONS,
							INCLUDE_CURRENT_USER,
							$restrict_users_mode,
							$listingObject->getCurrentOrderBy(),
							$listingObject->getCurrentSortOrder()); //$include_deactivated_users
			}

            $listingObject->addHeaderColumn(NULL, 'user_id_rs', FALSE, 'checkbox');
			$listingObject->addHeaderColumn(get_opendb_lang_var('user'), 'user_id');
			$listingObject->addHeaderColumn(get_opendb_lang_var('action'));
			$listingObject->addHeaderColumn(get_opendb_lang_var('user_role'), 'role');
            if($HTTP_VARS['restrict_active_ind'] != 'X')
			{
				$listingObject->addHeaderColumn(get_opendb_lang_var('last_visit'), 'lastvisit');
			}

			if($result)
			{
				$v_listing_url_vars = $HTTP_VARS;
				$v_listing_url_vars['mode'] = NULL;
				unset($v_listing_url_vars['show_deactivated_users_cbox']);
				
				register_opendb_session_var('user_listing_url_vars', $v_listing_url_vars);
			
				while ($user_r = db_fetch_assoc($result))
				{
				    $user_is_active = is_user_active($user_r['user_id']);
					$listingObject->startRow();

					// todo - consider disabling for guest users!
					if($HTTP_VARS['restrict_active_ind']!='X'?$user_is_active:TRUE)
					{
						$listingObject->addCheckboxColumn($user_r['user_id'], FALSE);
					}
					else
					{
						$listingObject->addColumn();
					}

					$user_name = get_opendb_lang_var('user_name',array('fullname'=>$user_r['fullname'], 'user_id'=>$user_r['user_id']));

					$listingObject->addColumn('<a href="user_profile.php?uid='.$user_r['user_id'].'" title="'.get_opendb_lang_var('user_profile').'">'.$user_name.'</a>');

					$action_links_rs = NULL;
					$action_links_rs[] = array(url=>'user_admin.php?op=edit&user_id='.$user_r['user_id'], img=>'edit_user.gif',text=>get_opendb_lang_var('edit'));

					if($user_r['user_id'] != get_opendb_session_var('user_id'))
					{
						if($user_is_active)
						{
							$action_links_rs[] = array(url=>'user_admin.php?op=deactivate&user_id='.$user_r['user_id'], img=>'deactivate_user.gif',text=>get_opendb_lang_var('deactivate_user'));
						}
						else
						{
							$action_links_rs[] = array(url=>'user_admin.php?op=activate&user_id='.$user_r['user_id'], img=>'activate_user.gif',text=>get_opendb_lang_var('activate_user'));
						}
					}
				
					$action_links_rs[] = array(url=>'user_admin.php?op=change_password&user_id='.$user_r['user_id'], img=>'change_password.gif',text=>get_opendb_lang_var('change_password'));

					$listingObject->addActionColumn($action_links_rs);

					$listingObject->addColumn($user_r['role_description']);

                    if($HTTP_VARS['restrict_active_ind'] != 'X')
					{
						if($user_r['active_ind'] == 'Y')
						{
							$listingObject->addColumn(
									(strlen($user_r['lastvisit'])>0?get_localised_timestamp(get_opendb_config_var('user_admin', 'datetime_mask'),$user_r['lastvisit']):get_opendb_lang_var('never_logged_in')));
						}
						else
						{
							$listingObject->addColumn(get_opendb_lang_var('deactivated'));
						}
					}
					
					$listingObject->endRow();
				}
				db_free_result($result);
			} //if($result)

			$listingObject->endListing();
			
			if($listingObject->isCheckboxColumns()>0)
			{
				if($HTTP_VARS['restrict_active_ind'] == 'X')
			    {
					$checkbox_action_rs[] = array('action'=>'user_admin.php', 'op'=>'activate_users', link=>get_opendb_lang_var('activate_users'));
			    }
			    else if(is_valid_opendb_mailer())
				{
					$checkbox_action_rs[] = array('action'=>'email.php', 'op'=>'send_to_uids', link=>get_opendb_lang_var('email_users'));
				}
				
				echo(format_checkbox_action_links('user_id_rs', get_opendb_lang_var('no_users_checked'), $checkbox_action_rs));
			}

			echo(format_help_block($listingObject->getHelpEntries()));
			
			echo("<ul class=\"listingControls\">");
			if($HTTP_VARS['restrict_active_ind'] != 'X')
			{
				echo("<li>".getToggleControl(
						$PHP_SELF, 
						$HTTP_VARS, 
						get_opendb_lang_var('show_deactivated_users'), 
						'show_deactivated_users', 
						ifempty($HTTP_VARS['show_deactivated_users'], 'N')).
					"</li>");
			}
			echo("<li>".getItemsPerPageControl($PHP_SELF, $HTTP_VARS)."</li>");
			echo("</ul>");
			
			echo(_theme_footer());
		}
		else
		{
			echo _theme_header(get_opendb_lang_var('not_authorized_to_page'));
			echo("<p class=\"error\">".get_opendb_lang_var('not_authorized_to_page')."</p>");
			echo(_theme_footer());
		}	
	}
	else
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())
else
{
	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo("<p class=\"error\">".get_opendb_lang_var('site_is_disabled')."</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>