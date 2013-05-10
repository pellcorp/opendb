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

include_once("lib/fileutils.php");
include_once("lib/utils.php");

function local_is_exists_theme($theme) {
	return $theme == 'default';
}

class OpenFileTest extends PHPUnit_Framework_TestCase {
	function testBaseDir() {
		// we want to assert that the base directory is not the lib directory
		// where files resides, but the dir under that.
		$this->assertTrue(ends_with(get_opendb_basedir(), "target/classes"));
	}

	function testOpenFile() {
		$f = file_open("upload/deleteme", 'r');
		$contents = fread($f, 1000000);
		fclose($f);
		$this->assertEquals("", $contents);
	}

	function testOpenFileIsNull() {
		$f = file_open("upload/deletemeX", 'r');
		$this->assertEquals(NULL, $f);
	}

	function testGetFileListInvalidDirectoryButEmptyArray() {
		$this->assertTrue(is_array(get_file_list("whocares")));
	}

	function testGetFileList() {
		$filelist = get_file_list("help/english", "");
		$this->assertEquals(3, count($filelist));

		$filelist = get_file_list("help/english", "xml");
		$this->assertEquals(0, count($filelist));
	}

	function testGetFileExtension() {
		$this->assertEquals("txt", get_file_ext("test.txt"));
		$this->assertEquals("xml", get_file_ext("test.xml"));
	}

	function testParseFile() {
		$file_r = parse_file("test.xml");
		$this->assertEquals("test", $file_r['name']);
		$this->assertEquals("xml", $file_r['extension']);
	}

	function testIsValidExtension() {
		$this->assertEquals('xml', get_valid_extension("test.xml", 'html,txt,xml'));
		$this->assertFalse(get_valid_extension("test.doc", 'html,txt,xml'));
	}

	function testGetRelativeFilename() {
		$this->assertEquals('upload/deleteme', get_opendb_relative_file(get_opendb_file('upload/deleteme')));
	}

	function testGetThemeDirList() {
		$dirlist = get_dir_list("theme", 'local_is_exists_theme');
		$this->assertEquals(1, count($dirlist));
	}

	function testGetDirList() {
		$dirlist = get_dir_list("theme");
		$this->assertEquals(1, count($dirlist));
	}

	function testOpenDbFileExists() {
		$this->assertTrue(opendb_file_exists("upload/deleteme"));
		$this->assertFalse(opendb_file_exists("upload/deletemeX"));

		// test dir exists!
		$this->assertTrue(opendb_file_exists("upload/"));
		$this->assertFalse(opendb_file_exists("uploadX/"));

	}

	// 	function testDeleteFile() {
	// 	}
}
