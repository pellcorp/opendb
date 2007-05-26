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
class DIF
{
	function get_display_name()
	{
		return 'Data Interchange Format';
	}
	
	function get_plugin_type()
	{
		return 'row';
	}
	
	function is_extension_supported($extension)
	{
		return (strcasecmp($extension, 'dif') === 0);
	}

	function is_header_row()
	{
		return FALSE;
	}

	function read_header(&$fileHandler, &$error)
	{
		// skip header information
		while(!$fileHandler->isEof())
		{
			$topic = trim($fileHandler->readLine());
			
			// Ignore next two lines - info and value
			$fileHandler->readLine();
			$fileHandler->readLine();
			
			if($topic == "DATA")
			{
				//read "0,1" and ignore!
				$fileHandler->readLine();
				
				// Now that we have skipped the DIF header, we need the first row of data.
				return $this->read_row($fileHandler, $error);
			}
		}
		
		// could not read header.
		$error = 'Could not read header';
		return FALSE;
	}
	
	/*
	* If row is empty, return an empty array()
	*/
	function read_row(&$fileHandler, &$error)
	{
		if($fileHandler->isEof() || trim($fileHandler->readLine())!=="BOT")
			return FALSE;

		while(!$fileHandler->isEof())
		{   
			$line1 = trim($fileHandler->readLine());

			// Now lets process it!
			if($line1 == "-1,0")
				break;//break out of while
			else
			{
				$line2 = trim($fileHandler->readLine());
				if(substr($line1,0,2) == "0,")
				{
					if($line2 == "V")
						$data = substr($line1,2);
					else
						$data = "";//not available or error!
				}
				else if($line1 == "1,0")
				{
					// Get rid of any surrounding quotes.  Will check for " only!
					// Must have a start and end " for the match to occur!
					if(strlen($line2)>=2 && substr($line2,0,1) == "\"" && substr($line2,-1) == "\"")
						$data = substr($line2,1,-1);
					
					// Get rid of any repeated quotes!
					$data = str_replace("\"\"", "\"", $data);
				}
				else
					$data = "";//error

				$row[] = $data;
			}
		}
		
		return $row;
	}
}
?>