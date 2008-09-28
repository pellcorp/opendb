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

class Upgrader_150b4_150b5 extends OpenDbUpgrader
{
	function Upgrader_150b4_150b5()
	{
		parent::OpenDbUpgrader(
						'1.5.0b4',
						'1.5.0b5',
						array(
							array('description'=>'Fix UPC ID Definition'),
							array('description'=>'Setup User Item Interest functionality'),
						)
					);
	}
}
?>