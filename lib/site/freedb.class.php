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
 * 
 * Search for 'Rambo' to return a list of various titles.
        Search for '12 Angry Men' to get an exact title match.
        Search for 'faddsda' to trigger a search error.
 */
include_once("./lib/SitePlugin.class.php");

function get_hello_param() {
	return "opendb+" . get_http_env('HTTP_HOST') . "+" . urlencode(get_opendb_config_var('site', 'title')) . "+" . get_opendb_version();
}

// discid search

class freedb extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function doDiscidSearch($search_vars_r) {
		$freedb_categories = array('data', 'folk', 'jazz', 'misc', 'rock', 'country', 'blues', 'newage', 'reggae', 'classical', 'soundtrack');

		$entries = NULL;

		reset($freedb_categories);
		foreach ($freedb_categories as $cat) {
			$result = $this->fetchURI("http://freedb2.org/~cddb/cddb.cgi?cmd=cddb+read+$cat+" . $search_vars_r['freedb_id'] . "&hello=" . get_hello_param() . "&proto=5");
			if (preg_match("/([0-9]+) /", $result, $regs) && $regs[1] != '401') {
				$entry['cddbgenre'] = $cat;
				$entry['freedb_id'] = $search_vars_r['freedb_id'];

				$title_r = $this->parseTitle($result);
				if (is_array($title_r)) {
					$entry['title'] = $title_r['artist'];
					$entry['artist'] = $title_r['artist'];
				}

				$entries[] = $entry;
			}
		}

		return $entries;
	}

	function doAlbumSearch($search_vars_r) {
		$entries = NULL;

		$pageBuffer = $this->fetchURI("http://freedb2.org/~cddb/cddb.cgi?cmd=cddb+album+" . urlencode($search_vars_r['title']) . "&hello=" . get_hello_param() . "&proto=5");
		if (strlen($pageBuffer) > 0) {
			$lines = preg_split("/[\r\n]/m", trim($pageBuffer), NULL, PREG_SPLIT_NO_EMPTY);
			if (is_not_empty_array($lines)) {
				$code = NULL;
				if (preg_match("/([0-9]+) ([^$]+)/", $lines[0], $matches)) {
					$code = $matches[1];
				}

				if ($code == '211' || $code == '210') {
					array_shift($lines);
					array_pop($lines);
				} else {
					return TRUE;
				}

				//blues 590ff119 Various Artists / Cool, Cool Blues, The Classic Sides (1951-54) - Disc B (Jackson, MS)
				reset($lines);
				foreach ($lines as $line) {
					$entry = NULL;
					if (preg_match("/([^\ ]+) ([^\ ]+) ([^$]+)/", $line, $matches)) {
						$entry['cddbgenre'] = $matches[1];
						$entry['freedb_id'] = $matches[2];
						$entry['title'] = $matches[3];

						$idx = strrpos($entry['title'], "/");
						if ($idx !== FALSE) {
							$entry['artist'] = trim(substr($entry['title'], 0, $idx));
							$entry['title'] = initcap(substr($entry['title'], $idx + 1));
						}

						$entries[] = $entry;
					}
				}
			}
		}
		return $entries;
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['freedb_id']) > 0 && strlen($search_vars_r['cddbgenre']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('freedb_id' => $search_vars_r['freedb_id'], 'cddbgenre' => $search_vars_r['cddbgenre']));
			return TRUE;
		} else {
			if (strlen($search_vars_r['freedb_id']) > 0) {
				$entries = $this->doDiscidSearch($search_vars_r);
			} else {
				$entries = $this->doAlbumSearch($search_vars_r);
			}

			if (is_array($entries)) {
				foreach ($entries as $entry) {
					$this->addListingRow($entry['artist'] . ' / ' . $entry['title'], NULL, $entry['cddbgenre'] . '/' . $entry['freedb_id'], array('freedb_id' => $entry['freedb_id'], 'cddbgenre' => $entry['cddbgenre']));
				}
			}

			//else no results found
			return TRUE;
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		$entryBlock = $this->fetchURI("http://www.freedb2.org/freedb/" . $search_attributes_r['cddbgenre'] . "/" . $search_attributes_r['freedb_id']);

		// no sense going any further here.
		if (strlen($entryBlock) == 0) {
			return FALSE;
		}

		$title_r = $this->parseTitle($entryBlock);
		if (is_array($title_r)) {
			$this->addItemAttribute('artist', $title_r['artist']);
			$this->addItemAttribute('title', $title_r['title']);
		}

		$discLength = $this->parseDiscLength($entryBlock);
		if ($discLength != NULL) {
			$this->addItemAttribute('run_time', $discLength);
		}

		$year = $this->parseYear($entryBlock);
		if ($year != NULL) {
			$this->addItemAttribute('year', $year);
		}

		$genre = $this->parseGenre($entryBlock);
		if ($genre != NULL) {
			$this->addItemAttribute('genre', $genre);
		}

		$tracks = $this->parseTracks($entryBlock);
		if (is_array($tracks)) {
			$this->addItemAttribute('tracks', $tracks);
		}

		return TRUE;
	}

	function parseDiscLength($entryBlock) {
		// Get runtime
		if (preg_match("/^# Disc length: ([^$]*)$/mU", $entryBlock, $regs)) {
			if (preg_match("/([0-9]+) seconds/", $regs[1], $regs2)) {
				$minutes = (int) ($regs2[1] / 60);
				$seconds = $regs2[1] % 60;

				//Prefix "0", so that seconds are properly formatted.
				if (strlen($seconds) < 2)
					$seconds = "0" . $seconds;

				return $minutes . ':' . $seconds;
			}
		}

		return NULL;
	}

	function parseTitle($entryBlock) {
		// get title.
		if (preg_match_all("/^DTITLE=([^$]*)$/mU", $entryBlock, $matches)) {
			$title = '';
			$artist = '';
			for ($i = 0; $i < count($matches[1]); $i++) {
				$title .= $matches[1][$i];
			}

			$index = strrpos($title, "/");
			if ($index !== FALSE) {
				$artist = trim(substr($title, 0, $index));//1="/"
				$title = trim(substr($title, $index + 1));//1="/"
			}

			// Ensure title is properly formatted.
			$title = initcap($title);

			return array('title' => $title, 'artist' => $artist);
		}

		return NULL;
	}

	function parseYear($entryBlock) {
		//get year
		if (preg_match("/^DYEAR=([^$]*)$/mU", $entryBlock, $regs)) {
			if (strlen(trim($regs[1])) > 0) {
				return $regs[1];
			}
		}

		return NULL;
	}

	function parseGenre($entryBlock) {
		// get extended genre
		if (preg_match("/^DGENRE=([^$]*)$/mU", $entryBlock, $regs)) {
			if (strlen(trim($regs[1])) > 0) {
				return $regs[1];
			}
		}
		return NULL;
	}

	function parseTracks($entryBlock) {
		// Collect titles in two passes
		// In the first pass, just find the raw data: the lines that begin
		// with TITLEnn. A long title can wrap around, e.g.:
		//	TITLE5=This is a ve
		//	TITLE5=ry long title
		// so we append to what's already in $entry[tracks][n].
		$tracks = NULL;
		if (preg_match_all("/^TTITLE([0-9]+)=([^$]+)$/mU", $entryBlock, $matches)) {
			for ($i = 0; $i < count($matches[2]); $i++) {
				$tracks[$matches[1][$i]] .= $matches[2][$i];
			}
		}

		// Now that we have the raw titles, clean them up. This is done in
		// a separate loop rather than the preceding one because a title
		// might be split in the middle of a word, so ucwords() would
		// capitalize whatever happened to be at the beginning of a split
		// line ("This Is A VeRy Long Title", above).
		if (is_array($tracks)) {
			for ($i = 0; $i < count($tracks); $i++) {
				$tracks[$i] = initcap(preg_replace('/[\s]+/', ' ', trim($tracks[$i])));
			}
		}
		return $tracks;
	}
}
?>
