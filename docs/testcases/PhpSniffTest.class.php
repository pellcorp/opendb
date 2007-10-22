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

// TODO - add tests

require_once 'PHPUnit.php';
include_once("./lib/phpsniff/phpSniff.class.php");

class PhpSniffTest extends PHPUnit_TestCase
{
	function PhpSniffTest($name) {
		parent::PHPUnit_TestCase($name);
	}
	
	function testIE55Browser() {
		$sniffer = new phpSniff('mozilla/4.0 (compatible; msie 5.5; windows 98; win 9x 4.90)');
		$this->assertTrue($sniffer->browser_is('ie'));
		$this->assertTrue($sniffer->browser_is('ie5'));
		$this->assertTrue($sniffer->browser_is('ie5+'));
		$this->assertFalse($sniffer->browser_is('ie6'));
	}
	
	function testIE6Browser() {
		$sniffer = new phpSniff('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
		$this->assertTrue($sniffer->browser_is('ie'));
		$this->assertTrue($sniffer->browser_is('ie6'));
		$this->assertFalse($sniffer->browser_is('ie7'));
	}
	
	function testIE7Browser() {
		$sniffer = new phpSniff('Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
		$this->assertTrue($sniffer->browser_is('ie'));
		$this->assertTrue($sniffer->browser_is('ie7'));
		$this->assertFalse($sniffer->browser_is('ie6'));
	}
	
	function testFF15Browser() {
		$sniffer = new phpSniff('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8) Gecko/20051111 Firefox/1.5');
		$this->assertTrue($sniffer->browser_is('fx'));
		$this->assertTrue($sniffer->browser_is('fx1.5+'));
		$this->assertTrue($sniffer->browser_is('fx1.5'));
		$this->assertFalse($sniffer->browser_is('fx2'));
		$this->assertFalse($sniffer->browser_is('ie'));
		$this->assertFalse($sniffer->browser_is('ie7'));
		$this->assertFalse($sniffer->browser_is('ie6'));
	}
	
	function testFF2Browser() {
		$sniffer = new phpSniff('mozilla/5.0 (x11; u; linux i686; en-us; rv:1.8.1.6) gecko/20071008 ubuntu/7.10 (gutsy) firefox/2.0.0.6');
		$this->assertTrue($sniffer->browser_is('fx'));
		$this->assertTrue($sniffer->browser_is('fx1.5+'));
		$this->assertTrue($sniffer->browser_is('fx2'));
		$this->assertFalse($sniffer->browser_is('fx1.5'));
		$this->assertFalse($sniffer->browser_is('ie'));
		$this->assertFalse($sniffer->browser_is('ie7'));
		$this->assertFalse($sniffer->browser_is('ie6'));
	}
	
	function testNS4Browser() {
		$sniffer = new phpSniff('Mozilla/4.78 [en] (Win98; U)');
		$this->assertTrue($sniffer->browser_is('ns4'));
		$this->assertTrue($sniffer->browser_is('ns4+'));
		$this->assertFalse($sniffer->browser_is('ns7'));
	}
}
?>