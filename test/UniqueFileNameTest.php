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

chdir(dirname(dirname(__FILE__)));

include_once("./lib/item_attribute.php");
include_once("./lib/parseutils.php");

class UniqueFileNameTest extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
	}

	function testGetUniqueFilename() {
		$file_r['name'] = 'abc';
		$file_r['extension'] = 'gif';
		
		$filelist_r[] = 'abc1.gif';
		$filelist_r[] = 'abc2.gif';
		$filelist_r[] = 'abc3.gif';
		
		do {
			$count++;
			$file = $file_r['name'].$count.'.'.$file_r['extension'];
		} while(in_array($file, $filelist_r));
		
		$this->assertEquals('abc4.gif', $file);
	}
	
	function testFindNameRoot() {
		$parse_r = parse_numeric_suffix('abc22');
		$this->assertEquals('abc', $parse_r['prefix']);
		$this->assertEquals('22', $parse_r['suffix']);
		
		$parse_r = parse_numeric_suffix('22');
		$this->assertEquals('', $parse_r['prefix']);
		$this->assertEquals('22', $parse_r['suffix']);
		
		$parse_r = parse_numeric_suffix('22a');
		$this->assertEquals('22a', $parse_r['prefix']);
		$this->assertEquals('', $parse_r['suffix']);
	}
}
?>