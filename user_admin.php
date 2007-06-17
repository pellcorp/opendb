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

include_once("./functions/user.php");
include_once("./functions/widgets.php");
include_once("./functions/http.php");
include_once("./functions/email.php");
include_once("./functions/datetime.php");
include_once("./functions/borrowed_item.php");
include_once("./functions/item.php");
include_once("./functions/review.php");
include_once("./functions/item_input.php");
include_once("./functions/address_type.php");
include_once("./functions/secretimage.php");

/**
* 	Assumes that is_user_admin check has been made
*/
function get_new_user_usertype_input_form($HTTP_VARS, $user_type_r, $user_type)
{
	global $PHP_SELF;
	
	echo("<h3>".get_opendb_lang_var('choose_user_type')."</h3>");
	
	$buffer .= "<form action=\"$PHP_SELF\" method=\"GET\">".
		get_url_fields($HTTP_VARS, NULL, array('user_type')); // Pass all http variables
	
	$user_type_rs = get_user_types_rs($user_type_r, ifempty($HTTP_VARS['user_type'],$user_type));
	$buffer .= "<dl class=\"userType\">";
	while(list(,$user_type_r) = each($user_type_rs))
	{
		$buffer .= "\n<dt>".
				"<input type=\"radio\" name=\"user_type\" value=\"".$user_type_r['value']."\"".($user_type_r['checked_ind']=='Y'?' CHECKED':'').">${user_type_r['display']}".
				"</dt>";
		$buffer .= "<dd>".$user_type_r['description']."</dd>";
	}
	$buffer .= "</dl>";
	
	$buffer .= "\n<input type=\"submit\" value=\"".get_opendb_lang_var('continue')."\">";

	$buffer .= "\n</form>";
			
	return $buffer;
}

/**
* @param $op is 'edit' or 'new'
*/
function get_user_input_form($user_r, $HTTP_VARS)
{
	global $_OPENDB_THEME;
	global $PHP_SELF;
	
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		// Include validation javascript, which will validate data input.
		echo get_validation_javascript();
	}

	$buffer .= "<form action=\"$PHP_SELF\" method=\"POST\">";
	
	$buffer .= "<table class=\"userInputForm\">";
	if(is_not_empty_array($user_r))
	{
		$buffer .= get_input_field("user_id",
				NULL, // s_attribute_type
				get_opendb_lang_var('userid'), 
				"readonly", //input type.
				"", //compulsory!
				$user_r['user_id'],
				TRUE);
	}
	else
	{
		$buffer .= get_input_field(
				"user_id",
				NULL, // s_attribute_type
				get_opendb_lang_var('userid'),
				"filtered(20,20,a-zA-Z0-9_.)", //input type.
				"Y", //compulsory!
				$HTTP_VARS['user_id'],
				TRUE);
	}			

	$buffer .= format_field(
					get_opendb_lang_var('user_type'),
					NULL, 
					get_usertype_prompt(ifempty($HTTP_VARS['user_type'],$user_r['type'])));
	
	if(!is_array($user_r))
	{
		$buffer .= "<input type=hidden name=\"user_type\" value=\"".$HTTP_VARS['user_type']."\">";
	}
							
	$buffer .= get_input_field("fullname",
				NULL, // s_attribute_type
				get_opendb_lang_var('fullname'), 
                "text(30,100)", //input type.
   	            "Y", //compulsory!
       	        ifempty($HTTP_VARS['fullname'],$user_r['fullname']),
				TRUE);
	
	if(get_opendb_config_var('user_admin', 'user_themes_support')!==FALSE)
	{	
		$uid_theme = ifempty($HTTP_VARS['uid_theme'],$user_r['theme']);
		$buffer .= format_field(get_opendb_lang_var('user_theme'), 
						NULL, 
						custom_select("uid_theme", get_user_theme_r(), "%value%", 1, is_legal_user_theme($uid_theme)?$uid_theme:get_opendb_config_var('site', 'theme')));// If theme no longer exists, then set to default!
	}
	
	if(get_opendb_config_var('user_admin', 'user_language_support')!==FALSE)
	{	
		// Do not bother with language input field if only one language pack available.
		if(fetch_language_cnt()>1)
		{
			$uid_language = ifempty($HTTP_VARS['uid_language'], $user_r['language']);
			
			$buffer .= format_field(
							get_opendb_lang_var('user_language'), 
							NULL, 
							custom_select(
								'uid_language', 
								fetch_language_rs(), 
								"%language%", 
								1, 
								is_exists_language($uid_language)?$uid_language:get_opendb_config_var('site', 'language'),
								'language',
								NULL,
								'default_ind'));// If language no longer exists, then set to default!
		}				
	}
	
	$buffer .= "</table>";
	
	// Now do the addresses
	if(is_not_empty_array($user_r))
	{
		$addr_results = fetch_user_address_type_rs($user_r['user_id'], $user_r['type'], TRUE);
	}
	else
	{
		$addr_results = fetch_address_type_rs($HTTP_VARS['user_type'], TRUE);
	}
	
	if($addr_results)
	{
		while($address_type_r = db_fetch_assoc($addr_results))
		{
			$v_address_type = strtolower($address_type_r['s_address_type']);
			
			if(is_not_empty_array($user_r))
			{
				$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $user_r['type'], 'update', TRUE);
			}
			else
			{
				$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $HTTP_VARS['user_type'], 'update', TRUE);
			}	
			
			if($attr_results)
			{
				$buffer .= '<h3>'.$address_type_r['description'].'</h3>';
				
				$buffer .= '<label for="'.$v_address_type.'[public_address_ind]">'.get_opendb_lang_var('public_address_indicator').'</label>'.
							'<input type="checkbox" id="'.$v_address_type.'[public_address_ind]" name="'.$v_address_type.'[public_address_ind]" value="Y"'.(ifempty($address_type_r['public_address_ind'], $HTTP_VARS[$v_address_type]['public_address_ind'])=='Y'?' CHECKED':'').'">';
				
				$buffer .= '<label for="'.$v_address_type.'[borrow_address_ind]">'.get_opendb_lang_var('borrow_address_indicator').'</label>'.
							'<input type="checkbox" name="'.$v_address_type.'[borrow_address_ind]" value="Y"'.(ifempty($address_type_r['borrow_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind'])=='Y'?' CHECKED':'').'">';
				
				$buffer .= "<table class=\"addressInputForm\">";
				while($addr_attribute_type_r = db_fetch_assoc($attr_results))
				{
					$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);
					
					$value = NULL;
					if($address_type_r['sequence_number']!==NULL)
					{
						if(is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type']))
						{
							$value = fetch_user_address_lookup_attribute_val(
														$address_type_r['sequence_number'], 
														$addr_attribute_type_r['s_attribute_type'], 
														$addr_attribute_type_r['order_no']);
						}
						else
						{
							
							$value = fetch_user_address_attribute_val(
														$address_type_r['sequence_number'], 
														$addr_attribute_type_r['s_attribute_type'],
														$addr_attribute_type_r['order_no']);
						}
						
						$value = ifempty(
								filter_item_input_field(
										$addr_attribute_type_r, 
										$HTTP_VARS[$v_address_type][$fieldname]),
								$value);
					}
					else
					{
                        $value = filter_item_input_field(
										$addr_attribute_type_r,
										$HTTP_VARS[$v_address_type][$fieldname]);
					}
						
					// If this is an edit operation - the value must be NOT NULL
					// for some widgets to work properly.
					if($address_type_r['sequence_number']!==NULL && $value === NULL)
					{
						$value = '';
					}
					
					$buffer .= get_item_input_field(
							$v_address_type.'['.$fieldname.']', 
							$addr_attribute_type_r,
							NULL, //$item_r
							$value);
				}//while
				db_free_result($attr_results);
				$buffer .= "</table>";
			}//if($attr_results)
		}//while
		db_free_result($addr_results);
	}//if($addr_results)

	if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
	{
		$buffer .= format_help_block(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')));
	}
	
	// can only provide password on new_user, otherwise separate function for changing password is required.
	if($HTTP_VARS['op'] == 'new_user')
	{
		$buffer .= "<h3>".get_opendb_lang_var('password')."</h3>";
		
		$buffer .= "<table class=\"changePasswordForm\">";
		if(get_opendb_config_var('user_admin', 'user_passwd_change_allowed')!==FALSE ||
					is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
		{
			if(is_valid_opendb_mailer() && ($HTTP_VARS['user_type'] == 'A' || $HTTP_VARS['user_type'] == 'N' || $HTTP_VARS['user_type'] == 'B'))
				$compulsory_ind = 'N';
			else
				$compulsory_ind = 'Y';

			$buffer .= get_input_field("pwd",
					NULL, // s_attribute_type
					get_opendb_lang_var('new_passwd'),
	                "password(30,40)", //input type.
					$compulsory_ind, //compulsory!
					"",
					TRUE);

			$buffer .= get_input_field("confirmpwd",
					NULL, // s_attribute_type
					get_opendb_lang_var('confirm_passwd'),
					"password(30,40)", //input type.
					$compulsory_ind, //compulsory!
					"",
					TRUE,
					NULL,
					get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE?"if( (this.form.pwd.value.length!=0 || this.form.confirmpwd.value.length!=0) && this.form.pwd.value!=this.form.confirmpwd.value){alert('".get_opendb_lang_var('passwds_do_not_match')."'); this.focus(); return false;}":"");
		}
		$buffer .= "\n</table>";
		
		// insert user
		if(is_valid_opendb_mailer() &&
					($HTTP_VARS['user_type'] == 'A' || 
					$HTTP_VARS['user_type'] == 'N' || 
					$HTTP_VARS['user_type'] == 'B'))
		{
			$buffer .= format_help_block(get_opendb_lang_var('new_passwd_will_be_autogenerated_if_not_specified'));
		}
	}//if($HTTP_VARS['op'] == 'new_user')
	
// do verify code logic here
	if($HTTP_VARS['op'] == 'signup')
	{
		$random_num = get_secretimage_random_num();
		$buffer .= "\n<input type=\"hidden\" name=\"gfx_random_number\" value=\"$random_num\">";

	   	$buffer .= "<p class=\"verifyCode\"><label for=\"gfx_code_check\">".get_opendb_lang_var('verify_code')."</label>".
	   				"<img src=\"$PHP_SELF?op=signup&op2=gfx_code_check&gfx_random_number=$random_num\">".
					"<input id=\"gfx_code_check\" type=\"text\" name=\"gfx_code_check\" size=\"15\" maxlength=\"6\"></p>";
	}
	
	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";
						
	// If user_id defined, we are in update mode.			
	if(is_not_empty_array($user_r))
	{		
		$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"update\">";
			
		if($HTTP_VARS['user_id'] != get_opendb_session_var('user_id'))
		{
			$buffer .= "\n<input type=\"button\" onclick=\"this.form.op.value='update'; $onclick_event\" value=\"".get_opendb_lang_var('update_user')."\">";
			
			if(is_user_active($HTTP_VARS['user_id']) && get_opendb_config_var('user_admin', 'user_delete_support') !== FALSE)
			{
                if(get_opendb_config_var('user_admin', 'user_deactivate_support') === TRUE)
                    $buffer .= "\n<input type=\"button\" onclick=\"this.form.op.value='deactivate'; this.form.submit();\" value=\"".get_opendb_lang_var('deactivate_user')."\">";
				else if(get_opendb_config_var('user_admin', 'user_delete_support') === TRUE)
					$buffer .= "\n<input type=\"button\" onclick=\"this.form.op.value='delete'; this.form.submit();\" value=\"".get_opendb_lang_var('delete_user')."\">";
			}
			else if(!is_user_active($HTTP_VARS['user_id']))
			{
				$buffer .= "\n<input type=\"button\" onclick=\"this.form.op.value='activate'; this.form.submit();\" value=\"".get_opendb_lang_var('activate_user')."\">";
			}
		}
		else
		{
			$buffer .= "\n<input type=\"button\" onclick=\"$onclick_event\" value=\"".get_opendb_lang_var('update_details')."\">";
		}
	}
	else //insert mode
	{
		if($HTTP_VARS['op'] != 'signup')
		{
			if(is_valid_opendb_mailer() &&
						($HTTP_VARS['user_type'] == 'A' || 
						$HTTP_VARS['user_type'] == 'N' || 
						$HTTP_VARS['user_type'] == 'B'))
			{
				// If we are actually showing the form, because an insert has failed,
				// we should set the email_user indicator according to what the user
				// has chosen.
				if($HTTP_VARS['op'] == 'new_user')
				{
					if($HTTP_VARS['email_user'] == 'Y')
						$checked = "CHECKED";
					else
						$checked = "";
				}						
				else
					$checked = "CHECKED";
			
				$buffer .= '<fieldset style="{border: none;}"><label>'.get_opendb_lang_var('send_welcome_email').
							"<input id=\"email_user\" type=\"checkbox\" name=\"email_user\" value=\"Y\" $checked></label></fieldset>";
			}
			
			$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"insert\">".
					"\n<input type=\"button\" onclick=\"$onclick_event\" value=\"".get_opendb_lang_var('add_user')."\">";
			
		}//if($HTTP_VARS['op'] != 'signup')
		else
		{
			// have to stay in signup mode.
			$buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"signup\">".
						"<input type=\"hidden\" name=\"op2\" value=\"send_info\">".
						"<input type=\"button\" onclick=\"$onclick_event\" value=\"".get_opendb_lang_var('submit')."\">";
		}
	}		
	
	$buffer .= "<input type=\"hidden\" name=\"listing_link\" value=\"".$HTTP_VARS['listing_link']."\">";
	
	$buffer .= "\n</form>";
	
	return $buffer;		
}

function get_user_password_change_form($user_r, $HTTP_VARS)
{
	global $PHP_SELF;

	if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
	{
		// Include validation javascript, which will validate data input.
		echo get_validation_javascript();
	}
			
	$buffer .= "<form action=\"$PHP_SELF\" method=\"POST\">";

	$buffer .= "<table class=\"changePasswordForm\">";
	$buffer .= get_input_field("user_id",
				NULL, // s_attribute_type
				get_opendb_lang_var('userid'),
				"readonly", //input type.
				"", //compulsory!
				$user_r['user_id'],
				TRUE);
	
    $buffer .= get_input_field("pwd",
					NULL, // s_attribute_type
					get_opendb_lang_var('new_passwd'),
	                "password(30,40)", //input type.
					'Y', //compulsory!
					"",
					TRUE);

			$buffer .= get_input_field("confirmpwd",
					NULL, // s_attribute_type
					get_opendb_lang_var('confirm_passwd'),
					"password(30,40)", //input type.
					'Y', //compulsory!
					"",
					TRUE,
					NULL,
					get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE?"if( (this.form.pwd.value.length!=0 || this.form.confirmpwd.value.length!=0) && this.form.pwd.value!=this.form.confirmpwd.value){alert('".get_opendb_lang_var('passwds_do_not_match')."'); this.focus(); return false;}":"");

	$buffer .= "</table>";
	
    if(get_opendb_config_var('widgets', 'show_prompt_compulsory_ind')!==FALSE)
	{
		$buffer .= format_help_block(array('img'=>'compulsory.gif', 'text'=>get_opendb_lang_var('compulsory_field')));
	}
	
    if(get_opendb_config_var('widgets', 'enable_javascript_validation')!==FALSE)
		$onclick_event = "if(!checkForm(this.form)){return false;}else{this.form.submit();}";
	else
		$onclick_event = "this.form.submit();";
	
    $buffer .= "\n<input type=\"hidden\" name=\"op\" value=\"update_password\">".
					"\n<input type=\"button\" onclick=\"$onclick_event\" value=\"".get_opendb_lang_var('change_password')."\">";
	
	$buffer .= "<input type=\"hidden\" name=\"listing_link\" value=\"".$HTTP_VARS['listing_link']."\">";
	
	$buffer .= "\n</form>";

	return $buffer;
}

function is_empty_attribute($s_attribute_type, $attribute_val)
{
	if(is_lookup_attribute_type($s_attribute_type))
	{
		if(is_array($attribute_val))
		{
			if(count($attribute_val)>0)
				return TRUE;
			else
				return FALSE;
		}
		else
		{
			if(strlen($attribute_val)>0)
				return TRUE;
			else
				return FALSE;
		}
	}
	else
	{
		if(strlen($attribute_val)>0)
			return TRUE;
		else
			return FALSE;
	}
}

/**
	Send notification to user that account is active
	
	@param - $user_r - a single record from user table
*/
function send_newuser_email($user_r, $passwd, &$errors)
{
	$from = fetch_user_email(get_opendb_session_var('user_id'));
	$from_name = fetch_user_name(get_opendb_session_var('user_id'));

	$subject    = get_opendb_lang_var('welcome_to_site', 'site', get_opendb_config_var('site', 'title'));
	$message    = get_opendb_lang_var('to_user_email_intro', 'fullname', $user_r['fullname']).
								"\n\n".
								get_opendb_lang_var('welcome_email', 'site', get_opendb_config_var('site', 'title')).
								 "\n\n".
			    	    	     get_opendb_lang_var('userid').": ".$user_r['user_id']."\n".
			        	    	 get_opendb_lang_var('new_passwd').": ".$passwd;

	if(is_user_allowed_to_edit_info($user_r['user_id']))
	{
		// Provide a link to open User Info form in edit mode.
		$message .= "\n\n".
				get_opendb_lang_var('edit_my_info').":\n".
				"    ".get_site_url()."user_admin.php?op=edit&user_id=".urlencode($user_r['user_id']);
 	}

	$email_addr = fetch_user_email($user_r['user_id']);
	if(is_valid_email_addr($email_addr))
	{
		// In this case ask the email function not to append [OpenDb] to subject
		// line, as we will already be including reference to the name
		return opendb_email(
						$email_addr,
						$user_r['fullname'],
						$from,
						$from_name,
						$subject,
						$message,
						$errors,
						FALSE);
	}
}

/*
*/
function handle_user_insert(&$HTTP_VARS, &$errors)
{
	// We need to check if user already exists, so we can return a nice error.
	// A userid CANNOT differ in case alone!
	if(!is_user_valid($HTTP_VARS['user_id'], TRUE))
	{
		if(is_usertype_valid($HTTP_VARS['user_type']))
		{
			$HTTP_VARS['user_id'] = strtolower(filter_input_field("filtered(20,20,a-zA-Z0-9_.)", $HTTP_VARS['user_id']));
			$HTTP_VARS['fullname'] = filter_input_field("text(30,100)", $HTTP_VARS['fullname']);

			$is_uid_validated = validate_input_field(get_opendb_lang_var('userid'), "filtered(20,20,a-zA-Z0-9_.)", "Y", $HTTP_VARS['user_id'], $errors);
			$is_fullname_validated = validate_input_field(get_opendb_lang_var('fullname'), "text(30,100)", "Y", $HTTP_VARS['fullname'], $errors);

			if($is_uid_validated && $is_fullname_validated)
			{
				$is_address_validated = TRUE;
				$addr_results = fetch_address_type_rs($HTTP_VARS['user_type'], TRUE);
				if($addr_results)
				{
					while($address_type_r = db_fetch_assoc($addr_results))
					{
						$v_address_type = strtolower($address_type_r['s_address_type']);
						$address_attribs_provided[$v_address_type] = FALSE;
						
						$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $HTTP_VARS['user_type'], 'update', TRUE);
						if($attr_results)
						{
							while($addr_attribute_type_r = db_fetch_assoc($attr_results))
							{
								$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'],$addr_attribute_type_r['order_no']);
								$HTTP_VARS[$v_address_type][$fieldname] = filter_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname]);
								
								if(is_empty_attribute($addr_attribute_type_r['s_attribute_type'], $HTTP_VARS[$v_address_type][$fieldname])!==FALSE)
								{
									$address_attribs_provided[$v_address_type] = TRUE;
									
									if(!validate_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname], $errors))
									{
										$is_address_validated = FALSE;
									}
								}
							}
							db_free_result($attr_results);
						}//if($addr_results)
					}
					db_free_result($addr_results);
				}//if($addr_results)
				
				if($is_address_validated)
				{
				    // no password saved when signing up, as user still must be activated
				    if($HTTP_VARS['op'] != 'signup')
					{
						// If no password specified, generate one
						if(strlen($HTTP_VARS['pwd'])==0)
						{
							// check whether a password is provided or not
							if(is_valid_opendb_mailer() &&
									($HTTP_VARS['user_type'] == 'A' ||
									$HTTP_VARS['user_type'] == 'N' ||
									$HTTP_VARS['user_type'] == 'B'))
							{
								$HTTP_VARS['pwd'] = generate_password(8);
							}
							else
							{
								$errors[] = array('error'=>get_opendb_lang_var('passwd_not_specified'));
								return FALSE;
							}
						}
						else if($HTTP_VARS['pwd'] != $HTTP_VARS['confirmpwd'])
						{
							$errors[] = array('error'=>get_opendb_lang_var('passwds_do_not_match'));
							return FALSE;
						}
			        }//if($HTTP_VARS['op'] != 'signup')
			        else
			        {
			            // Will be reset when user activated
			            $HTTP_VARS['pwd'] = 'none';
			        }
			        
					// Do not allow update with illegal theme!
					if(get_opendb_config_var('user_admin', 'user_themes_support')===FALSE || !is_legal_user_theme($HTTP_VARS['uid_theme']))
					{
						$HTTP_VARS['uid_theme'] = NULL;
					}
				
					// Do not allow update with illegal language.			
					if(get_opendb_config_var('user_admin', 'user_language_support')===FALSE || !is_exists_language($HTTP_VARS['uid_language']))
					{
						$HTTP_VARS['uid_language'] = NULL;
					}

					// If the user is in signup mode, set active_ind='X', which means that the user must be activated by the admin before he can log in
					if($HTTP_VARS['op'] == 'signup')
					{
						$active_ind = 'X';
					}
					else
					{
						$active_ind = 'Y';
					}

					// We want to validate and perform inserts even in signup mode
					if(insert_user($HTTP_VARS['user_id'], $HTTP_VARS['fullname'], $HTTP_VARS['pwd'], $HTTP_VARS['user_type'], $HTTP_VARS['uid_language'], $HTTP_VARS['uid_theme'], $active_ind))
					{
						$address_creation_success = TRUE;
						$addr_results = fetch_address_type_rs($HTTP_VARS['user_type'], TRUE);
						if($addr_results)
						{
							while($address_type_r = db_fetch_assoc($addr_results))
							{
								$v_address_type = strtolower($address_type_r['s_address_type']);
								
								if($address_attribs_provided[$v_address_type]!==FALSE)
								{
									$new_sequence_number = insert_user_address($HTTP_VARS['user_id'], $address_type_r['s_address_type'], $HTTP_VARS[$v_address_type]['public_access_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']);
									if($new_sequence_number !== FALSE)
									{
										$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $HTTP_VARS['user_type'], 'update', TRUE);
										if($attr_results)
										{
											while($addr_attribute_type_r = db_fetch_assoc($attr_results))
											{
												$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);
												
												if(is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type']))
												{
													$lookup_value_r = NULL;
													if(is_array($HTTP_VARS[$v_address_type][$fieldname]))
														$lookup_value_r =& $HTTP_VARS[$v_address_type][$fieldname];
													else if(strlen(trim($HTTP_VARS[$v_address_type][$fieldname]))>0)
														$lookup_value_r[] = $HTTP_VARS[$v_address_type][$fieldname];
													
													if(is_not_empty_array($lookup_value_r))
													{
														if(!insert_user_address_attributes($new_sequence_number, $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $lookup_value_r))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_added'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
												}
												else
												{
													if(strlen($HTTP_VARS[$v_address_type][$fieldname])>0)
													{
														if(!insert_user_address_attributes($new_sequence_number, $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $HTTP_VARS[$v_address_type][$fieldname]))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_added'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
												}
											}
											db_free_result($attr_results);
										}//if($addr_results)
									}//if($new_sequence_number !== FALSE)
									else
									{
										$db_error = db_error();
										$errors[] = array('error'=>get_opendb_lang_var('user_address_not_added'),'detail'=>$db_error);
										$address_creation_success = FALSE;
									}
								}//if($address_attribs_provided[$v_address_type]!==FALSE)
							}
							db_free_result($addr_results);
						}//if($addr_results)
			
						if($address_creation_success)
						{
							return TRUE;
						}
						else
						{
							return FALSE;
						}
					}
					else
					{
						$db_error = db_error();
						$errors[] = array('error'=>get_opendb_lang_var('user_not_added', 'user_id', $HTTP_VARS['user_id']),'detail'=>$db_error);
						return FALSE;
					}
				}//if($is_address_validated)
				else
				{
					return FALSE;
				}
			}//if($is_uid_validated && $is_fullname_validated)
			else // not all required information specified
			{
				return FALSE;				
			}
		}//if(is_usertype_valid($HTTP_VARS['user_type']))
		else
		{
			return FALSE;
		}
	}			
	else
	{
		$errors[] = array('error'=>get_opendb_lang_var('user_exists', 'user_id', $HTTP_VARS['user_id']),'detail'=>'');
		return FALSE;
	}
}

/*
*/
function handle_user_update(&$HTTP_VARS, &$errors)
{
	$user_r = fetch_user_r($HTTP_VARS['user_id']);
	if(is_not_empty_array($user_r))
	{
		$HTTP_VARS['fullname'] = filter_input_field("text(30,100)", $HTTP_VARS['fullname']);
		
		$is_fullname_validated = validate_input_field(get_opendb_lang_var('fullname'), "text(30,100)", "Y", $HTTP_VARS['fullname'], $errors);
		if($is_fullname_validated)
		{
			$address_attribs_provided = NULL;
			$is_address_validated = TRUE;
			$addr_results = fetch_user_address_type_rs($user_r['user_id'], $user_r['type'], TRUE);
			if($addr_results)
			{
				while($address_type_r = db_fetch_assoc($addr_results))
				{
					$v_address_type = strtolower($address_type_r['s_address_type']);
					
					$address_attribs_provided[$v_address_type] = FALSE;
					
					$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $user_r['type'], 'update', TRUE);
					if($attr_results)
					{
						while($addr_attribute_type_r = db_fetch_assoc($attr_results))
						{
							$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'],$addr_attribute_type_r['order_no']);
					
							$HTTP_VARS[$v_address_type][$fieldname] = filter_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname]);
							if(is_empty_attribute($addr_attribute_type_r['s_attribute_type'], $HTTP_VARS[$v_address_type][$fieldname])!==FALSE)
							{
								$address_attribs_provided[$v_address_type] = TRUE;
								
								if(!validate_item_input_field($addr_attribute_type_r, $HTTP_VARS[$v_address_type][$fieldname], $errors))
								{
									$is_address_validated = FALSE;
								}
							}
						}
						db_free_result($attr_results);
					}//if($addr_results)
				}
				db_free_result($addr_results);
			}//if($addr_results)
			
			if($is_address_validated)
			{
				// Do not allow update with illegal theme!
				if(get_opendb_config_var('user_admin', 'user_themes_support')===FALSE || !is_legal_user_theme($HTTP_VARS['uid_theme']))
				{
					$HTTP_VARS['uid_theme'] = FALSE; // Do not update theme!
				}
				
				// Do not allow update with illegal language.			
				if(get_opendb_config_var('user_admin', 'user_language_support')===FALSE || !is_exists_language($HTTP_VARS['uid_language']))
				{
					$HTTP_VARS['uid_language'] = NULL;
				}
			
				if(update_user($HTTP_VARS['user_id'], $HTTP_VARS['fullname'], $HTTP_VARS['uid_language'], $HTTP_VARS['uid_theme'], FALSE))
				{
					// No errors recorded at this stage.
					$errors = NULL;
				
					$address_creation_success = TRUE;
					$address_type_sequence_number_r = NULL;
					
					$addr_results = fetch_user_address_type_rs($user_r['user_id'], $user_r['type'], TRUE);
					if($addr_results)
					{
						while($address_type_r = db_fetch_assoc($addr_results))
						{
							$v_address_type = strtolower($address_type_r['s_address_type']);
							
							$address_creation_success = TRUE;
							
							// address does not currently exist, so create it.
							if($address_type_r['sequence_number'] === NULL)
							{
								if($address_attribs_provided[$v_address_type]!==FALSE)
								{
									$new_sequence_number = insert_user_address($user_r['user_id'], $address_type_r['s_address_type'], $HTTP_VARS[$v_address_type]['public_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']);
									if($new_sequence_number !== FALSE)
									{
										$address_type_r['sequence_number'] = $new_sequence_number;
									}
									else
									{
										$address_creation_success = FALSE;
									}
								}
							}
							else
							{
								$new_sequence_number = update_user_address($address_type_r['sequence_number'], $HTTP_VARS[$v_address_type]['public_address_ind'], $HTTP_VARS[$v_address_type]['borrow_address_ind']);
							}
							
							if($address_creation_success!==FALSE)
							{
								if($address_attribs_provided[$v_address_type]!==FALSE)
								{
									$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $user_r['type'], 'update', TRUE);
									if($attr_results)
									{
										while($addr_attribute_type_r = db_fetch_assoc($attr_results))
										{
											$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);
											
											if(is_lookup_attribute_type($addr_attribute_type_r['s_attribute_type']))
											{
												$lookup_value_r = NULL;
												if(is_array($HTTP_VARS[$v_address_type][$fieldname]))
													$lookup_value_r =& $HTTP_VARS[$v_address_type][$fieldname];
												else if(strlen(trim($HTTP_VARS[$v_address_type][$fieldname]))>0)
													$lookup_value_r[] = $HTTP_VARS[$v_address_type][$fieldname];
								
												$user_addr_attr_lookup_val_r = fetch_user_address_lookup_attribute_val(
																$address_type_r['sequence_number'], 
																$addr_attribute_type_r['s_attribute_type'], 
																$addr_attribute_type_r['order_no']);
												
												if($user_addr_attr_lookup_val_r !== FALSE)
												{
													if(is_not_empty_array($lookup_value_r)) // insert/update mode
													{
														if(!update_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $lookup_value_r))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
													else
													{
														if(!delete_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
												}
												else if(is_not_empty_array($lookup_value_r))
												{
													if(!insert_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $lookup_value_r))
													{
														$db_error = db_error();
														$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
														$address_creation_success = FALSE;
													}
												}
											}
											else
											{
												$attribute_val = fetch_user_address_attribute_val(
																$address_type_r['sequence_number'], 
																$addr_attribute_type_r['s_attribute_type'],
																$addr_attribute_type_r['order_no']);
												
												if($attribute_val!==FALSE)
												{
													if(strlen($HTTP_VARS[$v_address_type][$fieldname])>0)
													{
														if(!update_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $HTTP_VARS[$v_address_type][$fieldname]))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
													else
													{
														if(!delete_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
												}
												else
												{
													if(strlen($HTTP_VARS[$v_address_type][$fieldname])>0)
													{
														if(!insert_user_address_attributes($address_type_r['sequence_number'], $addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no'], $HTTP_VARS[$v_address_type][$fieldname]))
														{
															$db_error = db_error();
															$errors[] = array('error'=>get_opendb_lang_var('user_address_not_updated'),'detail'=>$db_error);
															$address_creation_success = FALSE;
														}
													}
												}
											}
										}//while($addr_attribute_type_r = db_fetch_assoc($attr_results))
										db_free_result($attr_results);
									}//if($attr_results)
								}//if($address_attribs_provided[$v_address_type]!==FALSE)
								else
								{
									// existing address, we want to get rid of it here
									if($address_type_r['sequence_number']!==NULL)
									{
										if(delete_user_address_attributes($address_type_r['sequence_number']))
										{
											delete_user_address($address_type_r['sequence_number']);
										}
									}
								}
							}//if($address_creation_success!==FALSE)
						}
						db_free_result($addr_results);
					}//if($addr_results)
						
					if($address_creation_success!==FALSE)
					{
						return TRUE;
					}
					else
					{
						// address update failed.
						return FALSE;
					}
				}//if(update_user($HTTP_VARS['user_id'], $HTTP_VARS['fullname'], $HTTP_VARS['location'], $HTTP_VARS['email'], $HTTP_VARS['uid_language'], $HTTP_VARS['uid_theme'], FALSE)) 
				else
				{
					$db_error = db_error();
					$errors[] = array('error'=>get_opendb_lang_var('user_not_updated', 'user_id', $HTTP_VARS['user_id']),'detail'=>$db_error);
					return FALSE;
				}
			}//if($is_address_validated)
			else
			{
				return FALSE;
			}
		}
		else // not all required information specified
		{
			return FALSE;
		}
	}
	else
	{
		$errors[] = array('error'=>get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id']));
		return FALSE;
	}		
}

function handle_user_password_change($user_id, $HTTP_VARS, &$errors)
{
    $user_r = fetch_user_r($user_id);
	if(is_not_empty_array($user_r))
	{
    	// If at least one password specified, we will try to perform update.
		if(strlen($HTTP_VARS['pwd'])>0 || strlen($HTTP_VARS['confirmpwd'])>0)
		{
			if(get_opendb_config_var('user_admin', 'user_passwd_change_allowed')!==FALSE || is_user_admin(get_opendb_session_var('user_id'),get_opendb_session_var('user_type')))
			{
				if ($HTTP_VARS['pwd'] != $HTTP_VARS['confirmpwd'])
				{
					$error = get_opendb_lang_var('passwds_do_not_match');
				}
				else if(strlen($HTTP_VARS['pwd'])==0)
				{
					$error = get_opendb_lang_var('passwd_not_specified');
				}
				else
				{
					if(update_user_passwd($user_id, $HTTP_VARS['pwd']))
					{
					    return TRUE;
					}
					else
					{
						$error = db_error();
						return FALSE;
					}
				}
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
		    $error = get_opendb_lang_var('passwd_not_specified');
		    return FALSE;
		}
	}
	else
	{
		return FALSE;
	}
}

/*
* The Deactivate process will not delete any records.  All pending reservations
* for the users items, and made by the user will be cancelled, but thats it.
*/
function handle_user_deactivate($user_id, $HTTP_VARS, &$errors)
{
	if(get_opendb_config_var('user_admin', 'user_deactivate_support') === TRUE)
	{
		if($user_id == get_opendb_session_var('user_id'))
		{
			$errors[] = array('error'=>get_opendb_lang_var('cannot_deactivate_yourself'),'detail'=>'');
			return FALSE;
		}
		else if(fetch_my_borrowed_item_cnt($user_id)>0)
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_borrows_not_deactivated'),'detail'=>'');
			return FALSE;
		}
		else if(fetch_owner_borrowed_item_cnt($user_id)>0)
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_owner_borrows_not_deactivated'),'detail'=>'');
			return FALSE;
		}
		else if($HTTP_VARS['confirmed'] == 'true')
		{
			// Cancel all reservations.
			$results = fetch_owner_reserved_item_rs($user_id);
			if($results)
			{
				while($borrowed_item_r = db_fetch_assoc($results))
				{
					cancel_reserve_item($borrowed_item_r['sequence_number']);
				}
				db_free_result($results);
			}
	
			$results = fetch_my_reserved_item_rs($user_id);
			if($results)
			{
				while($borrowed_item_r = db_fetch_assoc($results))
				{
					cancel_reserve_item($borrowed_item_r['sequence_number']);
				}
				db_free_result($results);
			}

			// deactivate user.
			if(deactivate_user($user_id))
				return TRUE;
			else
				return FALSE;
		}
		else if($HTTP_VARS['confirmed'] != 'false')// confirmation required.
		{
			return "__CONFIRM__";
		}
		else 
		{
			return "__ABORTED__";				
		}
	}
	else // if(get_opendb_config_var('user_admin', 'user_deactivate_support') === TRUE)
	{
		$errors[] = array('error'=>get_opendb_lang_var('user_deactivate_not_supported'),'detail'=>'');
		return FALSE;
	}
}

function handle_user_activate($user_id, $HTTP_VARS, &$errors)
{
	if($HTTP_VARS['confirmed'] == 'true')
	{
		if(activate_user($user_id))
			return TRUE;
		else
			return FALSE;
	}
	else if($HTTP_VARS['confirmed'] != 'false')// confirmation required.
	{
		return "__CONFIRM__";
	}
	else 
	{
		return "__ABORTED__";				
	}
}

/*
* The validation for deleting a user, is exactly the same as for 
* deactivating one, except where this function actually returns
* __DEACTIVATE__, which indicates that the user is available for
* deactivation.
* 
* @param $op - 'delete' or 'deactivate' if delete is not possible because of
* borrowed items and/or item_instance records.
* 
*/
function handle_user_delete($user_id, $HTTP_VARS, &$errors)
{
	if(get_opendb_config_var('user_admin', 'user_delete_support') === TRUE)
	{
		// We need to ensure that the user does not have any titles or borrowed/reserved items.
		if($user_id == get_opendb_session_var('user_id'))
		{
			$errors[] = array('error'=>get_opendb_lang_var('cannot_delete_yourself'),'detail'=>'');
			return FALSE;
		}
		else if(fetch_my_borrowed_item_cnt($user_id)>0)
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_borrows_not_deleted'),'detail'=>'');
			return FALSE;
		}
		else if(fetch_owner_borrowed_item_cnt($user_id)>0)
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_owner_borrows_not_deleted'),'detail'=>'');
			return FALSE;
		}
		
		// Now that we can proceed, we need to know whether we are performing a Delete or Deactivate
		// operation.
		if(get_opendb_config_var('user_admin', 'user_delete_with_reviews')!==TRUE && is_user_author($user_id, TRUE)>0)
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_reviews_not_deleted'),'detail'=>'');
			$confirm_operation = "__CONFIRM_DEACTIVATE__";
		}
		else if(get_opendb_config_var('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records')!==TRUE && 
					get_opendb_config_var('user_admin', 'user_delete_with_borrower_inactive_borrowed_items')!==TRUE && 
					(fetch_my_reserved_item_cnt($user_id)>0 || fetch_my_history_item_cnt($user_id)>0))
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_inactive_borrows_not_deleted'),'detail'=>'');
			$confirm_operation = "__CONFIRM_DEACTIVATE__";
		}
		else if(get_opendb_config_var('item_input', 'allow_delete_with_closed_or_cancelled_borrow_records')!==TRUE && 
					get_opendb_config_var('user_admin', 'user_delete_with_owner_inactive_borrowed_items')!==TRUE && 
					(fetch_owner_reserved_item_cnt($user_id)>0 || fetch_owner_history_item_cnt($user_id)>0))
		{
			$errors[] = array('error'=>get_opendb_lang_var('user_with_owner_inactive_borrows_not_deleted'),'detail'=>'');
			$confirm_operation = "__CONFIRM_DEACTIVATE__";
		}
		else // User can be completely deleted
		{
			$confirm_operation = "__CONFIRM__";
		}
				
		// If already confirmed operation.
		if($HTTP_VARS['confirmed'] == 'true')
		{
			// Cancel all reservations.
			$results = fetch_owner_reserved_item_rs($user_id);
			if($results)
			{
				while($borrowed_item_r = db_fetch_assoc($results))
				{
					cancel_reserve_item($borrowed_item_r['sequence_number']);
				}
				db_free_result($results);
			}
	
			$results = fetch_my_reserved_item_rs($user_id);
			if($results)
			{
				while($borrowed_item_r = db_fetch_assoc($results))
				{
					cancel_reserve_item($borrowed_item_r['sequence_number']);
				}
				db_free_result($results);
			}
				
			// We are proceeding with the delete operation here.
			if($confirm_operation == "__CONFIRM__")
			{
				// Delete all user reviews.
				if(is_user_author($user_id))
				{
					delete_author_reviews($user_id);
				}
				
				// Delete all inactive borrowed items
				delete_my_inactive_borrowed_items($user_id);
				
				// If no items, we can proceed to delete user.
				$results = fetch_owner_item_instance_rs($user_id);
				if($results)
				{
					// For each item, check if there are any dependencies.  If not, delete the
					// item_instance, and the item itself if no other instances.  Delete all
					// reviews, if this is the only dependency.
					while($item_r = db_fetch_assoc($results))
					{
						// The handle_item_delete does all the required checking before proceeding to
						// delete the item, so call - and programmatically set the 'confirmed = true' setting.
						if(!handle_item_delete($item_r, fetch_status_type_r($item_r['s_status_type']), array('confirmed'=>'true'), $error, TRUE))
						{
							$errors[] = $error;
						}
					}
					db_free_result($results);
				}
			}//if($confirm_operation == "__CONFIRM__")
			
			if($confirm_operation == "__CONFIRM_DEACTIVATE__" || 
						is_user_author($user_id, TRUE) || // If user has any dependent records left we cannot continue.
						is_exists_borrower_borrowed_item($user_id) || 
						is_exists_item_instance_with_owner($user_id))
			{
				if(deactivate_user($user_id))
					return "__DEACTIVATED__";
				else
					return FALSE;
			}
			else // user can be completely deleted.
			{
				// delete user addresses first.
				if(delete_user_addresses($user_id))
				{
					if(delete_user($user_id))
						return TRUE;
					else
					{
						$db_error = db_error();
						$errors = array('error'=>get_opendb_lang_var('user_not_deleted'),'detail'=>$db_error);
						return FALSE;
					}
				}
				else
				{
					$db_error = db_error();
					$errors = array('error'=>get_opendb_lang_var('user_not_deleted'),'detail'=>$db_error);
					return FALSE;
				}
			}
		}
		else if($HTTP_VARS['confirmed'] != 'false')// confirmation required.
		{
			return $confirm_operation;
		} 
		else 
		{
			return "__ABORTED__";				
		}
	}
	else // if(get_opendb_config_var('user_admin', 'user_delete_support') === TRUE)
	{
		$errors = array('error'=>get_opendb_lang_var('user_delete_not_supported'),'detail'=>'');
		return FALSE;
	}
}

function send_signup_info_to_admin($HTTP_VARS, &$errors)
{
	global $PHP_SELF;

	$http_vars['user_id'] = $HTTP_VARS['user_id'];
	$http_vars['fullname'] = $HTTP_VARS['fullname'];
	$http_vars['user_type'] = $HTTP_VARS['user_type'];
	$http_vars['pwd'] = $HTTP_VARS['pwd'];
	$http_vars['confirmpwd'] = $HTTP_VARS['pwd'];

	$user_info_lines =
	    get_opendb_lang_var('userid').": ".$HTTP_VARS['user_id'].
		"\n".get_opendb_lang_var('fullname').": ".$HTTP_VARS['fullname'].
		"\n".get_opendb_lang_var('user_type').": ".get_usertype_prompt($HTTP_VARS['user_type']).
		"\n".get_opendb_lang_var('user_theme').": ".$HTTP_VARS['uid_theme'];

	$email_addr = NULL;

	$addr_results = fetch_address_type_rs($HTTP_VARS['user_type'], TRUE);
	if($addr_results)
	{
		while($address_type_r = db_fetch_assoc($addr_results))
		{
			$address_type = strtolower($address_type_r['s_address_type']);
			$attr_results = fetch_address_type_attribute_type_rs($address_type_r['s_address_type'], $HTTP_VARS['user_type'], 'update', TRUE);
			if($attr_results)
			{
				while($addr_attribute_type_r = db_fetch_assoc($attr_results))
				{
					$fieldname = get_field_name($addr_attribute_type_r['s_attribute_type'], $addr_attribute_type_r['order_no']);

					$email_attribute = get_opendb_config_var('email', 'user_address_attribute');
					if(strlen($email_attribute)>0)
					{
						if(strcasecmp($addr_attribute_type_r['s_attribute_type'], $email_attribute) === 0)
							$email_addr = $HTTP_VARS[$address_type][$fieldname];
					}

					// may have to change this if statement, if fieldname will contain array, instead of scalar value
					if(is_not_empty_array($HTTP_VARS[$address_type][$fieldname]) ||
							(!is_array($HTTP_VARS[$address_type][$fieldname]) && strlen($HTTP_VARS[$address_type][$fieldname])>0))
					{
						$http_vars[$address_type][$fieldname] = $HTTP_VARS[$address_type][$fieldname];

						if(is_not_empty_array($HTTP_VARS[$address_type][$fieldname]))
						{
							$value = '';
							for($i=0; $i<count($HTTP_VARS[$address_type][$fieldname]); $i++)
							{
								if(strlen($value)>0)
									$value .= ',';

								$value .= $HTTP_VARS[$address_type][$fieldname][$i];
							}
						}
						else
						{
							$value = $HTTP_VARS[$address_type][$fieldname];
						}
						$user_info_lines .= "\n".$addr_attribute_type_r['prompt'].": ".$value;
					}
				}
				db_free_result($attr_results);
			}//if($attr_results)
		}
		db_free_result($addr_results);
	}//if($addr_results)

	$http_vars['email_user'] = 'Y';
	$activate_url = get_site_url().'user_admin.php?op=activate&user_id='.$HTTP_VARS['user_id'];
	$delete_url = get_site_url().'user_admin.php?op=delete&user_id='.$HTTP_VARS['user_id'];

	$message =
		get_opendb_lang_var(
			'new_account_email',
			array(
			'admin_name'=>get_opendb_lang_var('site_administrator', 'site', get_opendb_config_var('site', 'title')),
				'user_info'=>$user_info_lines,
				'site'=>get_opendb_config_var('site', 'title'),
				'activate_url'=>$activate_url,
				'delete_url'=>$delete_url));

	// if from address not provided, what the fuck do we do?!
	if(strlen($email_addr)==0)
	{
		$email_addr = get_opendb_config_var('email', 'noreply_address');
	}

	return send_email_to_site_admins($email_addr, get_opendb_lang_var('new_account'), $message, $errors);
}

if(is_site_enabled())
{
	if(is_opendb_valid_session() || $HTTP_VARS['op'] == 'signup')
	{ 
	    if ( $HTTP_VARS['op'] == 'signup' && $HTTP_VARS['op2'] == 'gfx_code_check' && is_numeric($HTTP_VARS['gfx_random_number']))
	    {
	        secretimage($HTTP_VARS['gfx_random_number']);
	    }
		else if ( $HTTP_VARS['op'] == 'signup' ||
					($HTTP_VARS['user_id'] === get_opendb_session_var('user_id') && 
					$HTTP_VARS['op'] != 'insert' && 
					$HTTP_VARS['op'] != 'new_user' && 
					$HTTP_VARS['op'] != 'deactivate' && 
					is_user_allowed_to_edit_info(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) ) || 
					is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')) ) // Can only access if admin or own user (not guest) record.
		{
		    if($HTTP_VARS['op'] == 'new_user')
			{
				echo _theme_header(get_opendb_lang_var('add_new_user'));
				echo("<h2>".get_opendb_lang_var('add_new_user')."</h2>");
				
				if(is_usertype_valid($HTTP_VARS['user_type']))
				{
					echo(get_user_input_form(NULL, $HTTP_VARS));
				}
				else
				{
					echo(get_new_user_usertype_input_form($HTTP_VARS, get_user_types_r(), 'N'));
				}
			}
			else if ($HTTP_VARS['op'] == 'edit')
			{
				if($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('my_info');
				else
					$page_title = get_opendb_lang_var('user_info');

				echo _theme_header($page_title);
				echo("<h2>".$page_title."</h2>");

				$user_r = fetch_user_r($HTTP_VARS['user_id']);
				if(is_not_empty_array($user_r))
				{
					echo(get_user_input_form($user_r, $HTTP_VARS));
				}
				else //user not found.
				{
					echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
				}
			}
			else if($HTTP_VARS['op'] == 'change_password')
			{
			    if($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('change_my_password');
				else
					$page_title = get_opendb_lang_var('change_user_password');

				echo _theme_header($page_title);
				echo("<h2>".$page_title."</h2>");
				
				if(get_opendb_config_var('user_admin', 'user_passwd_change_allowed')!==FALSE || is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
				{
					$user_r = fetch_user_r($HTTP_VARS['user_id']);
					if(is_not_empty_array($user_r))
					{
						echo(get_user_password_change_form($user_r, $HTTP_VARS));
					}
					else //user not found.
					{
						echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
					}
				}
				else
				{
					echo format_error_block(get_opendb_lang_var('operation_not_available'));
				}					
			}
			else if($HTTP_VARS['op'] == 'signup')
			{
				if(get_opendb_config_var('login.signup', 'enable')!==FALSE)
				{
					$signup_restrict_usertypes = get_opendb_config_var('login.signup', 'restrict_usertypes');

					// if no array defined, enforce default choices
					if(is_empty_array($signup_restrict_usertypes))
					{
						$signup_restrict_usertypes = array('B', 'N');
					}

					if(count($signup_restrict_usertypes) == 1)
					{
						// if only one usertype, then assign it to user_type variable, so we
						// can pretend it was user specified.
						$HTTP_VARS['user_type'] = $signup_restrict_usertypes[0];
					}

					// either valid usertype, or a single user type specified
					if(is_usertype_valid($HTTP_VARS['user_type']))
					{
						if($HTTP_VARS['op2'] == 'send_info')
						{
							$page_title = get_opendb_lang_var('new_site_account', 'site', get_opendb_config_var('site', 'title'));
							echo(_theme_header($page_title, is_show_login_menu_enabled()));
							echo("<h2>".$page_title."</h2>");

							// ensure the secret image codes check out
							if(is_numeric($HTTP_VARS['gfx_code_check']) &&
									is_numeric($HTTP_VARS['gfx_random_number']) &&
									is_secretimage_code_valid($HTTP_VARS['gfx_code_check'], $HTTP_VARS['gfx_random_number']))
							{
						   		$return_val = handle_user_insert($HTTP_VARS, $errors);
								if($return_val !== FALSE)
								{
									echo("\n<p class=\"success\">".get_opendb_lang_var('new_account_reply', 'site', get_opendb_config_var('site', 'title'))."</p>");
									if(send_signup_info_to_admin($HTTP_VARS, $errors))
									{
										echo("\n<p class=\"smsuccess\">".get_opendb_lang_var('new_account_admin_email_sent', 'site', get_opendb_config_var('site', 'title'))."</p>");
									}
									else
									{
										echo(format_error_block($errors));
									}
								}
								else // $return_val === FALSE
								{
         							echo(format_error_block($errors));
									echo(get_user_input_form(NULL, $HTTP_VARS));
								}
							}//is_secretimage_code_valid
							else
							{
							    echo(format_error_block(get_opendb_lang_var('invalid_verify_code')));
								echo(get_user_input_form(NULL, $HTTP_VARS));
							}
						}//if($HTTP_VARS['op2'] == 'send_info')
						else
						{
							$page_title = get_opendb_lang_var('new_site_account', 'site', get_opendb_config_var('site', 'title'));
							echo(_theme_header($page_title, is_show_login_menu_enabled()));
							echo("<h2>".$page_title."</h2>");
							echo(get_user_input_form(NULL, $HTTP_VARS));
						}
					}
					else
					{
						$page_title = get_opendb_lang_var('new_site_account', 'site', get_opendb_config_var('site', 'title'));
						echo(_theme_header($page_title, is_show_login_menu_enabled()));
						echo("\n<h2>".$page_title."</h2>");
						echo(get_new_user_usertype_input_form($HTTP_VARS, $signup_restrict_usertypes, NULL));
					}
				}
				else
				{
					echo _theme_header(get_opendb_lang_var('operation_not_available'), FALSE);
					echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
				}

			    $footer_links_r[] = array(url=>"login.php",text=>get_opendb_lang_var('return_to_login_page'));
			}
			else if($HTTP_VARS['op'] == 'insert') //inserting a new record.
			{
				echo _theme_header(get_opendb_lang_var('add_new_user'));
				echo("<h2>".get_opendb_lang_var('add_new_user')."</h2>");
				
				$return_val = handle_user_insert($HTTP_VARS, $errors);
				if($return_val !== FALSE)
				{
					echo("\n<p class=\"success\">".get_opendb_lang_var('user_added', 'user_id', $HTTP_VARS['user_id'])."</p>");
					if($HTTP_VARS['email_user'] == 'Y')
					{
					    $user_r = fetch_user_r($HTTP_VARS['user_id']);
					    if(is_valid_opendb_mailer())
					    {
							if(send_newuser_email($user_r, $HTTP_VARS['pwd'], $errors))
							{
							    echo("<p class=\"success\">".get_opendb_lang_var('welcome_email_sent', $user_r)."</p>");
							}
							else
							{
							    echo("<p class=\"error\">".get_opendb_lang_var('welcome_email_error', $user_r)."</p>");
								echo format_error_block($errors);
							}
					    }
					}//if($HTTP_VARS['email_user'] == 'Y')
					
					$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>($HTTP_VARS['user_id'] == get_opendb_session_var('user_id')?get_opendb_lang_var('edit_my_info'):get_opendb_lang_var('edit_user_info')));
				}
				else // $return_val === FALSE
				{
					echo format_error_block($errors);
					if(is_usertype_valid($HTTP_VARS['user_type']))
					{
                        $HTTP_VARS['op'] = 'new_user';
						echo(get_user_input_form(NULL, $HTTP_VARS));
					}
					else
					{
						echo(get_new_user_usertype_input_form($HTTP_VARS, get_user_types_r(), 'N'));
					}
				}
			}
			else if($HTTP_VARS['op'] == 'update')
			{
				if($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('my_info');
				else
					$page_title = get_opendb_lang_var('user_info');
				
				echo _theme_header($page_title);
				echo("<h2>".$page_title."</h2>");
				
				if(handle_user_update($HTTP_VARS, $errors))
				{
					// Any warnings that should be displayed.
					if($errors!==NULL)
						echo format_error_block($errors);
				}
				
				echo format_error_block($errors);
				
				$user_r = fetch_user_r($HTTP_VARS['user_id']);
				if(is_not_empty_array($user_r))
				{
                    $HTTP_VARS['op'] = 'edit';
					echo get_user_input_form($user_r,$HTTP_VARS);
				}
				else //user not found.
				{
					echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
				}
			}
			else if($HTTP_VARS['op'] == 'update_password')
			{
			    if($HTTP_VARS['user_id'] == get_opendb_session_var('user_id'))
					$page_title = get_opendb_lang_var('change_my_password');
				else
					$page_title = get_opendb_lang_var('change_user_password');

				echo _theme_header($page_title);
				echo("<h2>".$page_title."</h2>");

				if(handle_user_password_change($HTTP_VARS['user_id'], $HTTP_VARS, $error))
				{
				    echo("<p class=\"success\">".get_opendb_lang_var('passwd_changed')."</p>");
				}
				else
				{
					echo(format_error_block(array('error'=>get_opendb_lang_var('passwd_not_changed'), 'details'=>$error)));

					$user_r = fetch_user_r($HTTP_VARS['user_id']);
					if(is_not_empty_array($user_r))
					{
                        $HTTP_VARS['op'] = 'change_password';
						echo get_user_password_change_form($user_r,$HTTP_VARS);
					}
					else //user not found.
					{
						echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
					}
				}
			}
			else if($HTTP_VARS['op'] == 'delete')
			{
				echo _theme_header(get_opendb_lang_var('delete_user'));
				echo("<h2>".get_opendb_lang_var('delete_user')."</h2>");
	
				if(is_user_valid($HTTP_VARS['user_id']))
				{
					$return_val = handle_user_delete($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
					if($return_val === '__CONFIRM__')
					{
						echo get_op_confirm_form(
								$PHP_SELF,
								get_opendb_lang_var('confirm_user_delete', array('fullname'=>fetch_user_name($HTTP_VARS['user_id']),'user_id'=>$HTTP_VARS['user_id'])),
								$HTTP_VARS);
					}
					else if($return_val === '__CONFIRM_DEACTIVATE__')
					{
						echo format_error_block($errors);
						echo get_op_confirm_form(
										$PHP_SELF, 
										get_opendb_lang_var('confirm_user_delete_deactivate', array('fullname'=>fetch_user_name($HTTP_VARS['user_id']),'user_id'=>$HTTP_VARS['user_id'])),
										$HTTP_VARS);
					}
					else if($return_val === '__ABORTED__')
					{
						echo("<p class=\"success\">".get_opendb_lang_var('user_not_deleted')."</p>");
						$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>($HTTP_VARS['user_id'] == get_opendb_session_var('user_id')?get_opendb_lang_var('edit_my_info'):get_opendb_lang_var('edit_user_info')));
					}
					else if($return_val === '__DEACTIVATED__')
					{
						echo("<p class=\"success\">".get_opendb_lang_var('user_deactivated')."</p>");
						
						// this might explain why if a delete was requested only a deactivate occured
						if(is_array($errors))
						{
							echo format_error_block($errors);
						}
					}
					else if($return_val === TRUE)
					{
						echo("<p class=\"success\">".get_opendb_lang_var('user_deleted')."</p>");
					}
					else //if($return_val === FALSE)
					{
						echo format_error_block($errors);
					}
				}
				else
				{
					echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
				}				
			}
			else if($HTTP_VARS['op'] == 'deactivate')
			{
				echo _theme_header(get_opendb_lang_var('deactivate_user'));
				echo("<h2>".get_opendb_lang_var('deactivate_user')."</h2>");
					
				if(is_user_valid($HTTP_VARS['user_id']))
				{
					// user has to be currently active for a deactivation process to succeed
					if(is_user_active($HTTP_VARS['user_id']))
					{
						$return_val = handle_user_deactivate($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
						if($return_val === "__CONFIRM__")
						{
							echo get_op_confirm_form(
								$PHP_SELF, 
								get_opendb_lang_var('confirm_user_deactivate', array('fullname'=>fetch_user_name($HTTP_VARS['user_id']),'user_id'=>$HTTP_VARS['user_id'])),
								$HTTP_VARS);
						}
						else if($return_val === "__ABORTED__")
						{
							echo("<p class=\"success\">".get_opendb_lang_var('user_not_deactivated')."</p>");
							$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>get_opendb_lang_var('edit_user_info'));
						}
						else if($return_val === TRUE)
						{
							echo("<p class=\"success\">".get_opendb_lang_var('user_deactivated')."</p>");
						}
						else //if($return_val === FALSE)
						{
							echo format_error_block($errors);
							$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>get_opendb_lang_var('edit_user_info'));
						}
					}//if(is_user_active($HTTP_VARS['user_id']))
					else
					{
						echo format_error_block(get_opendb_lang_var('operation_not_available'));
					}
				}
				else
				{
					echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
				}
			}
			else if($HTTP_VARS['op'] == 'activate')
			{
				echo _theme_header(get_opendb_lang_var('activate_user'));
				echo("<h2>".get_opendb_lang_var('activate_user')."</h2>");

				if(is_user_valid($HTTP_VARS['user_id']))
				{
					// user must be deactivated in order for this process to continue.
					if(!is_user_active($HTTP_VARS['user_id']))
					{
					    // if newly activated user, then we want to reset password and
					    // send notification email.
					    $new_activated_user = is_user_not_activated($HTTP_VARS['user_id']);
					    
						$return_val = handle_user_activate($HTTP_VARS['user_id'], $HTTP_VARS, $errors);
						if($return_val === '__CONFIRM__')
						{
							echo get_op_confirm_form(
								$PHP_SELF, 
								get_opendb_lang_var('confirm_user_activate', array('fullname'=>fetch_user_name($HTTP_VARS['user_id']),'user_id'=>$HTTP_VARS['user_id'])),
								$HTTP_VARS);
						}
						else if($return_val === '__ABORTED__')
						{
							echo("<p class=\"success\">".get_opendb_lang_var('user_not_activated')."</p>");
						}
						else if($return_val === TRUE)
						{
							echo("<p class=\"success\">".get_opendb_lang_var('user_activated')."</p>");
							
							// reset password and send email
							if($new_activated_user)
							{
								$user_passwd = generate_password(8);
                                $pass_result = update_user_passwd($HTTP_VARS['user_id'], $user_passwd);
								if($pass_result===TRUE)
								{
								    $user_r = fetch_user_r($HTTP_VARS['user_id']);
								    if(is_valid_opendb_mailer())
								    {
										if(send_newuser_email($user_r, $user_passwd, $errors))
										{
										    echo("\n<p class=\"success\">".get_opendb_lang_var('welcome_email_sent', $user_r)."</p>");
										}
										else
										{
										    echo("<p class=\"error\">".get_opendb_lang_var('welcome_email_error', $user_r)."</p>");
											echo format_error_block($errors);
										}
								    }
								}
							}

							$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>get_opendb_lang_var('edit_user_info'));
						}
						else 
						{
							echo format_error_block($errors);
							$footer_links_r[] = array(url=>"$PHP_SELF?op=edit&user_id=".$HTTP_VARS['user_id'],text=>get_opendb_lang_var('edit_user_info'));
						}
					}//if(!is_user_active($HTTP_VARS['user_id']))
					else
					{
						echo format_error_block(get_opendb_lang_var('operation_not_available'));
					}
				}
				else
				{
					echo("<p class=\"error\">".get_opendb_lang_var('user_not_found', 'user_id', $HTTP_VARS['user_id'])."</p>");
				}
			}
			else if($HTTP_VARS['op'] == 'activate_users')
			{
			    echo _theme_header(get_opendb_lang_var('activate_users'));
				echo("<h2>".get_opendb_lang_var('activate_users')."</h2>");

				// handle activate of single user in the same way
				if(!is_array($HTTP_VARS['user_id_rs']) && is_user_valid($HTTP_VARS['user_id']))
				{
					$HTTP_VARS['user_id_rs'][] = $HTTP_VARS['user_id'];
					unset($HTTP_VARS['user_id']);
				}
				
				if(is_not_empty_array($HTTP_VARS['user_id_rs']))
				{
					// Remove $user_id's that are above the user type of the current user.
					$filtered_user_id_rs = NULL;

					reset($HTTP_VARS['user_id_rs']);
					while(list(,$uid) = each($HTTP_VARS['user_id_rs']))
					{
						if(in_array(fetch_user_type($uid), get_min_user_type_r(get_opendb_session_var('user_type'))))
						{
						    if(!is_user_active($uid))
								$filtered_user_id_rs[] = $uid;
						}
					}
			    }

				if(is_array($filtered_user_id_rs))
				{
				    // do not display confirm screen
				    $HTTP_VARS['confirmed'] = 'true';

					$success_userid_rs = NULL;
					$failure_userid_rs = NULL;
					
					reset($filtered_user_id_rs);
					while(list(,$userid) = each($filtered_user_id_rs))
					{
					    // if newly activated user, then we want to reset password and send notification email.
					    $new_activated_user = is_user_not_activated($userid);

                        $user_r = fetch_user_r($userid);
                        
						if(handle_user_activate($userid, $HTTP_VARS, $errors))
						{
							// reset password and send email
							if($new_activated_user)
							{
								$user_passwd  = generate_password(8);
	                            $pass_result = update_user_passwd($userid, $user_passwd);
								if($pass_result===TRUE)
								{
									if(is_valid_opendb_mailer())
									{
										if(send_newuser_email($user_r, $user_passwd, $errors))
										{
										    $user_r['_send_email_result'] = TRUE;
										}
										else
										{
										    $user_r['_send_email_result'] = FALSE;
										    $user_r['_send_email_errors'] = $errors;
										}
									}
								}
							}
							
							$success_userid_rs[] = $user_r;
						}
						else
						{
						    $failure_userid_rs[] = $user_r;
						}
					}
					
					if(is_array($success_userid_rs))
					{
					    echo("<p class=\"success\">".get_opendb_lang_var('users_activated')."</p>");
					    echo("<ul>");
					    for($i=0; $i<count($success_userid_rs); $i++)
					    {
					        echo("<li class=\"smsuccess\">".get_opendb_lang_var('user_activated_detail', $success_userid_rs[$i]));
					        
							if($success_userid_rs[$i]['_send_email_result']!==FALSE)
							{
					        	echo("<ul><li class=\"smsuccess\">".get_opendb_lang_var('welcome_email_sent', $success_userid_rs[$i])."</li></ul>");
							}
					        else
							{
								echo format_error_block(array('error'=>get_opendb_lang_var('welcome_email_error', $success_userid_rs[$i]), 'detail'=>$errors));
							}
							
							echo("</li>");
					    }
					    echo("</ul>");
					}
					
					if(is_array($failure_userid_rs))
					{
					    echo("<p class=\"error\">".get_opendb_lang_var('users_not_activated')."</p>");
					    echo("<ul>");
					    for($i=0; $i<count($failure_userid_rs); $i++)
					    {
					        echo("<li class=\"smerror\">\"".get_opendb_lang_var('user_activated_detail', $failure_userid_rs[$i])."</li>");
					    }
					    echo("</ul>");
					}
				}//if(!is_user_active($HTTP_VARS['user_id']))
   				else
				{
					echo format_error_block(get_opendb_lang_var('operation_not_available'));
				}
			}
			else //End of $HTTP_VARS['op'] checks
			{
				echo _theme_header(get_opendb_lang_var('operation_not_available'));
				echo("<p class=\"error\">".get_opendb_lang_var('operation_not_available')."</p>");
			}
			
			if($HTTP_VARS['listing_link'] === 'y' && is_array(get_opendb_session_var('user_listing_url_vars')))
			{
				$footer_links_r[] = array(url=>"user_listing.php?".get_url_string(get_opendb_session_var('user_listing_url_vars')),text=>get_opendb_lang_var('back_to_user_listing'));
			}
	
			echo format_footer_links($footer_links_r);
			echo _theme_footer();
		}
		else if(is_site_public_access_enabled())
		{
			// provide login at this point
			redirect_login($PHP_SELF, $HTTP_VARS);
		}
		else //not an administrator or own user.
		{
			echo _theme_header(get_opendb_lang_var('not_authorized_to_page'));
			echo("<p class=\"error\">".get_opendb_lang_var('not_authorized_to_page')."</p>");
			echo _theme_footer();
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