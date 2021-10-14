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
include_once("./lib/phpsniff/phpSniff.class.php");

class OpenDbBrowserSniffer {
	var $phpSniffer;
	var $isSupported;
	var $browsers_r = array (
			'ie',
			'ip',
			'ie6',
			'ie7',
			'fx',
			'fx1.5',
			'fx2',
			'op',
			'kq',
			'sf' );

	function __construct() {
		$this->phpSniffer = new phpSniff( get_http_env( 'HTTP_USER_AGENT' ) );
		$this->__initIsSupported ();
	}

	/**
	 * This method returns true if browser is not ns4, ie5 or IE5.5
	 *
	 * @return unknown
	 */
	function isBrowserSupported() {
		return $this->isSupported;
	}

	function getSupportedBrowsers() {
		return $this->browsers_r;
	}

	function isBrowser($b) {
		if ($b == 'ip') {
			if ($this->isPlatform ( 'iphone' )) {
				return TRUE;
			}
		}
		
		//else
		return $this->phpSniffer->browser_is ( $b );
	}

	/**
	 * For instance to determine iphone.
	 * 
	 * isPlatform('iphone')
	 * @param $p
	 */
	function isPlatform($p) {
		return $this->phpSniffer->property ( 'platform' ) == $p;
	}

	function __initIsSupported() {
		if ($this->phpSniffer->browser_is ( 'ns4' )) {
			$this->isSupported = FALSE;
		} else if ($this->phpSniffer->browser_is ( 'ie' ) && ! $this->phpSniffer->browser_is ( 'ie6+' )) {
			$this->isSupported = FALSE;
		} else {
			$this->isSupported = TRUE;
		}
	}
}
?>
