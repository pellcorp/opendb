<?php
/** 	
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

require_once 'PHPUnit.php';

include_once("./functions/widgets.php");

class WidgetsTest extends PHPUnit_TestCase
{
	function WidgetsTest($name)
	{
		$this->PHPUnit_TestCase($name);
	}

	function setUp()
	{
	}
	
	function testNumberRangeExpand() 
	{
		$this->assertEquals( "0123456789",
							expand_chars_exp("0-9"));
	}
	
	function testPhoneNumberFormat() 
	{
		$this->assertEquals( "0123456789 -+",
							expand_chars_exp("0-9 -+"));
	}
	
	function testAlphaNumericFormat() 
	{
		$this->assertEquals( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.",
							expand_chars_exp("a-zA-Z0-9_."));
	}
	
	function testNonRangeNextToAlpha() 
	{
		$this->assertEquals( "0123456789-+",
							expand_chars_exp("0-9\-+"));
	}
	
	function testIsAlphaNum()
	{
		$this->assertEquals(TRUE, is_alphanum(ord('a')), "Assert 'a' is alphanun");
		$this->assertEquals(TRUE, is_alphanum(ord('Z')), "Assert 'Z' is alphanun");
		$this->assertEquals(TRUE, is_alphanum(ord('0')), "Assert '0' is alphanun");
		$this->assertEquals(TRUE, is_alphanum(ord('9')), "Assert '9' is alphanun");
	}
}
?>