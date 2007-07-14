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
class OpenDb_XML
{
	var $version = '1.2';
	var $is_version_valid = FALSE;
	
	function get_display_name()
	{
		return 'Open Media Collectors Database XML';
	}
	
	function get_plugin_type()
	{
		return 'xml';
	}
	
	function is_doctype_supported($doctype)
	{
		return (strcasecmp($doctype, 'opendb-items') === 0);
	}

	function start_element($name, $attribs, $pcdata)
	{
		if(strcmp($name, 'opendb-items')===0)
		{
			if($attribs['version'] === $this->version)
				$this->is_version_valid = TRUE;
			else
				import_add_error('start_element', 'Incorrect OpenDb XML Version. ('.$attribs['version'].'!='.$this->version.')');
		}
		else if($this->is_version_valid)
		{
			if(strcmp($name, 'item')===0)
			{
				import_start_item($attribs['s_item_type'], $attribs['title']);
			}
			else if(strcmp($name, 'instance')===0)
			{
				import_start_item_instance($attribs['s_status_type'], $attribs['status_comment'], $attribs['borrow_duration']);
			}
			else if(strcmp($name, 'attribute')===0)
			{
				import_item_attribute($attribs['s_attribute_type'], NULL, unhtmlentities($pcdata));
			}
		}
	}
	
	function end_element($name)
	{
		if($this->is_version_valid)
		{
			if(strcmp($name, 'item')===0)// ignore doctype start element.
			{
				import_end_item();
			}
			else if(strcmp($name, 'instance')===0)
			{
                import_end_item_instance();
			}
		}
	}
}
?>