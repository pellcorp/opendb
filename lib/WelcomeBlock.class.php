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
include_once("./lib/utils.php");
include_once("./lib/auth.php");

class WelcomeBlock {
	var $_id;
	var $_cfgId;
	var $_titlelangvar;
	var $_permId;

	/**
	 * Assumes the configuration ID = welcome.$id, or $cfgId needs to be non NULL to override
	 */
	function __construct($id, $titlelangvar = NULL, $cfgId = NULL, $permId = NULL) {
		$this->_id = $id;
		$this->_title = $titlelangvar;
		$this->_cfgId = $cfgId;
		$this->_permId = $permId;
	}

	function getHeading() {
		if (strlen ( $this->_titlelangvar ) > 0) {
			return '<h3>' . get_opendb_lang_var ( $this->_titlelangvar ) . '</h3>';
		} else {
			return NULL;
		}
	}

	function getPermId() {
		return $this->_permId;
	}

	function isAvailable($userid) {
		if (($this->getConfigId () == NULL || get_opendb_config_var ( $this->getConfigId (), 'enable' ) === TRUE) && ($this->getPermId () == NULL || is_user_granted_permission ( $this->getPermId (), $userid ))) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function getConfigId() {
		if (strlen ( $this->_cfgId ) > 0) {
			return 'welcome.' . $this->_cfgId;
		} else {
			return NULL;
		}
	}

	function getId() {
		return $this->_id;
	}

	function render($userid, $lastvisit) {
		if ($this->isAvailable ( $userid )) {
			$block = $this->renderBlock ( $userid, $lastvisit );
			
			if ($block) {
				return "\n<div id=\"" . $this->getId () . "\">" . $this->getHeading () . $block . '</div>';
			}
		}
		// else 
		return NULL;
	}

	/**
	 * OVERRIDE in each welcome plugin
	 *
	 * @param unknown_type $userid
	 * @param unknown_type $lastvisit
	 * @return unknown
	 */
	function renderBlock($userid, $lastvisit) {
		return "Override Me!";
	}
}
?>
