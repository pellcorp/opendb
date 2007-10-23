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

	Note:
		This backup code is based on the "dump" feature from the phpMyAdmin project.
*/

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");
include_once("./functions/user.php");
include_once("./functions/widgets.php");
include_once("./functions/http.php");
include_once("./functions/utils.php");
include_once("./functions/datetime.php");

/**
	Get the content of $table as a series of INSERT statements.
*/	
function get_table_content($table, $crlf)
{
   	$result = db_query("SELECT * FROM $table");

	//prefix if required to table name before exporting.
	if(strlen(get_opendb_config_var('db_server', 'table_prefix'))>0)
	{
		$table = get_opendb_config_var('db_server', 'table_prefix').$table;
	}
	
   	$i = 0;
    while($row = db_fetch_row($result))
	{
		$table_list = "";
		for($j=0; $j<db_num_fields($result);$j++)
		{
			if(strlen($table_list)>0)
				$table_list .= ", ";
				
			$table_list .= db_field_name($result,$j);
		}
		$table_list = "(".$table_list.")";

		$schema_insert = "";	
		for($j=0; $j<db_num_fields($result);$j++)
		{
			if(strlen($schema_insert)>0)
				$schema_insert .= ", ";
				
			if(!isset($row[$j]))
				$schema_insert .= "NULL";
			else if($row[$j] != "")
			{
				$row[$j] = replace_newlines($row[$j]);
				
				// Escape normal addslashes: \', \", \\, \0 add to that \n
				$row[$j] = addcslashes($row[$j], "\'\"\\\0\n");
				$schema_insert .= "'".$row[$j]."'";
			}
			else
				$schema_insert .= "''";
		}
		
		$schema_insert = "INSERT INTO $table $table_list VALUES (".$schema_insert.")";

		// Get rid of newlines.
		$schema_insert = str_replace("\n","", $schema_insert);
		$schema_insert = str_replace("\r","", $schema_insert);
		
		echo(trim($schema_insert).";".$crlf);
				
		$i++;
	}
	return TRUE;
}

if (is_opendb_valid_session())
{
	// Only admin user is allowed to access this.
	if(is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		if($HTTP_VARS['op'] == 'export')
		{
			@set_time_limit(600);
			header("Cache-control: no-store");
			header("Pragma: no-store");
			header("Expires: 0");
			header("Content-disposition: attachment; filename=backup.sql");
			header("Content-type: application/octet-stream");
			
			$CRLF = get_user_browser_crlf();

			echo("# -------------------------------------------------------------".$CRLF);
		    echo("# ".get_opendb_title_and_version().$CRLF);
   			echo("# http://sourceforge.net/projects/opendb/".$CRLF);
		    echo("#".$CRLF);
   			echo("# ".get_opendb_lang_var('connected_to', get_opendb_config_var('db_server')).$CRLF);
			echo("# ".get_opendb_lang_var('db_backup_generated', 'date', get_localised_timestamp(get_opendb_config_var('listings', 'print_listing_datetime_mask'))).$CRLF);
		    echo("# -------------------------------------------------------------".$CRLF);

			// special all tables option reset $HTTP_VARS['tables'] array as a result
			if(strcasecmp($HTTP_VARS['all_tables'],'y')===0)
			{
				unset($HTTP_VARS['tables']);
				
                   $opendb_tables_r = fetch_opendb_table_list_r();
				while(list(,$value) = each($opendb_tables_r))
				{
					$HTTP_VARS['tables'][] = $value;
				}
			}
			
			@reset($HTTP_VARS['tables']);
			while (list(,$table) = @each($HTTP_VARS['tables']))
			{
				echo $CRLF."#".$CRLF;
				echo "# ".get_opendb_lang_var('dumping_data_for_table', 'table', $table).$CRLF;
				echo "#".$CRLF.$CRLF;

				get_table_content($table, $CRLF);
			}			
		}
		else //if($HTTP_VARS['op'] == 'export')
		{
			echo("<h3>Which tables should be backed up?</h3>");
			
			echo("<form method=\"POST\" action=\"$PHP_SELF\">"
				."<input type=hidden name=\"type\" value=\"$ADMIN_TYPE\">"
				."<input type=hidden name=\"op\" value=\"export\">"
				."<input type=hidden name=\"mode\" value=\"job\">");
			
			echo("<table>\n<tr>");
			$count=0;

               $opendb_tables_r = fetch_opendb_table_list_r();
			while(list(,$table) = each( $opendb_tables_r ))
			{
				// the cache tables cannot be backed up as they might contain
				// binary data, which we don't yet support.
				if(!ends_with($table, '_cache'))
				{
					if($count>=2)
					{
						echo("\n</tr>\n<tr>");
						$count=0;
					}

                    $checked = FALSE;
					if(strcasecmp(substr($table,0,2),'s_')!==0 &&
								$table != 'import_cache' &&
								$table != 'file_cache' &&
                                $table != 'php_session')
					{
						$checked = TRUE;
					}

					echo("<td><input type=checkbox name=\"tables[]\" value=\"$table\" ".($checked?"CHECKED":"").">$table</td>");
					
					$count++;
				}
			}
			
			if($count>0 && $count < 2) // $count == 1 would have been easier, but might change to 3 columns, so logic is easier to maintain
			{
				for($i=$count; $i<2; $i++)
				{
					echo("<td>&nbsp;</td>");
				}					
			}
			
			echo("</tr>");
			echo("</table>");
			
			echo("<ul class=\"actionButtons\">".
				"<li><input type=button value=\"".get_opendb_lang_var('check_all')."\" onClick=\"setCheckboxes(this.form, 'tables[]', true);\"></li>".
				"<li><input type=button value=\"".get_opendb_lang_var('uncheck_all')."\" onClick=\"setCheckboxes(this.form, 'tables[]', false);\"></li>".
				"<li><input type=reset value=\"".get_opendb_lang_var('reset')."\"></li>".
				"</ul>");

			echo("<input type=\"submit\" value=\"".get_opendb_lang_var('backup_database')."\">
				</form>");
		}
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
