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
 */

if (!defined('OPENDB_ADMIN_TOOLS')) {
	die('Admin tools not accessible directly');
}

include_once("./lib/install.php");

/**
    Will display queries as they appear in the script, will
    not attempt to prefix, as this is only a print SQL
    operation.
 */
function echo_install_sql_file($sqlfile) {
	$errors = "";
	$lines = @file($sqlfile);
	if (!$lines) {
		echo ("<div class=\"error\">Error loading $filename $errors</div>");
		return false;
	}

	foreach ($lines as $line) {
		if (strlen(trim($line)) === 0)
			echo ("<br />");
		else {
			if (strpos($line, "#") === 0)
				echo ("<div class=\"code\"><b>$line</b></div>\n");
			else {
				echo ("<div class=\"code\">$line</div>");
			}
		}
	}
}

function display_patch_list($title, $patchdir) {
	global $ADMIN_TYPE;

	echo ("<h3>" . $title . "</h3>");
	$filelist = get_file_list('./admin/patch_facility/sql/' . $patchdir, 'sql');
	$sqllist = NULL;
	if (is_not_empty_array($filelist)) {
		for ($i = 0; $i < count($filelist); $i++) {
			$parsedfile_r = parse_file($filelist[$i]);
			$sqllist[] = array('sqlfile' => $filelist[$i], 'name' => initcap(str_replace('_', ' ', $parsedfile_r['name'])));
		}

		if (is_not_empty_array($sqllist)) {
			echo ("<table>");
			echo ("<tr class=\"navbar\">" . "<th>Patch</th>" . "<th>SQL File</th>" . "<th></th>" . "<th></th>" . "</tr>");

			for ($i = 0; $i < count($sqllist); $i++) {
				echo ("<tr class=\"oddRow\">" . "<td>" . $sqllist[$i]['name'] . "</td>" . "<td>" . $sqllist[$i]['sqlfile'] . "</td>" . "<td><a href=\"admin.php?type=$ADMIN_TYPE&op=previewsql&mode=job&title=" . urlencode($sqllist[$i]['sqlfile']) . "&patchdir=$patchdir&sqlfile="
						. $sqllist[$i]['sqlfile'] . "&preview=true\" target=\"_new\">Preview</a></td>" . "<td><a href=\"admin.php?type=$ADMIN_TYPE&op=installsql&patchdir=$patchdir&sqlfile=" . $sqllist[$i]['sqlfile'] . "\">Install</a></td>" . "</tr>");
			}
			echo ("</table>");
		}
	}
}

function validate_sql_script($patchdir, $sqlfile) {
	$patchdir = basename($patchdir);
	$sqlfile = basename($sqlfile);

	$file = "./admin/patch_facility/sql/$patchdir/$sqlfile";

	if (file_exists($file))
		return $file;
	else
		return FALSE;
}

@set_time_limit(600);

if ($HTTP_VARS['op'] == 'previewsql') {
	echo ("<html>");
	echo ("\n<head>");
	echo ("\n<title>" . get_opendb_config_var('site', 'title') . " " . get_opendb_version() . " - " . $HTTP_VARS['title'] . "</title>");

	if (file_exists('./theme/default/style.css')) {
		echo ("\n<link rel=stylesheet type=\"text/css\" href=\"./theme/default/style.css\">");
	}

	echo ("\n<style type=\"text/css\">");
	echo ("\n.code { color: black; font-family: courier; font-size:small }");
	echo ("\n</style>");
	echo ("\n</head><body>");

	if (($file = validate_sql_script($HTTP_VARS['patchdir'], $HTTP_VARS['sqlfile'])) !== FALSE) {
		echo_install_sql_file($file);
	}

	echo ("\n</body></html>");
} else {
	if ($HTTP_VARS['op'] == 'installsql') {
		if (($file = validate_sql_script($HTTP_VARS['patchdir'], $HTTP_VARS['sqlfile'])) !== FALSE) {
			if (exec_install_sql_file($file, $error)) {
				echo ("<div class=\"smsuccess\">SQL script executed successfully.</div>");
			} else {
				echo format_error_block($error);
			}
		}
	}

	display_patch_list('Customise for Country', 'country');
	display_patch_list('Miscellaneous Updates', 'extras');
}
?>
