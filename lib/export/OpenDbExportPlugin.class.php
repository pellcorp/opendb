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

class OpenDbExportPlugin {
	var $_level = 1;

	/*
	 * The content type, when saved as file.
	 */
	function get_file_content_type() {
		return 'text/xml';
	}

	/*
	 * The filename extension, when saved as file.
	 */
	function get_file_extension() {
		return 'xml';
	}

	function get_display_name() {
		return 'Open Media Collectors Database XML';
	}

	function get_plugin_type() {
		return 'item';
	}

	/*
	 * The file header, when saved as file.
	 */
	function file_header($title) {
		return "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n" . "<!--\n" . "\t$title\n" . "-->\n" . "<Items xmlns=\"http://opendb.iamvegan.net/xsd/Items-1.3.xsd\" " . "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" "
				. "xsi:schemaLocation=\"http://opendb.iamvegan.net/xsd/Items-1.3.xsd\">";
	}

	/*
	 * The file footer, when saved as file.
	 */
	function file_footer() {
		return "\n</Items>\n";
	}

	function start_item($item_id, $s_item_type, $title) {
		return "\n" . $this->__tabIndent($this->_level++) . "<Item ItemId=\"$item_id\" ItemType=\"$s_item_type\">" . "\n" . $this->__tabIndent($this->_level) . "<Title>" . $this->__wrapString($title) . "</Title>";
	}

	function end_item() {
		return "\n" . $this->__tabIndent(--$this->_level) . "</Item>";
	}

	function start_item_instance($item_id, $instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment, $update_on) {
		$buffer = "\n" . $this->__tabIndent($this->_level++) . "<Instance InstanceNo=\"$instance_no\" OwnerId=\"$owner_id\" BorrowDuration=\"$borrow_duration\" StatusType=\"$s_status_type\">";
		if (strlen($status_comment) > 0) {
			$buffer .= "\n" . $this->__tabIndent($this->_level) . "<StatusComment>" . $this->__wrapString($status_comment) . "</StatusComment>";
		}
		return $buffer;
	}

	function end_item_instance() {
		return "\n" . $this->__tabIndent(--$this->_level) . "</Instance>";
	}

	function item_attribute($s_attribute_type, $order_no, $attribute_val) {
		return "\n" . $this->__tabIndent($this->_level) . "<Attribute AttributeType=\"$s_attribute_type\" OrderNo=\"$order_no\">" . $this->__wrapString($attribute_val) . "</Attribute>";
	}

	//
	// Should be considered hidden methods.
	//
	function __wrapString($str) {
		return "<![CDATA[" . $str . "]]>";
	}

	//utility functions
	function __tabIndent($level) {
		if (is_numeric($level) && $level > 0)
			return str_repeat("\t", $level);
		else
			return "";
	}
}
?>
