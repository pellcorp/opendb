<?php
/*
    eMusic SitePlugin for Open Media Collectors Database
    Copyright (C) 2008 Jeroen Budts
    http://gluefish.net/opendb/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 3
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; If not, see <http://www.gnu.org/licenses/>.

    Using
    -------
    You have two ways to add music
    1. use the album title: this will search eMusic for albums with the given title
    2. use the album url: either paste the complete url, or the part after /album/
       in the url, of the page on eMusic. By example to import Live At Tonic by Chritian
       McBride, simply paste
       http://www.emusic.com/album/Christian-McBride-Live-At-Tonic-MP3-Download/10915705.html
       in the 'eMusic Link'-field.
    
    
    Version History
    -------
 * 1.1 (2008-03-02)
        - adapted changes to the eMusic site which broke the plugin
 * 1.0 (2008-02-25)
        - initial version

 */

include_once("./lib/SitePlugin.class.php");

class emusic extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['emusic_lnk']) > 0) {
			if ($emusicLink = $this->_parseEmusicLink($search_vars_r['emusic_lnk'])) {
				$this->addListingRow(NULL, NULL, NULL, array('emusic_lnk' => $emusicLink));
				return TRUE;
			} else {
				$this->setError('The eMusic Link is invalid: ' . $search_vars_r['emusic_lnk'], 'The eMusic Link should either be a complete url of an album\'s page on eMusic.com' . ' or everything that follows \'/album/\' in the url.');
			}
		}

		if (strlen($search_vars_r['title']) > 0) {
			$this->searchOnAlbum($search_vars_r['title'], $offset);
			return TRUE;
		}

		return FALSE;
	}

	function searchOnAlbum($album, $offset) {
		// http://www.emusic.com/search.html?mode=b&off=0&QT=Live%20At%20Tonic
		$page = $this->fetchURI("http://www.emusic.com/search.html?mode=b&off=" . intval($offset) . "&QT=" . urlencode($album));

		if (strlen($page) <= 0) {
			return FALSE;
		}

		/*
		<tr class="rowOdd">
		    <td class="column1">
		        <div class="shadow1"><a href="/album/Christian-McBride-Live-At-Tonic-MP3-Download/10915705.html"><img src="/img/album/109/157/10915705_60_60.jpeg" title="Live At Tonic" alt="Live At Tonic"></a></div>
		        <div class="mediaTitle">
		            <a href='/samples/m3u/album/10915705/0.m3u'><img src="/images/ctlg/listen.gif?v=20050520,1,1" alt="Listen" title="Listen" class="listenBrowse"></a>
		            <p><a href="/album/Christian-McBride-Live-At-Tonic-MP3-Download/10915705.html">Live At Tonic</a></p>
		        </div>
		    </td>
		    <td><a href="/artist/Christian-McBride-MP3-Download/11487301.html">Christian McBride</a></td>
		    <td><a href="/genre/291.html">Jazz</a></td>
		    <td class="column4"><a href="/label/Ropeadope-Records-IODA-MP3-Download/121252.html">Ropeadope Records / IODA</a>&nbsp;</td>
		</tr>
		 */
		$RESULT_ROW_REGEX = "!<img src=\"(/img/album/[\d_/]+\.jpe?g)\" title=\".*\" alt=\".*\">.*<p><a href=\"/album/(.+\.html)\">(.+)</a></p>.+<a href=\"/artist/[\w-_]+/\d+\.html\">(.+)</a>.*<a href=\"/genre/\d+\.html\">(.+)</a>!sU";

		if (preg_match_all($RESULT_ROW_REGEX, $page, $matches)) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				$this->addListingRow($matches[3][$i], "http://www.emusic.com" . $matches[1][$i], $matches[4][$i] . " (" . $matches[5][$i] . ")", array('emusic_lnk' => $matches[2][$i]));
			}
		}
		return TRUE;
	}

	function queryItem($search_attributes_r, $s_item_type) {
		// http://www.emusic.com/album/Christian-McBride-Live-At-Tonic-MP3-Download/10915705.html
		$page = $this->fetchURI("http://www.emusic.com/album/" . $search_attributes_r['emusic_lnk']);

		if (strlen($page) <= 0) {
			return FALSE;
		}

		$this->addTitle($page);
		$this->addArtist($page);
		$this->addYear($page);
		$this->addImage($page);
		$this->addTracks($page); // TODO: performers; tracklength
		$this->addTime($page);
		$this->addGenre($page);

		return TRUE;
	}

	function addGenre($page) {
		// Genre:</span> <a href="/genre/291.html">Jazz</a>
		if (preg_match("!Genre:\s*<a href=\"/genre/\d+\.html\">(.+)</a>!", $page, $matches)) {
			$this->addItemAttribute('musicgenre', $matches[1]);
		}
	}

	function addTime($page) {
		//Total Length:</span> 71:30</div>
		if ($discs = preg_match_all("!Total Length:</span> (\d\d:\d\d)</p>!", $page, $matches)) {
			$this->addItemAttribute('no_discs', $discs);

			if (1 == $discs) { // ok only one disc
				$this->addItemAttribute('run_time', $matches[1][0]);
			} else {
				// more then one disc, make the sum of all the times
				$totalTime = "0:00";
				for ($i = 0; $i < count($matches[1]); $i++) {
					$totalTime = $this->_calculateTotalTime($totalTime, $matches[1][$i]);
					//$this->addItemAttribute('run_time', $matches[1][$i]);
				}
				$this->addItemAttribute('run_time', $totalTime);
			}
		}
	}

	function addTracks($page) {
		//<td class="track">
		//Technicolor Nightmare
		//
		//
		//<div>
		$tracks = NULL;

		if (preg_match_all("!<td class=\"track\">\s*(.+)\s*<div!m", $page, $matches)) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				$tracks[$i] = $matches[1][$i];
			}
		}

		if (is_array($tracks)) {
			$this->addItemAttribute('tracks', $tracks);
		}
	}

	function addImage($page) {
		//IMAGEURL
		// <td class="cover"><div class="ds1"><div class="ds2"><div class="ds3"><img src="/img/album/109/157/10915705_155_155.jpeg" alt="Live At Tonic by Christian McBride" /></div></div></div></td>
		if (preg_match("!<img src=\"(/img/album/[\d/_]+\.jpe?g)\" alt=\"!", $page, $matches)) {
			$this->addItemAttribute('imageurl', "http://www.emusic.com" . $matches[1]);
		}
	}

	function addYear($page) {
		// Release Date:</span> 2 mei 2006
		// Release Date:</span> 10 juli 2007<br />
		if (preg_match("!Release Date: <span>.*(\d\d\d\d)!", $page, $matches)) {
			$this->addItemAttribute('year', $matches[1]);
		}
	}

	function addArtist($page) {
		// <span class="artist"><a href="/artist/Christian-McBride-MP3-Download/11487301.html">Christian McBride</a></span>
		if (preg_match("!Artist: <a href=\"/artist/[\w-\d]+/\d+\.html\">(.+)</a></h3>!", $page, $matches)) {
			$this->addItemAttribute('artist', $matches[1]);
		}
	}

	function addTitle($page) {
		// <h1 class="albumTitle">Live At Tonic</h1>
		if (preg_match("!<h1 id=\"album\d+\">(.+)</h1>!", $page, $matches)) {
			$this->addItemAttribute('title', $matches[1]);
		}
	}

	// add 2 times
	// this function is prolly lame, but i wrote it in 5 minutes ;)
	// and it's been a veeeery long time since my last php programming,
	// so it just does the thing but is ugly as hell
	function _calculateTotalTime($totalTime, $timeToAdd) {
		$arrTotalTime = explode(':', $totalTime);
		$arrTimeToAdd = explode(':', $timeToAdd);

		$minutes = $arrTotalTime[0] + $arrTimeToAdd[0];
		$seconds = $arrTotalTime[1] + $arrTimeToAdd[1];

		if ($seconds >= 60) {
			$minutes++;
			$seconds -= 60;
		}

		$strSeconds = $seconds;
		if (0 == $seconds) {
			$strSeconds = "00";
		} else if ($seconds < 10) {
			$strSeconds = "0" . $seconds;
		}

		return ((0 == $minutes) ? '0' : $minutes) . ':' . $strSeconds;
	}

	function _parseEmusicLink($emusicLink) {
		if (preg_match("!^(http://(www\.)?emusic\.com/album/)?([\w\d-]+/\d+\.html?)$!", $emusicLink, $matches)) {
			return $matches[3];
		}
		return FALSE;
	}
}
?>
