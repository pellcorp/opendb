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

include_once("lib/Configuration.class.php");
include_once("lib/Database.class.php");

class ConfigurationTest extends PHPUnit_Framework_TestCase {
	function testConfiguration() {
		$db_server_config_r = array('host' => 'localhost:/opt/lampp/var/mysql/mysql.sock', //OpenDb database host
		'dbname' => 'opendb', //OpenDb database name
		'username' => 'lender', //OpenDb database user name
		'passwd' => 'test', //OpenDb user password
		'table_prefix' => '', //Table prefix.
		'debug-sql' => FALSE);

		$database = new Database($db_server_config_r);
		$config = new Configuration($database);
		$this->assertTrue($database->isConnected());

		$this->assertTrue($config->getGroupVar('item_input', 'duplicate_title_support'));
		$this->assertTrue($config->setGroupVar('item_input', 'duplicate_title_support', FALSE));
		$this->assertFalse($config->getGroupVar('item_input', 'duplicate_title_support'));
	}
}
