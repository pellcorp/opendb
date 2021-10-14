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
require_once ("./lib/ODImage.class.php");
include_once("./lib/chart/StatsLibChart.class.php");

function sort_data_element($a_r, $b_r) {
	$a = $a_r ['value'];
	$b = $b_r ['value'];
	
	if ($a == $b) {
		return 0;
	}
	return ($a > $b) ? - 1 : 1;
}

function build_and_send_graph($data_rs, $chartType, $title) {
	$gdImage = new ODImage ( get_opendb_image_type() );
	$imgType = $gdImage->getImageType();
	unset ( $gdImage );
	
	$graphCfg = _theme_graph_config ();
	
	$chart = new StatsChartImpl ( $chartType, $graphCfg );
	
	$chart->setTitle ( $title );
	
	if (is_array ( $data_rs )) {
		
		usort ( $data_rs, "sort_data_element" );
		
		// only show first 12 items - otherwise graph will not render correctly.
		if ($chartType == 'piechart' && count ( $data_rs ) > 12)
			$data_rs = array_slice ( $data_rs, 0, 11 );
		
		reset ( $data_rs );
		foreach ($data_rs as $data_r) {
			if ($chartType == 'piechart')
				$chart->addData ( $data_r ['display'] . " (${data_r['value']})", $data_r ['value'] );
			else
				$chart->addData ( $data_r ['display'], $data_r ['value'] );
		}
	}
	
	$chart->render ( $imgType );
}
?>
