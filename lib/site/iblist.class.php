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

    Change Information
    -------------------

    0.81	Upgraded to 0.81+ compatible site plugin format
    0.81p1  Fixes thanks to Adam Wolf <wolf0436@umn.edu>
    0.81p2  Fixes thanks to Esben Madsen <github.com_opendb@minpingvin.dk>

 */
include_once("./lib/SitePlugin.class.php");

class iblist extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		// standard block of code to cater for refresh option, where item already has
		// reference to site item unique ID.
		if (strlen($search_vars_r['iblist_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('iblist_id' => $search_vars_r['iblist_id']));
			return TRUE;
		}

		$search_clause = "";
		if (strlen($search_vars_r['author']) > 0)
			$search_clause = "item=" . urlencode($search_vars_r['author']);// . "&author=on";
		else if (strlen($search_vars_r['title']) > 0)
			$search_clause = "item=" . urlencode($search_vars_r['title']);// . "&title=on";
		else if (strlen($search_vars_r['isbn']) > 0)
			$search_clause = "item=" . urlencode($search_vars_r['isbn']);// . "&isbn=on";
		else if (strlen($search_vars_r['series']) > 0)
			$search_clause = "item=" . urlencode($search_vars_r['series']);// . "&series=on";
		else if (strlen($search_vars_r['description']) > 0)
			$search_clause = "item=" . urlencode($search_vars_r['description']);// . "&description=on";

//		$pageBuffer = $this->fetchURI("http://www.iblist.com/search/advanced_search.php?$search_clause&next=$offset");
		$pageBuffer = $this->fetchURI("http://www.iblist.com/search/search.php?$search_clause&next=$offset");

		// find out how many matches we have.
//		if (preg_match("!<b>\[</b>([0-9]+) <b>-</b> ([0-9]+) <b>out of</b> ([0-9]+)<b>\]</b>!i", $pageBuffer, $matches)) {
		if (preg_match("!([0-9]+) - ([0-9]+) out of ([0-9]+)!i", $pageBuffer, $matches)) {
			$this->setTotalCount($matches[3]);

//			if (preg_match_all("!<LI><A HREF=\"http://www.iblist.com/book([0-9]+).htm\">([^<]+)</A> by <A HREF=\"[^\"]+\">([^<]+)</A> </LI>!i", $pageBuffer, $matches2)) {
			if (preg_match_all("!<LI><A HREF=\"http://www.iblist.com/book([0-9]+).htm\">([^<]+)</A> by <A HREF=\"[^\"]+\">([^<]+)</A></LI>!i", $pageBuffer, $matches2)) {
				for ($i = 0; $i < count($matches2[0]); $i++) {
//					$this->addListingRow($matches2[2][$i] . ' by ' . trim($matches2[3][$i]), "http://www.iblist.com/images/covers/" . $matches2[1][$i] . ".jpg", NULL, array('iblist_id' => $matches2[1][$i]));
// image id is wrong, so ignore the url
					$this->addListingRow($matches2[2][$i] . ' by ' . trim($matches2[3][$i]), '', NULL, array('iblist_id' => $matches2[1][$i]));
				}
			}
		}

		return TRUE;
	}

	function queryItem($search_attributes_r, $s_item_type) {
		$entryBlock = $this->fetchURI("http://www.iblist.com/book.php?id=" . $search_attributes_r['iblist_id']);

		// no sense going any further here.
		if (strlen($entryBlock) > 0) {
//			if (preg_match("!href=\"http://www.iblist.com/book" . $search_attributes_r['iblist_id'] . ".htm\">([^(]*)\(([^)]*)\)([^<]+)</a>!i", $entryBlock, $matches)) {
			if (preg_match("!href=\"http://www.iblist.com/book" . $search_attributes_r['iblist_id'] . ".htm\">([^(]*)\(([^)]*)\)([^<]*)</a>!i", $entryBlock, $matches)) {
				$this->addItemAttribute('title', $matches[1]);
				$this->addItemAttribute('pub_date', $matches[2]);
			}

			if (preg_match("!href=\"http://www.iblist.com/author([0-9]+).htm\">([^<]*)</A>!i", $entryBlock, $matches)) {
				$this->addItemAttribute('iblist_author_id', $matches[1]);
				$this->addItemAttribute('author', $matches[2]);
			}

			//<img src="http://www.iblist.com/images/covers/2479.jpg" alt="cover" border="1">
//			if (preg_match("!<img src=\"([^\"]+)\" alt=\"Cover[^>]*>!i", $entryBlock, $matches)) {
//new format: <img src="thumbs/covers/7607.jpg" width="85" alt="cover" border="1" /
			if (preg_match("!<img src=\"([^\"]+)\".*?alt=\"Cover[^>]*>!i", $entryBlock, $matches)) {
//				$this->addItemAttribute('imageurl', $matches[1]);
				$this->addItemAttribute('imageurl', "http://www.iblist.com/$matches[1]");
			}

//			if (preg_match("!<P><i>Series:</i> <A href=\"series([0-9]+).htm\">([^<]*)</a><BR>!i", $entryBlock, $matches)) {
			if (preg_match("!<p><i>Series:</i> <a href=\"series([0-9]+).htm\">([^<]*)</a>!i", $entryBlock, $matches)) {
				$this->addItemAttribute('series_id', $matches[1]);
				$this->addItemAttribute('series', $matches[2]);
				if (preg_match("!<i>Part:</i> ([0-9]+)!i", $entryBlock, $matches2)) {
//					$this->addItemAttribute('series_part', $matches2[1]);
// length of attributes is limited to 10 chars
					$this->addItemAttribute('series_pt', $matches2[1]);
				}
			}

//			if (preg_match("!<i>ISBN:</i> ([0-9]+)!i", $entryBlock, $matches)) {
			if (preg_match("!<i>ISBN:</i> <a[^>]+>([0-9]+)!i", $entryBlock, $matches)) {
				$this->addItemAttribute('isbn', $matches[1]);
			}

			$start = strpos($entryBlock, '<i>Genre:</i>');
			if ($start !== FALSE) {
				$end = strpos($entryBlock, "<br", $start + 13);//<i>Genre:</i> - unended tag due to optional space and /
				if ($end !== FALSE) {
					$genre = substr($entryBlock, $start + 13, $end - ($start + 13));//13=<i>Genre:</i>

					$genre_r = explode("&rarr;", $genre);
					if (is_array($genre_r)) {
						for ($i = 0; $i < count($genre_r); $i++) {
							$genre_r[$i] = html_entity_decode(strip_tags(trim($genre_r[$i])));
							// Since iblist has a different genre names than the ones in the database...
							$renames=array('!science fiction!i' => 'Sci-Fi',
										   '!.* comedy[ ,]*.*!i' => 'Comedy' // catch eg. "Humorous, Parody and Comedy"
										   );  
							$genre_r[$i]=preg_replace(array_keys($renames),array_values($renames),$genre_r[$i]);
							// only include if genre has no spaces in it.
							if (strpos($genre_r[$i], " ") === FALSE) {
								// in case there is a combined genre split it up.
								if (strpos($genre_r[$i], "/") !== FALSE) {
									$genre2_r = explode("/", $genre_r[$i]);
									for ($j = 0; $j < count($genre2_r); $j++) {
										$this->addItemAttribute('genre', $genre2_r[$j]);
									}
								} else {
									$this->addItemAttribute('genre', $genre_r[$i]);
								}
							}
						}
					}
				}
			}
/*
			$start = strpos($entryBlock, '<i>Synopsis:</i><br><div class="indent">');
			if ($start !== FALSE) {
				$end = strpos($entryBlock, "</div>", $start + 40);//<i>Synopsis:</i><br><div class="indent">
				if ($end !== FALSE) {
					$blurb = substr($entryBlock, $start + 40, $end - ($start + 40));//40=<i>Synopsis:</i><br><div class="indent">
					$this->addItemAttribute('plot', $blurb);
				}
			}
*/		
			if (preg_match("!<i>Summary.*?<div class=\"indent\">(.*?)</div>!si",$entryBlock,$matches)) {
				$this->addItemAttribute('plot',html_entity_decode(strip_tags($matches[1])));
			}

			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>
