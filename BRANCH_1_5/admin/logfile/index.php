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

/*
Secure the log directory via Web Server configuration.  For
example, this is my Apache 1.3.x configuration for Opendb.
I have secured the /opendb/log virtual_location with the
second directive.
	
Alias /opendb /opt/opendb
<Location /opendb>
  Options Indexes FollowSymLinks
  order deny,allow
  deny from all
  allow from all
</Location>
 
<Location "/opendb/log/">
order deny,allow
deny from all
</Location>
*/

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");

include_once("./functions/user.php");
include_once("./functions/datetime.php");

function _build_tooltip($prompt, $value)
{
	$value = str_replace("\"", "&quot;", $value);
	$value = str_replace("'", "\\'", $value);
	$value = str_replace("\n", "<br />", $value);
	
	return "<a href=\"#\" onmouseover=\"show_tooltip('".$value."','".$prompt."');\" onmouseout=\"return hide_tooltip();\">(?)</a>";
}

$logging_config_r = get_opendb_config_var('logging');

// Not much point continuing if no logfile.
if(strlen($logging_config_r['file'])>0)
{
	if(is_readable($logging_config_r['file']))
	{
		if($HTTP_VARS['op'] == 'download') // user wants to download usagelog
		{
			$filename = basename($logging_config_r['file']);
				
			header("Cache-control: no-store");
			header("Pragma: no-store");
			header("Expires: 0");
		    header("Content-disposition: attachment; filename=$filename");
			header("Content-type: application/octetstream");
			header("Content-Length: ".filesize($logging_config_r['file']));
                
			fpassthru2($logging_config_r['file']);

			//no theme here!
			return;
		}
		else if($HTTP_VARS['op'] == 'clear') // confirm with user to delete log
		{
			if($HTTP_VARS['confirmed'] == 'true')
			{
				$result = @unlink(get_opendb_config_var('logging', 'file'));
				if($result)
				{
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Usage log cleared');
				}
				else
				{
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Usage log not cleared');
				}
				
				// return to log file without POST params so can do immediate refresh of log
				http_redirect(basename($PHP_SELF)."?type=$ADMIN_TYPE");
			}
			else if($HTTP_VARS['confirmed'] != 'false')
			{
				echo("<h3>".get_opendb_lang_var('clear_usagelog')."</h3>\n");
				
				// hack to get the redirect to work
				$HTTP_VARS['mode'] = 'job';
				
				echo get_op_confirm_form(
					$PHP_SELF, 
					get_opendb_lang_var('confirm_clear_log'), 
					$HTTP_VARS);
			}
			else // confirmation required.
			{
				$HTTP_VARS['op'] = '';
				
				// return to log file without POST params so can do immediate refresh of log
				http_redirect(basename($PHP_SELF)."?type=$ADMIN_TYPE");
			}
		}
		else if($HTTP_VARS['op'] == 'backup')
		{
			if(strlen($logging_config_r['backup_ext_date_format'])>0)
				$mask = get_localised_timestamp($logging_config_r['backup_ext_date_format']);
			else
				$mask = get_localised_timestamp('DDMMYY');
			
			$filename = $logging_config_r['file'].'.'.get_localised_timestamp($mask);
			
			$result = @copy($logging_config_r['file'], $filename);
			if($result)
			{
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Usage log backed up', array($filename));
				$success[] = get_opendb_lang_var('backup_successful', 'filename', $filename);
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Usage log not backed up', array($filename));
				$errors[] = get_opendb_lang_var('backup_unsuccessful', 'filename', $filename);
			}
			
			$HTTP_VARS['op'] = '';
		}
		
		if($HTTP_VARS['op'] == '')
		{
			if(is_array($errors))
				echo(format_error_block($errors));
			else if(is_array($success))
				echo(format_error_block($success, 'information'));
			
			echo("<p>[ <a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=clear\">".get_opendb_lang_var('clear_usagelog')."</a> / "
				."<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=backup\">".get_opendb_lang_var('backup_usagelog')."</a> / "
				."<a href=\"$PHP_SELF?type=$ADMIN_TYPE&op=download&mode=job\">".get_opendb_lang_var('download_usagelog')."</a> ]</p>");
			
			$logfile = fopen($logging_config_r['file'], 'r');
			if($logfile)
			{
				// Might need this much time to display logfile
				@set_time_limit(600);
				
				echo("<table style=\"{width: 100%;}\">");
				echo("\n<tr class=\"navbar\">".
					"<th>".get_opendb_lang_var('log_type')."</th>".
					"<th>".get_opendb_lang_var('user')."</th>".
					"<th>".get_opendb_lang_var('user_ip')."</th>".
					"<th>".get_opendb_lang_var('date')."</th>".
					"<th>".get_opendb_lang_var('file')."</th>".
					"<th>".get_opendb_lang_var('function')."</th>".
					"<th>".get_opendb_lang_var('message')."</th>".
					"<th>".get_opendb_lang_var('parameters')."</th>".
					"</tr>");
				
				//$token_names = array('ip', 'datetime', 'type', 'function', 'parameters', 'message');
				while(($tokens = fget_tokenised_log_entry($logfile))!==FALSE)
				{
					if($tokens['admin_user_id'] != NULL)
					{
						$tokens['user_id'] .= ' ('.$tokens['admin_user_id'].')';
					}
					
					if($tokens['type'] == 'ERROR')
						$class = 'error';
					else if($tokens['type'] == 'WARN')
						$class = 'warn';
					else if($tokens['type'] == 'INFO')
						$class = 'success';
					
					if($tokens['parameters'] != NULL)
					{
						$tokens['parameters'] = _build_tooltip(get_opendb_lang_var('parameters'), $tokens['parameters']);
					}
					else
					{
						$tokens['parameters'] = '&nbsp;';
					}
						
					if($tokens['message'] != NULL)
					{
						if(strlen($tokens['message'])>100)
						{
							$tokens['message'] = _build_tooltip(get_opendb_lang_var('message'), $tokens['message']);
						}										
					}
					else
					{
						$tokens['message'] = '&nbsp;';
					}
					
					if($tokens['function'] == NULL)
						$tokens['function'] = '&nbsp;';
					
					$tokens['file'] = get_relative_opendb_filename($tokens['file']);
					
					echo("\n<tr class=\"logRow\">".
					"<td class=\"$class\">".$tokens['type']."</td>".
					"<td class=\"$class\">".$tokens['user_id']."</td>".
					"<td class=\"$class\">".$tokens['ip']."</td>".
					"<td class=\"$class dateTime\">".$tokens['datetime']."</td>".
					"<td class=\"$class\">".$tokens['file']."</td>".
					"<td class=\"$class\">".$tokens['function']."</td>".
					"<td class=\"$class\">".$tokens['message']."</td>".
					"<td class=\"$class\">".$tokens['parameters']."</td>".
					"</tr>");
				}
				fclose($logfile);
	
				echo("</table>");
			}
			else//if($logfile)
			{	
				// Should never heve happen, as we have already cheched if logfile 'is_readable'
				echo("<div class=\"error\">".$lang_var['undefined_error']."</div>");
			}
		}
	}
	else //if(is_readable(get_opendb_config_var('logging', 'file')))
	{
		echo("<div class=\"error\">".get_opendb_lang_var('no_logfile_found')."</div>");
	}
}
else//if(strlen(get_opendb_config_var('logging', 'file'))>0)
{
	echo("<div class=\"error\">".get_opendb_lang_var('no_logfile_defined')."</div>");
}
?>