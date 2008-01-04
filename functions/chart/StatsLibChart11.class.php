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

include_once("./lib/libchart/libchart.php");

include_once("./functions/chart/StatsChart.class.php");

class StatsLibChart extends StatsChart
{
	var $libchart;
	
	function StatsLibChart($chartType, $width, $height) {
		parent::StatsChart($chartType, $width, $height);
		
		if($chartType == 'piechart')
			$this->libchart = new PieChart($width, $height);
		else
			$this->libchart = new VerticalChart($width, $height);
			
		$this->libchart->setLogo(NULL);
	}
	
	function addData($display, $value) {
		$this->libchart->addPoint(new Point($display, $value));	
	}
	
	function setTitle($title) {
		$this->libchart->setTitle($title);
	}
		
	function render($imgType)
	{
		$this->libchart->render($imgType);
	}
}
?>