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

class LastItemsList extends WelcomeBlock {
	function __construct() {
		parent::__construct('lastitemslist', 'last_items_list', 'last_items_list', PERM_VIEW_LISTINGS);
	}

	function renderBlock($userid, $lastvisit) {
		$lastitemlist_blocks_r = get_welcome_last_item_list($lastvisit, $userid);
		if (is_array($lastitemlist_blocks_r))
			return get_last_item_list_table($lastitemlist_blocks_r);
		else
			return FALSE;
	}
}
?>
