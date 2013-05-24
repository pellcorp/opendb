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
class CSVImportPlugin {
	// required for introspection
	var $delimiter = ',';
	var $line_ending = "\n";

	function CSVImportPlugin() {
	}

	function get_display_name() {
		return 'Comma Delimited';
	}

	function get_plugin_type() {
		return 'row';
	}

	function is_extension_supported($extension) {
		return (strcasecmp($extension, 'csv') === 0);
	}

	/**
	    Indicates that CSV are assumed to have a header row
	 */
	function is_header_row() {
		return TRUE;
	}

	function read_header(&$fileHandler, &$error) {
		return $this->read_row($fileHandler, $error);
	}

	function read_row(&$fileHandler, &$error) {
		$argument = NULL;
		$quotefound = FALSE;

		while (($line = $fileHandler->readLine()) !== FALSE) {
			// do not require start and end newlines.
			$line = trim($line);

			for ($i = 0; $i < strlen($line); $i++) {
				if ($argument === NULL)
					$argument = "";

				if ($line[$i] == "\"") {
					if ($quotefound && $line[$i + 1] == "\"") {
						$argument .= $line[$i];
						$i++;
					} else {
						$quotefound = !$quotefound;//toggle.
					}
				} else if ($line[$i] == $this->delimiter) {
					if ($quotefound)
						$argument .= $line[$i];
					//ignore cos its in quotes.
					else {
						$arguments[] = trim($argument);
						$argument = "";
					}
				} else {
					$argument .= $line[$i];
				}
			}

			// only keep going if we are in middle of quote
			if ($quotefound) {
				$argument .= $this->line_ending;
			} else {
				break;
			}
		}

		if ($argument !== NULL)
			$arguments[] = trim($argument);

		return $arguments;
	}
}
?>
