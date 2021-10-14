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

class CSVExportPlugin {
	/*
	 * The content type, when saved as file.
	 */
	function get_file_content_type() {
		return 'application/csv';
	}

	/*
	 * The filename extension, when saved as file.
	 */
	function get_file_extension() {
		return 'csv';
	}

	function get_display_name() {
		return 'Comma Delimited Format';
	}

	function get_plugin_type() {
		return 'row';
	}

	function prompt_header($columns) {
		$buffer = "";

		foreach ($columns as $column) {
			if (strlen($buffer) > 0)
				$buffer .= ",$column";
			else
				$buffer .= "$column";
		}
		return $buffer . "\n";
	}

	function item_row($columns) {
		$buffer = "";

		$isFirst = TRUE;
		foreach ($columns as $column) {
			if (is_array($column)) {
				$colval = '';
				for ($i = 0; $i < count($column); $i++) {
					if ($i > 0)
						$colval .= ',';
					$colval .= $column[$i];
				}

				unset($column); // unset, so we can assign a string to it.
				$column = $colval;
			}

			$doQuote = FALSE;
			if (strpos($column, "\"") !== FALSE) {
				$column = str_replace("\"", "\"\"", $column);
				$doQuote = TRUE;
			}

			if (strpos($column, ",") !== FALSE || strpos($column, "\n") !== FALSE) {
				$doQuote = TRUE;
			}

			if ($doQuote)
				$column = "\"" . $column . "\"";

			if (!$isFirst) {
				$buffer .= ",$column";
			} else {
				$buffer .= "$column";
				$isFirst = FALSE;
			}
		}
		return $buffer . "\n";
	}
}
?>
