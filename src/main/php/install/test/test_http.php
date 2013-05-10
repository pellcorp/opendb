<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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

include_once("lib/Snoopy.class.php");

$snoopy = new Snoopy;

//override user agent.
$snoopy->agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4';

// replicate your proxy configuration if required 
$proxy_config_r = array('enable' => FALSE, 'host' => 'squid.domain.com.au', 'port' => '8080', 'userid' => 'jpell', 'password' => 'pwd');

// if no proxy
if ($proxy_config_r['enable'] == TRUE) {
	echo '<br>Proxy server ENABLED';
	echo '<br>proxy_host: ' . $proxy_config_r['host'];
	echo '<br>proxy_port: ' . $proxy_config_r['port'];
	echo '<br>user: ' . $proxy_config_r['userid'];
	echo '<br>pass: ' . $proxy_config_r['password'];

	$snoopy->proxy_host = $proxy_config_r['host'];
	$snoopy->proxy_port = $proxy_config_r['port'];
	$snoopy->user = $proxy_config_r['userid'];
	$snoopy->pass = $proxy_config_r['password'];
} else {
	echo '<br>Proxy server DISABLED';
}

if ($snoopy->fetch('http://www.sourceforge.net/projects/opendb')) {
	echo '<br>Fetch successful.';
	if ($snoopy->status >= 200 && $snoopy->status < 300) {
		$result = $snoopy->results;
		echo '<br>Size of result returned: ' . strlen($result);
	} else {
		echo 'URL returned a non 200 status (Status-' . $snoopy->status . '; Error-' . $snoopy->error . ')';
	}
} else {
	echo 'Failed to open URL (Status-' . $snoopy->status . '; Error-' . $snoopy->error . ')';
}
?>