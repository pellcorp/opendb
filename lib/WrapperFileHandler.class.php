<?php
/* 	
 	OpenDb Media Collector Database
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
class WrapperFileHandler {
	var $_fileHandle;

	function __construct($fileHandle) {
		$this->_fileHandle = $fileHandle;
	}

	function isEof() {
		return feof ( $this->_fileHandle );
	}

	function readLine() {
		if (! $this->isEof ()) {
			return fgets ( $this->_fileHandle, 4096 );
		} else {
			return FALSE;
		}
	}
}
?>
