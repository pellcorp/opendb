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

include_once("./lib/WelcomeBlock.class.php");
include_once("./lib/statsdata.php");
include_once("./lib/whatsnew.php");

/**
 * To enable this plugin do the following:
 * 
 * INSERT INTO s_config_group ( id, order_no, name, description ) VALUES ( 'welcome.category_stats', 4, 'Category Stats', 'Category Stats configuration' );
 * 
 */
class CategoryStats extends WelcomeBlock {
	function __construct() {
		parent::__construct('categorystats', 'category_stats', 'category_stats', PERM_VIEW_STATS);
	}

	function renderBlock($userid, $lastvisit) {
		return "\n<ul>" . "<li>" . render_chart_image('categories') . "</li>" . "\n</ul>";
	}
}
?>
