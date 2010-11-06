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

// TODO - not currently working very well.
include_once("./functions/chart/StatsChart.class.php");

include_once("./lib/phplot/phplot.php");

class StatsChartImpl extends StatsChart
{
	var $phplot;
	var $data_values;
	
	function StatsChartImpl($chartType, $graphCfg) {
		parent::StatsChart($chartType, $graphCfg);
		
		$this->phplot = new PHPlot($this->width, $this->height); 

		$this->phplot->SetImageBorderType('plain');
		
		if($chartType == 'piechart')
		{
			$this->phplot->SetDataType('text-data-single');
			$this->phplot->SetPlotType('pie');
			$this->phplot->SetLabelScalePosition(0.35);
			//$this->phplot->SetDataColors(array('#BDC7F7', '#DCE6FB', 'yellow', 'red', 'blue', 'orange', 'purple'));
		}
		else
		{
			$this->phplot->SetPlotType('bar');
			$this->phplot->SetDataType('text-data');
			# Turn off X tick labels and ticks because they don't apply here:
			$this->phplot->SetXTickLabelPos('none');
			$this->phplot->SetXTickPos('none');
			
			$this->phplot->SetXLabelAngle(90);
			
			# Make sure Y=0 is displayed:
			$this->phplot->SetPlotAreaWorld(NULL, 0);
			
			# Y Tick marks are off, but Y Tick Increment also controls the Y grid lines:
			//$this->phplot->SetYTickIncrement(100);
			
			# Turn on Y data labels:
			$this->phplot->SetYDataLabelPos('plotin');
			
			# With Y data labels, we don't need Y ticks or their labels, so turn them off.
			$this->phplot->SetYTickLabelPos('none');
			$this->phplot->SetYTickPos('none');
		}
	}
	
	function addData($display, $value) {
		 $this->data_values[] = array($display, $value);
	}
	
	function setTitle($title) {
		$this->phplot->setTitle($title);
	}
		
	function render($imgType)
	{
		$this->phplot->SetDataValues($this->data_values);
		
		if($this->chartType == 'piechart') {
			reset($this->data_values);
			while(list(,$name) = each($this->data_values))
			{
				$this->phplot->SetLegend($name[0]);
			}
		}
				
		$this->phplot->SetFileFormat($imgType);
		$this->phplot->DrawGraph();
	}
}
?>
