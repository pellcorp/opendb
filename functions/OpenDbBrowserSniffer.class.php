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
include_once("./lib/phpsniff/phpSniff.class.php");

class OpenDbBrowserSniffer 
{
	var $phpSniffer;
	
	var $isSupported;
	
	function OpenDbBrowserSniffer()
	{
		$this->phpSniffer = new phpSniff(get_http_env('HTTP_USER_AGENT'));
		$this->__initIsSupported();
	}
	
	function isBrowserSupported()
	{
		return $this->isSupported;
	}
	
	function isBrowser($b)
	{
		return $this->phpSniffer->browser_is($b);
	}
	
	function getSupportedBrowsers()
	{
		$supportedBrowsers = array(
			array('name'=>'Firefox 1.5, 2.0', 'url'=>'http://www.mozilla.com/firefox/', 'icon'=>'firefox.jpg'),
			array('name'=>'Internet Explorer 7.0', 'url'=>'http://www.microsoft.com/windows/products/winfamily/ie/default.mspx', 'icon'=>'icon_ie7.gif'),
			array('name'=>'Internet Explorer 6.0', 'url'=>'http://www.microsoft.com/windows/ie/ie6/default.mspx', 'icon'=>'ie6.gif'),
		);
	
		return $supportedBrowsers;	
	}
	
	function __initIsSupported()
	{
		if($this->phpSniffer->browser_is('ns4'))
		{
			$this->isSupported = FALSE;
		}
		else if ($this->phpSniffer->browser_is('ie') && !$this->phpSniffer->browser_is('ie6+'))
		{
			$this->isSupported = FALSE;
		}
		else
		{	
			$this->isSupported = TRUE;
		}
	}
}
?>