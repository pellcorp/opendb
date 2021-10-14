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

include_once("./lib/utils.php");

/*
* Work out the DocType and namespace of the Document,
*/
class DocTypeNameSpaceXMLParser {
	var $_docType;
	var $_nameSpace;
	var $_errors;

	function parseFile($fileLocation) {
		// reset it.
		$this->_docType = NULL;
		$this->_nameSpace = NULL;
		$this->_errors = NULL;
		
		$fp = @fopen ( $fileLocation, 'r' );
		if ($fp) {
			$parser = xml_parser_create ( 'ISO-8859-1' );
			xml_set_object ( $parser, $this );
			xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, FALSE );
			xml_set_element_handler ( $parser, "_startElement", "_endElement" );
			
			while ( $data = fread ( $fp, 1024 ) ) {
				if (strlen ( $this->_docType ) > 0) {
					break;
				}
				
				if (! xml_parse ( $parser, $data, feof ( $fp ) )) {
					$error = xml_error_string ( xml_get_error_code ( $parser ) );
					break;
				}
			}
			xml_parser_free ( $parser );
			
			@fclose ( fp );
			return TRUE;
		} else {
			$this->_errors [] = 'File ' . $fileLocation . ' could not be opened.';
			return FALSE;
		}
	}

	function getErrors() {
		return $this->_errors;
	}

	function getDocType() {
		return $this->_docType;
	}

	function getNameSpace() {
		return $this->_nameSpace;
	}

	/**
	 * xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	 * xsi:schemaLocation="http://opendb.iamvegan.net/xsd/Items-1.3.xsd"
	 *
	 * @param unknown_type $parser
	 * @param unknown_type $name
	 * @param unknown_type $attributes
	 */
	function _startElement($parser, $name, $attributes) {
		if (strlen ( $this->_docType ) == 0) {
			$this->_docType = $name;
			
			if (is_array ( $attributes )) {
				reset ( $attributes );
				foreach ($attributes as $name => $value) {
					if (ends_with ( $name, ":schemaLocation" )) {
						$this->_nameSpace = $value;
						break;
					}
				}
			}
		}
	}

	function _endElement($parser, $name) {
		// not used
	}
}
?>
