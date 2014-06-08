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

require_once("./lib/database.php");

class DbPrefixesTest extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
	}
	
	function testAlterTable()
	{
		$this->assertEquals( "ALTER TABLE opendb_user_address ADD public_address_ind VARCHAR(1) NOT NULL DEFAULT 'N'",
							parse_sql_statement("ALTER TABLE user_address ADD public_address_ind VARCHAR(1) NOT NULL DEFAULT 'N'", 'opendb_'));
	}
	
	function testDropTable()
	{
		$this->assertEquals( "DROP TABLE opendb_item_attribute_old",
							parse_sql_statement("DROP TABLE item_attribute_old", 'opendb_'));
	}
	
	function testDropTableIfExists()
	{
		$this->assertEquals( "DROP TABLE IF EXISTS opendb_item_attribute_old",
							parse_sql_statement("DROP TABLE IF EXISTS item_attribute_old", 'opendb_'));
	}
	
	function testAlterTableDrop()
	{
		$this->assertEquals( "ALTER TABLE opendb_item DROP category",
							parse_sql_statement("ALTER TABLE item DROP category", 'opendb_'));
	}
	
	function testDescribeTable()
	{
		$this->assertEquals( "DESCRIBE opendb_item",
							parse_sql_statement("DESCRIBE item", 'opendb_'));
	}
	
	function testLockTable()
	{
		$this->assertEquals( "LOCK TABLES opendb_s_item_listing_column_conf WRITE",
							parse_sql_statement("LOCK TABLES s_item_listing_column_conf WRITE", 'opendb_'));
	}
	
	function testLockTables()
	{
		$this->assertEquals( "LOCK TABLES opendb_s_item_listing_column_conf WRITE, opendb_s_item_listing_conf WRITE",
							parse_sql_statement("LOCK TABLES s_item_listing_column_conf WRITE, s_item_listing_conf WRITE", 'opendb_'));
	}
	
	function testShowTableStatus()
	{
		$this->assertEquals( "SHOW TABLE STATUS LIKE 'opendb_item_instance'",
							parse_sql_statement("SHOW TABLE STATUS LIKE 'item_instance'", 'opendb_'));
	}
	
	function testShowColumnsFromTable()
	{
		$this->assertEquals( "SHOW COLUMNS FROM opendb_item_instance",
							parse_sql_statement("SHOW COLUMNS FROM item_instance", 'opendb_'));
	}
	
	function testShowFullColumnsFromTable()
	{
		$this->assertEquals( "SHOW FULL COLUMNS FROM opendb_item_instance",
							parse_sql_statement("SHOW FULL COLUMNS FROM item_instance", 'opendb_'));
	}
	
	function testAddTableIndex()
	{
		$this->assertEquals( "ALTER TABLE opendb_item_attribute ADD INDEX lookup_attribute_val_idx ( lookup_attribute_val )",
							parse_sql_statement("ALTER TABLE item_attribute ADD INDEX lookup_attribute_val_idx ( lookup_attribute_val )", 'opendb_'));
	}	
	
	function testAddTableIndex2()
	{
		$this->assertEquals( "ALTER TABLE opendb_item_attribute ADD INDEX attribute_val_idx ( attribute_val ( 255 ) )",
							parse_sql_statement("ALTER TABLE item_attribute ADD INDEX attribute_val_idx ( attribute_val ( 255 ) )", 'opendb_'));
	}
}

?>