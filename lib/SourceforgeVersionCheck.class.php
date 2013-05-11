<?php
/* 	
	OpenDb Media Collector Database
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
include_once("./lib/database.php");
include_once("./lib/fileutils.php");
include_once("./lib/utils.php");
include_once("./lib/OpenDbSnoopy.class.inc");

class SourceforgeVersionCheck
{
	var $versionString;
	var $htmlPage;
	
	/**
	 * @param File $htmlReleaseFile - if file reference specified, then use this for the page contents to parse,
	 * 						this is for unit testing only.	
	 * @return SourceforgeVersionCheck
	 */
	function SourceforgeVersionCheck($htmlReleaseFile = NULL)
	{
		if(strlen($htmlReleaseFile)>0  && file_exists($htmlReleaseFile)) {
			$this->latestReleasePage = file_get_contents($htmlReleaseFile);
		}
		
		if(strlen($this->latestReleasePage)==0) {
			$snoopy = new OpenDbSnoopy();
			$pageContents = $snoopy->fetchURI('http://sourceforge.net/project/showfiles.php?group_id=37089&package_id=29402');
			if($pageContents!==FALSE) {
				$this->latestReleasePage = $pageContents;
			}
		}
		
		$this->initVersionCheck();
	}
	
	function initVersionCheck() {
		$this->versionString = FALSE;
		
		if(strlen($this->latestReleasePage)>0) {
			//<tr class="release current" id="pkg0_1rel0">
			//id="pkg0_1rel0_0">1.0.4pl1</a> 
			if(preg_match("!<tr class=\"release current\" id=\"[^\"]*\">.*?id=\"pkg0_1rel0_0\">([^<]+)</a>!ms", $this->latestReleasePage, $matches)) {
				$this->versionString = $matches[1];
			}
		}
	}
	
	function getVersion() {
		return $this->versionString;
	}
	
	function isUpdatedVersion($currentVersion) {
		return opendb_version_compare($this->getVersion(), $currentVersion, '>');
	}
}
?>