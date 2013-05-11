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

function test_extension($extension)
{
	$db_functions = array(
				$extension.'_connect', 
				$extension.'_select_db', 
				$extension.'_error',
				$extension.'_errno',  
				$extension.'_query', 
				$extension.'_free_result', 
				$extension.'_fetch_assoc',
				$extension.'_num_rows', 
				$extension.'_num_fields',
				$extension.'_affected_rows');

	for($i=0; $i<count($db_functions); $i++)
	{
		if(function_exists($db_functions[$i]))
			echo 'Function \''.$db_functions[$i].'\' exists<br>';
		else
			echo 'Function \''.$db_functions[$i].'\' DOES NOT exist<br>';
	}
}
?>

<html>
<head>
<title>MySQL Extension Test</title>
</head>
<body>
<h1>MySQL Extension Test</h1>

<h2>MYSQLi Extension</h2>
<?php test_extension('mysqli'); ?>

<h2>MYSQL Extension</h2>
<?php test_extension('mysql'); ?>

</body>
</html>