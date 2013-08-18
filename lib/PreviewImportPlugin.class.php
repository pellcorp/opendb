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
class PreviewImportPlugin {
	var $classname = NULL;

	function get_display_name() {
		return get_opendb_lang_var ( 'preview' );
	}

	function get_plugin_type() {
		return 'row';
	}

	function is_header_row() {
		return TRUE;
	}

	function read_header($file_handle, &$error) {
		return NULL;
	}
}
?>