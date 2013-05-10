<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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

require_once("lib/GDImage.class.php");

$chartLib = get_opendb_config_var('stats', 'chart_lib');

if ($chartLib == 'legacy') {
	include_once("lib/chart/LegacyStatsChart.class.php");
} else if ($chartLib == 'phplot' && is_dir("lib/phplot")) {
	include_once("lib/chart/PhplotStatsChart.class.php");
} else if ($chartLib == 'jpgraph' && is_php51() && is_dir("lib/jpgraph")) {
	include_once("lib/chart/JPGraphStatsChart.class.php");
} else if (is_php5()) {
	include_once("lib/chart/StatsLibChart12.class.php");
} else {
	include_once("lib/chart/StatsLibChart11.class.php");
}

function sort_data_element($a_r, $b_r) {
	$a = $a_r['value'];
	$b = $b_r['value'];

	if ($a == $b) {
		return 0;
	}
	return ($a > $b) ? -1 : 1;
}

function build_and_send_graph($data_rs, $chartType, $title) {
	$gdImage = new GDImage(get_opendb_image_type());
	$imgType = $gdImage->getImageType();
	unset($gdImage);

	$graphCfg = theme_graph_config();

	$chart = new StatsChartImpl($chartType, $graphCfg);

	$chart->setTitle($title);

	if (is_array($data_rs)) {

		usort($data_rs, "sort_data_element");

		// only show first 12 items - otherwise graph will not render correctly.
		if ($chartType == 'piechart' && count($data_rs) > 12)
			$data_rs = array_slice($data_rs, 0, 11);

		reset($data_rs);
		while (list(, $data_r) = each($data_rs)) {
			if ($chartType == 'piechart')
				$chart->addData($data_r['display'] . " (${data_r['value']})", $data_r['value']);
			else
				$chart->addData($data_r['display'], $data_r['value']);
		}
	}

	$chart->render($imgType);
}
?>