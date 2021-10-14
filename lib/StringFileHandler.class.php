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
class StringFileHandler {
	var $_content;
	var $_content_length;
	var $_offset;
	var $_line_ending;
	var $_line_ending_length;

	function __construct($content) {
		$this->_content = $content;
		$this->_content_length = strlen ( $content );
		$this->_offset = 0;
		
		// lets ascertain the operating system of this
		// file by looking for first line ending.
		if (strpos ( $this->_content, "\r\n" ) !== FALSE) {
			$this->_line_ending = "\r\n";
			$this->_line_ending_length = 2;
		} else if (strpos ( $this->_content, "\n" ) !== FALSE) {
			$this->_line_ending = "\n";
			$this->_line_ending_length = 1;
		} else if (strpos ( $this->_content, "\r" ) !== FALSE) {
			$this->_line_ending = "\r";
			$this->_line_ending_length = 1;
		} else {
			$this->_line_ending = NULL; // no line ending in this file.
		}
	}

	function isEof() {
		if ($this->_offset >= $this->_content_length)
			return TRUE;
		else
			return FALSE;
	}

	/**
	* Handles all operating system line endings:
	* 	Macintosh - \r
	* 	Windows \r\n
	* 	Unix \n
	* 
	* If the line is empty, except for the line ending, this function will
	* return NULL.  It will return FALSE when no more data to read.
	*/
	function readLine() {
		if (! $this->isEof ()) {
			if ($this->_line_ending !== NULL) {
				if (($idx = strpos ( $this->_content, $this->_line_ending, $this->_offset )) !== FALSE) {
					if ($idx > $this->_offset) {
						$line = substr ( $this->_content, $this->_offset, $idx - $this->_offset );
						$this->_offset = $idx + $this->_line_ending_length;
						return $line;
					} else {
						$this->_offset ++;
						return NULL;
					}
				} else {
					$line = substr ( $this->_content, $this->_offset );
					$this->_offset = $this->_content_length;
					
					// last line.
					return $line;
				}
			} else {
				// no lines, return all content.
				$this->_offset = $this->_content_length;
				return $this->_content;
			}
		}
		
		return FALSE;
	}
}
?>
