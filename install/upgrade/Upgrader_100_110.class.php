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

class Upgrader_100_110 extends OpenDbUpgrader
{
	function Upgrader_100_110()
	{
		parent::OpenDbUpgrader(
						'1.0',
						'1.1',
						array(
							array('description'=>'New Related Status Type'),
							array('description'=>'Transfer Linked Items')
						)
					);
	}
	
	function executeStep1($stepPart)
	{
		exec_install_sql_file("./admin/s_status_type/sql/R-Related.sql", $errors);
	}
	
	/**
	 * Create a item_instance for every item that has a parent_id set.  Then create
	 * a item_instance_relationship to link it to all the parent item instances.  Finally
	 * drop the parent_id column.
	 */
	function executeStep2($stepPart)
	{
		$results = db_query(
					"SELECT ii.item_id, ii.instance_no, ii.owner_id, i.id AS related_item_id
					FROM	item i,
							item_instance ii 
					WHERE	i.parent_id = ii.item_id AND 
							i.parent_id IS NOT NULL");
		if($results)
		{
			while($item_instance_r = db_fetch_assoc($results))
			{
				insert_item_instance(
						$item_instance_r['related_item_id'], 
						1, //$instance_no, 
						'R', //$s_status_type, 
						NULL, //$status_comment, 
						NULL, //$borrow_duration, 
						$item_instance_r['owner_id']);
					
				insert_item_instance_relationship(
							$item_instance_r['item_id'], 
							$item_instance_r['instance_no'], 
							$item_instance_r['related_item_id'], 
							1);
			}
			db_free_result($results);
		}
	}
}
?>