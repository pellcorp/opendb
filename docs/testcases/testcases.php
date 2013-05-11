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

chdir('../../');

// This must be first - includes config.php
require_once("./include/begin.inc.php");

require_once('PHPUnit.php');
require_once('PHPUnit/GUI/SetupDecorator.php');
require_once('PHPUnit/GUI/HTML.php');

$gui = new PHPUnit_GUI_HTML();

$handle=opendir("./docs/testcases/");
while ($file = readdir($handle))
{
	if($file != "." && $file != ".." && preg_match("/([a-zA-Z0-9]+)\.class\.php/", $file, $regs))
	{
		include_once("./docs/testcases/".$file);
		
		$className = basename($file,'.class.php');
		if (class_exists($className)) {
			$suites[] = new PHPUnit_TestSuite($className);
		}
	}
}
closedir($handle);

$gui->addSuites($suites);
$gui->show();

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>