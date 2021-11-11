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

class WhatsNew extends WelcomeBlock {
	function __construct() {
		parent::__construct('whatsnew', 'whats_new', 'whats_new', PERM_VIEW_WHATSNEW);
	}

	function renderBlock($userid, $lastvisit) {
		$whats_new_rs = get_whats_new_details($lastvisit, $userid);
		if (is_array($whats_new_rs)) {
			$buffer = "\n<ul>";
			foreach ( $whats_new_rs as $whats_new_r ) {
				if (is_array($whats_new_r['items'])) {
					$buffer .= "\n<li><ul class=\"block\">";
					$buffer .= "\n<h4>" . $whats_new_r['heading'] . "</h4>";

					reset($whats_new_r['items']);
					foreach ( $whats_new_r['items'] as $item_rs ) {
						$buffer .= "\n<li class=\"" . $item_rs['class'] . "\">" . $item_rs['content'] . "</li>";
					}
					$buffer .= "\n</ul></li>";
				}
			}
			$buffer .= "\n</ul>";

			return $buffer;
		} else {
			return FALSE;
		}
	}
}
?>
