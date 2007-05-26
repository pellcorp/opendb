<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

//
	// Should be considered hidden methods.
	//
function escape_xml_entities($str)
{
	// this can be found in functions/utils.php
	return str_replaces(
				array("\"", "<", ">", "\n", "\r", "&"), // find
				array("&#34;", "&#60;", "&#62;", "&#10;", "&#13;", "&#38;"), // replace
				$str);
}

//utility functions
function tab_indent($level)
{
	if(is_numeric($level) && $level>0)
		return str_repeat("\t", $level);
	else
		return "";
}

class OpenDb_XML
{
	var $_level = 1;

	/*
	* The content type, when saved as file.
	*/
	function get_file_content_type()
	{
		return 'text/xml';
	}

	/*
	* The filename extension, when saved as file.
	*/
	function get_file_extension()
	{
		return 'xml';
	}
	
	function get_display_name()
	{
		return 'Open Media Collectors Database XML';
	}
	
	function get_plugin_type()
	{
		return 'item';
	}
	
	/*
	* The file header, when saved as file.
	*/
	function file_header($title)
	{
		return "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n".
				"<!DOCTYPE opendb-items PUBLIC \"-//Open Media Collectors Database//DTD OpenDb Item Export 1.2//EN\" \"http://opendb.iamvegan.net/dtd/opendb-items_1.2.dtd\">\n\n".
				"<!--\n".
				"\t$title\n".
				"-->\n".
				"<opendb-items version=\"1.2\">";
	}

	/*
	* The file footer, when saved as file.
	*/
	function file_footer()
	{
		return "\n</opendb-items>\n";
	}

	function start_item($item_id, $s_item_type, $title)
	{
		return "\n".tab_indent($this->_level++)."<item item_id=\"$item_id\" s_item_type=\"$s_item_type\" title=\"".escape_xml_entities($title)."\">";
	}

	function end_item()
	{
		return "\n".tab_indent(--$this->_level)."</item>";
	}

	function start_item_instance($instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment)
	{
		return "\n".tab_indent($this->_level++)."<instance instance_no=\"$instance_no\" owner_id=\"$owner_id\" borrow_duration=\"$borrow_duration\" s_status_type=\"$s_status_type\" status_comment=\"".escape_xml_entities($status_comment)."\">";
	}
	
	function end_item_instance()
	{
		return "\n".tab_indent(--$this->_level)."</instance>";
	}

	function item_attribute($s_attribute_type, $order_no, $attribute_val)
	{
		return "\n".tab_indent($this->_level)."<attribute s_attribute_type=\"$s_attribute_type\" order_no=\"$order_no\">".
			escape_xml_entities($attribute_val).
			"</attribute>";
	}
}
?>
