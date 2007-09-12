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

session_start();
if (is_opendb_valid_session())
{ 
	@set_time_limit(600);
	
	if (is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		if($HTTP_VARS['op'] == 'job')
		{
			echo("<div class=\"footer\">[<a href=\"$PHP_SELF?type=$ADMIN_TYPE\">Back to Main List</a>]</div>");
			
			if($HTTP_VARS['job'] == 'recalculate')
				echo("\n<h3>Recalculate Item Reviews</h3>");

			$jobObj->printJobProgressBar();
		}
		
		if($HTTP_VARS['op'] == '')
		{
			echo("<p>");
			echo("[<a href=\"admin.php?type=$ADMIN_TYPE&op=job&job=recalculate\">Recalculate Item Reviews</a>]&nbsp;");
			echo("</p>");
		}
	}
}//(is_opendb_valid_session())
?>