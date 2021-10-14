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

include_once("./lib/libchart/classes/libchart.php");

include_once("./lib/chart/StatsChart.class.php");

class StatsChartImpl extends StatsChart {
	var $libchart;
	var $dataSet;

	function __construct($chartType, $graphCfg) {
		parent::__construct($chartType, $graphCfg);

		if ($chartType == 'piechart')
			$this->libchart = new PieChart($this->width, $this->height);
		else
			$this->libchart = new VerticalBarChart($this->width, $this->height);

		$this->libchart->getPlot()->setLogoFileName(NULL);

		$this->dataSet = new XYDataSet();
	}

	function addData($display, $value) {
		$this->dataSet->addPoint(new Point($display, $value));
	}

	function setTitle($title) {
		$this->libchart->setTitle($title);
	}

	function render($imgType) {
		$this->libchart->setDataSet($this->dataSet);
		$this->libchart->render();
	}
}
?>
