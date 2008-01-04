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

//include_once("./functions/chart/OldStatsChart.class.php");
include_once("./functions/chart/StatsLibChart11.class.php");
//include_once("./functions/chart/StatsLibChart12.class.php");

function sort_data($data, $sortorder)
{
	if(is_array($data) && !empty($sortorder))
	{
		if($sortorder == 'asc')	
			asort($data);
		else
			arsort($data);
	}
	
	return $data;
}

function build_and_send_graph($data, $chartType, $title)
{
	if(strcasecmp(get_opendb_config_var('stats', $chartType.'_sort'),"asc")===0 || 
					strcasecmp(get_opendb_config_var('stats', $chartType.'_sort'),"desc")===0)
	{
		$sortorder = strtolower(get_opendb_config_var('stats', $chartType.'_sort'));
	}

	$data = sort_data($data, $sortorder);
	
	$imgType = strlen(get_opendb_config_var('stats', 'image_type'))>0? get_opendb_config_var('stats', 'image_type') : "png";

	$chart = new StatsLibChart($chartType, 600, 250);
	
	$chart->setTitle($title);
	while(list($x, $y) = each($data))
	{
		if($chartType == 'piechart')
			$chart->addData($x." ($y)", $y);
		else
			$chart->addData($x, $y);
	}
	
	$chart->render($imgType);
}
?>