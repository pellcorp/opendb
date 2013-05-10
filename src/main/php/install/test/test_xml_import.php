<?
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

include_once("lib/fileutils.php");

$file = "export.xml";

function startElement($parser, $name, $attribs) {
	print "&lt;<font color=\"#0000cc\">$name</font>";
	if (sizeof($attribs)) {
		while (list($k, $v) = each($attribs)) {
			print " <font color=\"#009900\">$k</font>=\"<font color=\"#990000\">$v</font>\"";
		}
	}
	print "&gt;";
}

function endElement($parser, $name) {
	print "&lt;/<font color=\"#0000cc\">$name</font>&gt;";
}

function characterData($parser, $data) {
	print "$data";
}

echo "<pre>";
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");

if (!($fp = file_open($file, "r"))) {
	die("could not open XML input");
}

while ($data = fread($fp, 16384)) {
	if (!xml_parse($xml_parser, $data, feof($fp))) {
		die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
	}
}
echo "</pre>";
xml_parser_free($xml_parser);

?>

