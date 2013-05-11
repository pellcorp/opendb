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

require_once 'PHPUnit.php';

include_once("./lib/install.php");

class VersionCheckTest extends PHPUnit_TestCase
{
	function VersionCheckTest($name)
	{
		$this->PHPUnit_TestCase($name);
	}

	function setUp()
	{
	}
	
	function testVersion150a8To150b1()
	{
		$this->assertTrue(version_compare('1.5.0b1', '1.5.0a8', '>'), '1.5.0b1 > 1.5.0a8');
	}
	
	function testVersion10To11Dev1()
	{
		$this->assertTrue(version_compare('1.1dev1', '1.0pl1', '>'), '1.1dev1 > 1.0pl1');
	}
	
	function testVersionBeta3ToBeta4()
	{
		$this->assertTrue(opendb_version_compare('1.0b3', '1.0a4', '>'), '1.0b3 > 1.0a4');
	}
	
	function testVersionBeta6ToRC1()
	{
		$this->assertTrue(opendb_version_compare('1.0RC1', '1.0b6', '>'), 'RC1 > 1.0b6');
	}
	
	function testVersionBeta9ToBeta10()
	{
		$this->assertTrue(opendb_version_compare('1.0b10', '1.0b9', '>'), '1.0b10 > 1.0b9');
	}
	
	function testVersionRC1To10()
	{
		$this->assertTrue(opendb_version_compare('1.0', '1.0RC1', '>'), '1.0RC1 > 1.0');
	}
	
	function testVersionRC2To10()
	{
		$this->assertTrue(opendb_version_compare('1.0', '1.0RC2', '>'), '1.0RC2 > 1.0');
	}
	
	function testVersion10To101()
	{
		$this->assertTrue(opendb_version_compare('1.0.1', '1.0', '>'), '1.0.1 > 1.0');
	}
	
	function testVersion10To10pl1()
	{
		$this->assertTrue(opendb_version_compare('1.0pl1', '1.0', '>'), '1.0pl1 > 1.0');
	}
	
	function testVersion10pl1To101()
	{
		$this->assertTrue(opendb_version_compare('1.0.1', '1.0pl1', '>'), '1.0.1 > 1.0pl1');
	}
	
	function testVersion100pl1To101()
	{
		$this->assertTrue(opendb_version_compare('1.0.1', '1.0.0pl1', '>'), '1.0.1 > 1.0.0pl1');
	}
	
	function testVersion081To101()
	{
		$this->assertTrue(opendb_version_compare('1.0.1', '0.81', '>'), '1.0.1 > 0.81');
	}
	
	function testVersion101To101pl1()
	{
		$this->assertTrue(opendb_version_compare('1.0.1p1l', '1.0.1', '>'), '1.0.1p1l > 1.0.1');
	}
	
	function testVersion10pl1To101pl1()
	{
		$this->assertTrue(opendb_version_compare('1.0.1p1l', '1.0pl1', '>'), '1.0.1p1l > 1.0pl1');
	}
	
	function testVersion10pl1To110dev1()
	{
		$this->assertTrue(opendb_version_compare('1.1.0dev1', '1.0pl1', '>'), '1.1.0dev1 > 1.0pl1');
	}
	
	function testVersion101To110dev1()
	{
		$this->assertTrue(opendb_version_compare('1.1.0dev1', '1.0.1', '>'), '1.1.0dev1 > 1.0.1');
	}
	
	function testVersion111To110pl1()
	{
		$this->assertTrue(opendb_version_compare('1.1.0pl1', '1.1.0', '>'), '1.1.0pl1 > 1.1.0');
	}
	
	function testVersion106To150RC1()
	{
		$this->assertTrue(opendb_version_compare('1.5.0RC1', '1.0.6', '>'), '1.5.0RC1 > 1.0.6');
	}
	
	function testVersion150RC4To150()
	{
		$this->assertTrue(opendb_version_compare('1.5.0', '1.5.0RC4', '>'), '1.5.0 > 1.5.0RC4');
	}
	
	function testVersion150To1501()
	{
		$this->assertTrue(opendb_version_compare('1.5.0.1', '1.5.0', '>'), '1.5.0.1 > 1.5.0');
	}
}

?>