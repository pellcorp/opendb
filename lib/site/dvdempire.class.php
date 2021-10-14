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
    
    -- CHANGLOG --
        
    Version		Comments
    -------		--------
    0.81		re-release using 0.81 site plugin architecture
    0.81p14		revisions for page format changes
        1.5.0b5		revisions:
            - fix Number of Discs 
            - fix audio_format and audio_lang
            - fix rating
            - added Audio Track (basically Audio Language + Audio Format)
            - added back image URL
            - added attributes for STUDIO, DIRECTOR, CREATORS, WRITERS, ARTISTS
              into s_item_types DVD, BD (dvd.sql, bd.sql)
            - changed genre type to accept and automatically add, and print new genre values if %display% is blank
              ( line 679 of lib/displayfields.php )
            - fix queryitemlisting to handle 'userid=-1' - listing now works again

 */
include_once("./lib/SitePlugin.class.php");

function get_page_block($blockid, $buffer) {
	$index = strpos($buffer, "<b>" . $blockid . "</b>");
	if ($index !== FALSE) {
		$i = 0;
		// get table just before $blockid match
		$start = strpos($buffer, '<table', $index);
		if ($start !== FALSE) {
			$depth = 1;
			$startindex = $start + 6;
			while ($depth > 0) {
				// we need to get the next open or close table, so we
				// can keep track of the depth.
				$openidx = strpos($buffer, "<table", $startindex);
				$closeidx = strpos($buffer, "</table>", $startindex);

				if ($openidx === FALSE) {
					if ($closeidx === FALSE) {
						$startindex = strlen($buffer);
						$depth = 0;
					} else {
						$depth--;
						$startindex = $closeidx + 8;//8=</table>
					}
				} else if ($openidx === FALSE) {
					$endindex = strlen($buffer);
					$depth = 0;
				} else if ($closeidx < $openidx) {
					$depth--;
					$startindex = $closeidx + 8;//8=</table>
				} else if ($openidx < $closeidx) { // open tag
					$depth++;
					$startindex = $openidx + 6;//6=<table
				}
			}//while

			return substr($buffer, $start, $startindex - $start);
		}
	}
}

function parse_page_block($blockid, $block) {
	$index = strpos($block, "<b>$blockid:</b>");
	if ($index !== FALSE) {
		$start = strpos($block, "<td", $index);
		if ($start !== FALSE) {
			$end = strpos($block, "</td>", $start);
			if ($end !== FALSE) {
				return html_entity_decode(strip_tags(substr($block, $start, $end - ($start))), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1');
			}
		}
	}

	// else
	return NULL;
}

function parse_film_info_block($blockid, $block) {
	$term = array('Actors', 'Directors', 'Producers', 'Writers', 'Creators');

	$start = strpos($block, "<b>$blockid:</b>");
	if ($start !== FALSE) {
		$start += strlen("<b>$blockid:</b>");

		$end = strlen($block);

		reset($term);
		foreach ($term as $value) {
			$idx = strpos($block, "<b>$value:</b>", $start);

			if ($idx !== FALSE && $idx < $end) {
				$end = $idx;
			}
		}

		$block = substr($block, $start, $end - $start);

		$block = str_replace("&#149;", ",", $block);
		$block = str_replace("&nbsp;", " ", $block);
		$block = html_entity_decode(strip_tags($block), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1');
		$block = str_replace(" , ", ",", $block);

		if ($block[0] == ',')
			$block = substr($block, 1);
		return explode(",", $block);
	}
}

class dvdempire extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		// standard block of code to cater for refresh option, where item already has
		// reference to site item unique ID.
		if (strlen($search_vars_r['dvdempr_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('dvdempr_id' => $search_vars_r['dvdempr_id']));
			return TRUE;
		}

		$item_type_url_options = $this->getConfigValue('item_type_url_config', $s_item_type);

		$pageBuffer = $this->fetchURI('http://www.dvdempire.com/exec/v5_search_item.asp?display_pic=1&page=' . $page_no . '&string=' . urlencode($search_vars_r['title']) . '&' . $item_type_url_options);
		if (strlen($pageBuffer) > 0) {//print_r($pageBuffer);
			if (preg_match_all('!<b><a href=[\'|"]/Exec/v4_item.asp\?item_id=([0-9]+)[\'|"]>(.*?)</a>!mi', $pageBuffer, $matches)) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					//<img src='http://cdn3a.dvdempire.org/products/48/1322848t.jpg'
					// Ensure an image is found for the specified item, before trying to include it in the listing.
					if (preg_match('!<img.*?src=[\'|"](http://\w*?\.dvdempire\.org/products/[0-9]*/' . $matches[1][$i] . 't.jpg)[\'|"]!i', $pageBuffer, $regs)) {
						$thumbimg = $regs[1];
						//print_r($regs);
					} else
						$thumbimg = NULL;

					//					if(strlen(trim($matches[3][$i]))>0)
					//						$title = $matches[2][$i]." ".trim($matches[3][$i]);
					//					else
					$title = $matches[2][$i];

					$this->addListingRow($title, $thumbimg, NULL, array('dvdempr_id' => $matches[1][$i]));
				}

				if (preg_match("/<b>([0-9]+)<\/b> Matches Found/i", $pageBuffer, $regs))
					$this->setTotalCount($regs[1]);
				else
					$this->setTotalCount(count($matches[1]));

				return TRUE;
			} else if (preg_match("/item_id=([]0-9]+)/", $this->getFetchedURILocation(), $matches)) {
				$this->addListingRow(NULL, NULL, NULL, array('dvdempr_id' => $matches[1]));
				return TRUE;
			} else {
				// no matches
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}

	function queryItem($search_attributes_r, $s_item_type) {
		$buffer = $this->fetchURI("http://www.dvdempire.com/Exec/v4_item.asp?item_id=" . $search_attributes_r['dvdempr_id']);
		if (strlen($buffer) > 0) {
			//<b>LIST:</b> $29.95<br /> 
			if (preg_match("/<b>LIST:<\/b>[\s]\\$([0-9.]*)/mi", $buffer, $regs)) {
				$listprice = trim($regs[1]);

				if (is_numeric($listprice) && preg_match("/<b>SAVE:<\/b>[\s]\\$([0-9.]*)/mi", $buffer, $regs)) {
					$saveprice = trim($regs[1]);
					if (is_numeric($saveprice))
						$saleprice = $listprice - $saveprice;
					else
						$saleprice = $listprice;
				} else {
					$saleprice = $listprice;
				}

				$this->addItemAttribute('listprice', $saleprice);
			}

			// plot
			$index = strpos($buffer, "<b>Synopsis</b>");
			if ($index !== FALSE) {
				$start = strpos($buffer, "<table", $index);
				if ($start !== FALSE) {
					$end = strpos($buffer, "</table>", $start);
					if ($end !== FALSE) {
						$plot = substr($buffer, $start, $end - $start);
						$plot = str_replace(">", "> ", $plot); // workaround for lack of spaces between HZML tags
						$plot = trim(preg_replace("/[\s]+/", " ", html_entity_decode(strip_tags($plot), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1')));
						$this->addItemAttribute('blurb', $plot);
					}
				}
			}

			//<b>Genre</b>: <nobr><a href="/exec/v2_category.asp?userid=99365484654163&amp;cat_id=529&amp;site_id=4&amp;site_media_id=2">Action</a></nobr>, <nobr><a href="/exec/v2_category.asp?userid=99365484654163&amp;cat_id=955&amp;site_id=4&amp;site_media_id=2">Military</a></nobr>, <nobr><a href="/exec/v2_category.asp?userid=99365484654163&amp;cat_id=595&amp;site_id=4&amp;site_media_id=2">Vietnam War</a></nobr>, <nobr><a href="/exec/v2_category.asp?userid=99365484654163&amp;cat_id=567&amp;site_id=4&amp;site_media_id=2">War</a>
			$start = strpos($buffer, "<b>Genre</b>");
			if ($start !== FALSE) {
				$this->addItemAttribute('start', $start);
				//					$this->addItemAttribute('buffer', $buffer);
				$end = strpos($buffer, "</td>", $start);
				$genre = trim(substr($buffer, $start, $end - $start));
				if (preg_match_all("!<a href=\"/exec/v2_category.asp[^\"]*\">([^<]*)</a>!", $genre, $matches)) { 
				//  if(preg_match_all("!<a href=\"/exec/v2_category.asp\?.*\">([^<]*)</a>!", substr($buffer, $start, $end - $start), $matches))
					$this->addItemAttribute('genre', str_replace("'", "", str_replace(" ", "", $matches[1])));
					//print_r($matches[1]);
				}
			}

			$this->parse_dvdempire_video_data($search_attributes_r, $s_item_type, $buffer);

			// indicate everything was ok
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function parse_dvdempire_video_data($search_attributes_r, $s_item_type, $buffer) {
		//<title>DVD Empire - Item - Rambo III: Ultimate Edition  /  DVD-Video</title>
		if (preg_match("!<title>DVD Empire - Item - (.*)/[\s]*DVD-Video<\/title>!m", $buffer, $regs)) {
			$this->addItemAttribute('title', str_replace("\"", "", html_entity_decode(strip_tags($regs[1]), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1')));
		}
		//<title>Buy Just Friends DVD @ DVD Empire </title>
		if (preg_match("!<title>Buy (.*) DVD @ DVD Empire <\/title>!", $buffer, $matches)) {
			$this->addItemAttribute('title', $matches[1]);
		}

		if (preg_match("/Region ([0-9]+)/i", $buffer, $regs)) {
			$this->addItemAttribute('dvd_region', $regs[1]);
		} else {
			$this->addItemAttribute('dvd_region', '1'); // otherwise assume US region
		}

		$product_info = get_page_block('Product Information', $buffer);

		$this->addItemAttribute('dvd_extras',
			preg_replace(array("'[\n|\r]+'", "'[\t ]+'"), array("\n", " "), html_entity_decode(strip_tags(str_replace("<br>", "\n", parse_page_block('Features', $product_info))), ENT_COMPAT, get_opendb_config_var('themes', 'charset') == 'utf-8' ? 'UTF-8' : 'ISO-8859-1')));

		$this->addItemAttribute('vid_format', 'NTSC'); // An American site, so most likely NTSC

		$video = parse_page_block('Video', $product_info);
		if (strlen($video) > 0) {
			if (strpos($video, 'Anamorphic') !== FALSE) {
				$this->addItemAttribute('anamorphic', 'Y');
			}

			$ratio_list_r = array('1.33', '1.66', '1.78', '1.85', '2.35', '2.78');
			foreach ($ratio_list_r as $ratio) {
				if (preg_match('/' . $ratio . ':1/', $video)) {
					$this->addItemAttribute('ratio', $ratio);
				}
			}
		}

		$audio = parse_page_block('Audio', $product_info);
		if (strlen($audio) > 0) {
			$audio_r = explode_lines(strip_tags(preg_replace("/<br>/i", "\n", $audio)));
			if (is_not_empty_array($audio_r)) {
				//ENGLISH: Dolby Digital 5.1 [CC]
				for ($i = 0; $i < count($audio_r); $i++) {
					if (preg_match("/([A-Z]+): ([^$]+)$/Ui", $audio_r[$i], $matches)) {
						if (ends_with($matches[2], "[CC]")) {
							$audio_format = trim(substr($matches[2], 0, -5));
						} else {
							$audio_format = trim($matches[2]);
						}

						$this->addItemAttribute('audiotrk', $matches[0]);
						$this->addItemAttribute('audio_lang', $matches[1]);
						$this->addItemAttribute('audio_format', $audio_format);
					}
				}
			}
		}

		$subtitles = parse_page_block('Subtitles', $product_info);
		if (strpos($subtitles, 'None') === FALSE)
			$this->addItemAttribute('subtitles', trim_explode(",", $subtitles));

		if (preg_match("/<b>Packaging:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('dvd_packge', $regs[1]);
		}

		if (preg_match("/<b>Disc:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('dvd_disc', $regs[1]);
		}

		if (preg_match("/<b>Number of Discs:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$no_discs = trim($regs[1]); // fixed
			if (strlen($no_discs) > 0) {
				$this->addItemAttribute('no_discs', $no_discs);
			}
		}

		if (preg_match("/<b>Item Code:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('item_code', $regs[1]);
		}

		if (preg_match("/<b>Chapters:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('dvd_chptrs', $regs[1]);
		}

		if (preg_match("/<b>UPC Code:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('upc_id', $regs[1]);
		}

		if (preg_match("/<b>Studio:<\/b> <a href=\'.*?\'>(.*?)<\/a><br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('studio', $regs[1]);
		}

		if (preg_match("/<b>Production Year:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('year', $regs[1]);
		}

		if (preg_match("/<b>Release Date:<\/b> (.*?)<br \/>/i", $product_info, $regs)) {
			$this->addItemAttribute('rel_date', $regs[1]);
		}

		if (preg_match("/<b>DVD Year:<\/b>(.*?)<br>/i", $product_info, $regs)) {
			$this->addItemAttribute('dvd_rel_dt', $regs[1]);
		}

		if (preg_match("/<b>Length:<\/b>[\s]([0-9]*)/i", $product_info, $regs)) {
			$this->addItemAttribute('run_time', $regs[1]);
		}

		if (preg_match("/<b>Rating:<\/b>(.*?)<br \/>/i", $product_info, $regs)) {
			$age_rating = trim($regs[1]);

			if (strlen($age_rating) > 0) {
				$this->addItemAttribute('age_rating', $age_rating);
			}
		}

		//<b>Cast & Crew</b>
		$film_info = get_page_block('Cast & Crew', $buffer);
		if (strlen($film_info) > 0) {
			$this->addItemAttribute('actors', parse_film_info_block('Actors', $film_info));
			$this->addItemAttribute('director', parse_film_info_block('Directors', $film_info));
			$this->addItemAttribute('producers', parse_film_info_block('Producers', $film_info));
			$this->addItemAttribute('writers', parse_film_info_block('Writers', $film_info));
			$this->addItemAttribute('creators', parse_film_info_block('Creators', $film_info));
		}

		$index = strpos($buffer, "<b>Reviews</b>");
		if ($index !== FALSE) {
			$index += strlen("<b>Reviews</b>");
			$reviews = get_page_block('Reviews', substr($buffer, $index));
			if (strlen($reviews) > 0) {
				// <b>Overall Rating:</b> <img src="/Graphics/Running/v4_rating0.gif" border='0' vspace='0' hspace='1'><img src="/Graphics/Running/v4_rating0.gif" border='0' vspace='0' hspace='1'><img src="/Graphics/Running/v4_rating0.gif" border='0' vspace='0' hspace='1'><img src="/Graphics/Running/v4_rating0.gif" border='0' vspace='0' hspace='1'><img src="/Graphics/Running/v4_rating1.gif" border='0' vspace='0' hspace='1'><span class='fontsmall'>&nbsp;<b>4.45</b> out of <b>5</b>, including <b>41</b> reviews<br>

			}
		}

		// ----------------------------
		// Now the Cover images
		// ----------------------------
		//http://images2.dvdempire.com/gen/movies/3073.jpg
		if (preg_match('!<img src=[\'|"](http://\w*?\.dvdempire\.org/products/[0-9]*/' . $search_attributes_r['dvdempr_id'] . '\.jpg)[\'|"]!', $buffer, $regs)) {
			$this->addItemAttribute('thumbimg', $regs[1]);
		}

		// Now we need to get the cover images
		$buffer = $this->fetchURI('http://www.dvdempire.com/Exec/v4_item.asp?item_id=' . $search_attributes_r['dvdempr_id'] . '&tab=5');
		if (strlen($buffer) > 0) {
			//<img src="http://images2.dvdempire.com/gen/movies/3073h.jpg" valign="top" align="middle" border="0" hspace="0" vspace="0">
			if (preg_match('!<img src=[\'|"](http://\w*?\.dvdempire\.org/products/[0-9]*/' . $search_attributes_r['dvdempr_id'] . 'h\.jpg)[\'|"]!', $buffer, $regs)) {
				$this->addItemAttribute('imageurl', $regs[1]);
				$this->addItemAttribute('imageurlf', $regs[1]);
			}
		}

		$buffer = $this->fetchURI('http://www.dvdempire.com/Exec/v4_item.asp?item_id=' . $search_attributes_r['dvdempr_id'] . '&tab=5&back=1');
		if (strlen($buffer) > 0) {
			if (preg_match('!<img src=[\'|"](http://\w*?\.dvdempire\.org/products/[0-9]*/' . $search_attributes_r['dvdempr_id'] . 'bh\.jpg)[\'|"]!', $buffer, $regs)) {
				$this->addItemAttribute('imageurlb', $regs[1]);
			}
		}
	}
}
?>
