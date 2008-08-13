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

include_once("./functions/OpenDbUpgrader.class.php");

class Upgrader_150b5_150b6 extends OpenDbUpgrader
{
	function Upgrader_150b5_150b6()
	{
		parent::OpenDbUpgrader(
						'1.5.0b5',
						'1.5.0b6',
						array(
							array('description'=>'Add new Book Genres'),
							array('description'=>'Add new Game Genres'),
							array('description'=>'Add new Movie Genres'),
							array('description'=>'Add new Game Regions'),
							array('description'=>'Tweak a bunch of attributes'),
						)
					);
	}

	function executeStep1($stepPart)
	{
		if(is_lookup_attribute_type('BOOKGENRE')) {
			return exec_install_sql_file("./install/upgrade/1.5.0b5/step1.sql", $errors);
		} else {
			return TRUE;
		}
	}
	
	function executeStep2($stepPart)
	{
		if(is_lookup_attribute_type('GAMEGENRE')) {
			return exec_install_sql_file("./install/upgrade/1.5.0b5/step2.sql", $errors);
		} else {
			return TRUE;
		}
	}
	
	function executeStep3($stepPart)
	{
		if(is_lookup_attribute_type('MOVIEGENRE')) {
			return exec_install_sql_file("./install/upgrade/1.5.0b5/step3.sql", $errors);
		} else {
			return TRUE;
		}
	}
	
	function executeStep4($stepPart)
	{
		if(is_lookup_attribute_type('GAMEREGION')) {
			return exec_install_sql_file("./install/upgrade/1.5.0b5/step4.sql", $errors);
		} else {
			return TRUE;
		}
	}
	
	
}
?>