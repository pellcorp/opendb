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

include_once("lib/config.php");
include_once("lib/Database.class.php");

class DatabaseTest extends PHPUnit_Framework_TestCase {
	function testDatabase() {
		$db_server_config_r = array('host' => 'localhost:/opt/lampp/var/mysql/mysql.sock', //OpenDb database host
		'dbname' => 'opendb', //OpenDb database name
		'username' => 'lender', //OpenDb database user name
		'passwd' => 'test', //OpenDb user password
		'table_prefix' => '', //Table prefix.
		'debug-sql' => FALSE);

		$database = new Database($db_server_config_r);
		$this->assertTrue($database->isConnected());
		$this->assertTrue($database->ping());

		$results = $database->query("SELECT user_id, fullname, user_role, language, theme, email_addr, active_ind FROM user where user_id = 'admin'");
		$this->assertEquals(1, $database->numRows($results));
		$this->assertEquals(7, $database->numFields($results));
		$user_rs = array();
		while ($user_r = $database->fetchAssoc($results)) {
			$user_rs[] = $user_r;
		}
		$database->freeResult($results);
		$this->assertEquals(1, count($user_rs));

		$this->assertNull($database->error());
		$this->assertEquals(0, $database->errno());

		$this->assertEquals(1, $database->affectedRows()); // not just for inserts!?
		$this->assertEquals(0, $database->lastInsertId()); // not sure why this is zero, but for now it is!

		$database->close();

		// now check that the class behaves even when disconnected!
		$this->assertFalse($database->isConnected());
		$this->assertFalse($database->ping());
		$this->assertFalse($database->query("SELECT user_id, fullname, user_role, language, theme, email_addr, active_ind FROM user where user_id = 'admin'"));
		$this->assertFalse($database->numRows($results));
		$this->assertFalse($database->numFields($results));
		$this->assertFalse($database->fetchAssoc($results));
		$this->assertFalse($database->freeResult($results));
		$this->assertFalse($database->affectedRows());
		$this->assertFalse($database->lastInsertId());
	}
}
