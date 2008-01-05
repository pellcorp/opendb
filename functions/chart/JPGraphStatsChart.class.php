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

include_once("./functions/chart/StatsChart.class.php");

include_once("./lib/jpgraph/jpgraph.php");
include_once("./lib/jpgraph/jpgraph_pie.php");
include_once("./lib/jpgraph/jpgraph_pie3d.php");
include_once("./lib/jpgraph/jpgraph_bar.php");

class StatsChartImpl extends StatsChart
{
	var $graph;
	var $value_r = array();
	var $display_r = array();
	
	function StatsChartImpl($chartType, $graphCfg) {
		parent::StatsChart($chartType, $graphCfg);
		
		if($this->chartType == 'piechart')
		{
			$this->graph = new PieGraph($this->width, $this->height, "");
		}
		else
		{
			$this->graph = new Graph($this->width, $this->height, "");
			$this->graph->SetScale("textlin");
			$this->graph->yaxis->scale->SetGrace(20);
			$this->graph->SetMargin(25,25,50,125);
			$this->graph->yaxis->HideZeroLabel();
			$this->graph->yscale->SetAutoMin(0);
			$this->graph->yaxis->SetLabelFormatString("%2d");
			$this->graph->xaxis->SetLabelAngle(90); 
		}
		
		if($this->transparent)
		{
			$this->graph->img->setTransparent("white");
		}
		
		$this->graph->SetFrame(false);
	}
	
	function addData($display, $value) {
		$this->value_r[] = $value;
		$this->display_r[] = $display;
	}
	
	function setTitle($title) {
		$this->graph->title->Set($title);
	}
		
	function render($imgType) {
		$this->graph->SetImgFormat($imgType);
		
		if($this->chartType == 'piechart') {
			$plot = new PiePlot3d($this->value_r);
			$plot->SetTheme("sand");
			$plot->SetCenter(0.3);
			$plot->SetAngle(30);
			$plot->SetLegends($this->display_r);
		} else {
			$this->graph->xaxis->SetTickLabels($this->display_r);
			
			$plot = new BarPlot($this->value_r);
			$plot->SetWidth(0.5);
			$plot->SetFillColor("orange@0.75");
		}
		
		$this->graph->Add($plot);
		$this->graph->Stroke();
	}
}

?>