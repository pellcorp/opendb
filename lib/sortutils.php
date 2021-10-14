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

/**
	This function will return a complete sort function
	to be used with usort and as a second argument to 
	create_argument (Ensure the first parameter to
	create_function is '$a,$b':

		usort($item_rs, create_function('$a,$b', get_usort_function('title ASC, owner_id ASC, s_item_type DESC')));

		array(
			array(title=>'Crow, The',owner_id=>'jpell',s_item_type=>'DVD'),
			array(title=>'Rambo',owner_id=>'martin',s_item_type=>'DVD'),
			array(title=>'Good Will Hunting',owner_id=>'lucy',s_item_type=>'VHS')
			);
			
	The call to get_usort_function('title ASC, owner_id ASC, s_item_type DESC'), results in the following
	generated function:
		$title=strcmp($a[title], $b[title]); if($title==0){$owner_id=strcmp($a[owner_id], $b[owner_id]); if($owner_id==0){return strcmp($b[s_item_type], $a[s_item_type]);}else{return $owner_id;}}else{return $title;}

	The order_by_clause should look something like this:
		'item_id ASC, owner_id DESC, title ASC'
*/
function get_usort_function($order_by_clause) {
	// We get the options in reverse order, so we can properly nest them.
	$order_by_options_r = array_reverse ( explode ( ",", $order_by_clause ) );
	
	$first_element = TRUE;
	$retval = NULL;
	foreach ($order_by_options_r as $order_by) {
		if (strlen ( $retval ) > 0) {
			$inner_val = $retval;
		}
		
		$order_by = trim ( $order_by );
		$indexOfSpace = strpos ( $order_by, ' ' );
		if ($indexOfSpace !== FALSE) {
			$column = trim ( substr ( $order_by, 0, $indexOfSpace ) );
			$sortorder = trim ( substr ( $order_by, $indexOfSpace ) );
			
			if (strcasecmp ( $sortorder, "DESC" ) === 0)
				$comp = 'strcmp($b[\'' . $column . '\'], $a[\'' . $column . '\'])';
			else
				$comp = 'strcmp($a[\'' . $column . '\'], $b[\'' . $column . '\'])';
			
			if ($first_element) {
				$retval = 'return ' . $comp . ';';
				$first_element = FALSE;
			} else {
				$retval = '$' . $column . '=' . $comp . '; if($' . $column . '==0){' . $inner_val . '}else{return $' . $column . ';}';
			}
		}
	}
	
	return $retval;
}
?>
