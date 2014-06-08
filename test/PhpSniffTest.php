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
include_once("./lib/phpsniff/phpSniff.class.php");

class PhpSniffTest extends PHPUnit_Framework_TestCase
{
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
		$this->assertTrue($sniffer->browser_is('fx1+'));
		$this->assertTrue($sniffer->browser_is('fx1.5+'));
		$this->assertTrue($sniffer->browser_is('fx1.5'));
		$this->assertFalse($sniffer->browser_is('fx15'), 'fx15 is not a legal type - wish it was!');
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
		$this->assertFalse($sniffer->browser_is('op'));
		$this->assertFalse($sniffer->browser_is('kq'));
	}
	
	function testNS4Browser() {
		$sniffer = new phpSniff('Mozilla/4.78 [en] (Win98; U)');
		$this->assertTrue($sniffer->browser_is('ns4'));
		$this->assertTrue($sniffer->browser_is('ns4+'));
		$this->assertFalse($sniffer->browser_is('op'));
		$this->assertFalse($sniffer->browser_is('kq'));
		$this->assertFalse($sniffer->browser_is('ns7'));
	}
	
	function testOperaBrowser() {
		$sniffer = new phpSniff('mozilla/4.0 (compatible; msie 5.0; linux 2.4.16 i686) opera 5.0  [en]');
		$this->assertTrue($sniffer->browser_is('op'));
		$this->assertFalse($sniffer->browser_is('kq'));
		$this->assertFalse($sniffer->browser_is('ns4'));
		$this->assertFalse($sniffer->browser_is('fx'));
		$this->assertFalse($sniffer->browser_is('ie'));
	}
	
	function testKonquererBrowser() {
		$sniffer = new phpSniff('Mozilla/5.0 (compatible; Konqueror/3.1-13; Linux)');
		$this->assertTrue($sniffer->browser_is('kq'));
		$this->assertFalse($sniffer->browser_is('ns4'));
		$this->assertFalse($sniffer->browser_is('fx'));
		$this->assertFalse($sniffer->browser_is('ie'));
	}
	
	function testSafariBrowser() {
		$sniffer = new phpSniff('Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/48 (like Gecko) Safari/48');
		$this->assertTrue($sniffer->browser_is('sf'));
		$this->assertFalse($sniffer->browser_is('kq'));
		$this->assertFalse($sniffer->browser_is('ns4'));
		$this->assertFalse($sniffer->browser_is('fx'));
		$this->assertFalse($sniffer->browser_is('ie'));
	}
	
	function testStrReplaceForDot() {
		$browser = 'fx1.5';
		$this->assertEquals('fx15', str_replace(".", NULL, $browser));
	}
}
?>