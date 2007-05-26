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

include_once("./functions/excelgen/excelgen.class.inc");

class Excel
{
	var $_excelGen;
	var $_rownum = 0;

	function Excel()
	{
		$this->_excelGen = new ExcelGen();
	}
	
	/*
	* The content type, when saved as file.
	*/
	function get_file_content_type()
	{
		return 'application/vnd.ms-excel';
	}

	/*
	* The filename extension, when saved as file.
	*/
	function get_file_extension()
	{
		return 'xls';
	}
	
	function get_plugin_type()
	{
		return 'row';
	}
	
	function get_display_name()
	{
		return 'Microsoft Excel';
	}

	function prompt_header($columns)
	{
		$colnum = 0;
		while(list(,$column) = each($columns))
		{
			$this->_excelGen->WriteText( $this->_rownum, $colnum++, $column );
		}
		$this->_rownum++;
		
		return $this->_excelGen->GetBuffer();
	}

	function item_row($columns)
	{
		$colnum = 0;
		while(list(,$column) = each($columns))
		{
			if(is_array($column))
			{
				$colval = '';
				for($i=0; $i<count($column); $i++)
				{
					if(!empty($colval))
						$colval .= ',';
					$colval .= $column[$i];
				}
				
				$this->_excelGen->WriteText( $this->_rownum, $colnum++, $colval );
			}
			else
			{
				$this->_excelGen->WriteText( $this->_rownum, $colnum++, $column );
			}
		}
		$this->_rownum++;
		
		return $this->_excelGen->GetBuffer();
	}

	function close()
	{
		return $this->_excelGen->Close();
	}
}
?>
