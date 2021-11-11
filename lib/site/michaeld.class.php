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
include_once("./lib/item_attribute.php");

class michaeld extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		if (strlen($search_vars_r['michaeldid']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('michaeldid' => $search_vars_r['michaeldid']));
			return TRUE;
		} else {
			// Normal entry point: they only gave us a title to search for.  Show
			// them a list.  Take out the commas and "the" - michaeld is fussier than IMDb.
			$title = trim(preg_replace('#^the(\W)#i', '$1', strtr($search_vars_r['title'], ',', ' ')));

			$pageBuffer = $this->fetchURI('http://www.michaeldvd.com.au/Search/TitleSearch.asp?title=' . rawurlencode(strtolower($title)));
			if (strlen($pageBuffer) > 0) {
				if (preg_match_all('#/Discs/Disc.asp\?ID=(\d+)">(.+?)</a>(.*?)</tr>#is', $pageBuffer, $matches)) {
					for ($i = 0; $i < count($matches[0]); $i++) {
						$comments = '';
						if (preg_match_all('#<td.*?>(.+?)</td>#is', $matches[3][$i], $matches2)) {
							for ($j = 0; $j < count($matches2[0]); $j++) {
								if (strlen($comments) > 0)
									$comments .= "\n";
								$comments .= trim(strip_tags($matches2[1][$j]));
							}
						}

						$this->addListingRow($matches[2][$i], 'http://www.michaeldvd.com.au/CoverArt/' . $matches[1][$i] . '.jpg', $comments, array('michaeldid' => $matches[1][$i]));

					} //for($i=0; $i<count($matches[0]); $i++)

					//print_r($this->_item_list_rs);
					return TRUE;
				} else {
					return TRUE;
				}
			} else { //if(strlen($pageBuffer)>0)
				return FALSE;
			}
		}
	}

	/*
	    Will return an array of the following structure.
	        array(
	            "title"=>title,
	            "miker4r1"=>string describing best version,
	            "plot"=>blurb,
	            "imdb_id"=>IMDb ID,
	            "age_rating"=>age rating,
	            "actors"=>actors,
	            "category"=>categories space separated,
	            "year"=>year,
	            "director"=>director/s,
	            "run_time"=>runtime,
	            "dvd_region"=>Regions space separated,
	            "ratio"=>ratio,
	            "audio_lang"=>audio languages space separated,
	            "subtitles"=>subtitles space separated,
	            "dvd_extras"=>paragraph of DVD goodies,
	            "anamorphic"=>bool,
	            "imageurl"=>imageurl
	        );
	
	    This is designed to tolerate changes in MichaelD's format.  They bracket
	    their headings in <B></BR> at the moment, but may change it to <Hx> or
	    styled <DIV>s.  Similarly their <TD>s don't have attributes, but that will
	    change when someone objects to MichaelD's plain style.
	
	    So these patterns are fairly loose.  They suck up lots of HTML together
	    with the data we're after, and I rely on strip_tags to prune it down.
	 */
	function queryItem($search_attributes_r, $s_item_type) {
		$page = $this->fetchURI('http://www.michaeldvd.com.au/Discs/Disc.asp?ID=' . $search_attributes_r['michaeldid']);
		if ($page) {
			// First translate things like &nbsp; into "real" characters.  This
			// achieves the reverse of htmlentities():
			$page = strtr($page, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));

			// But &nbsp; is translated to a hard space, which trim() doesn't trim.
			// Work around that:
			$page = strtr($page, chr(160), ' ');

			// The image may not even exist!
			if (preg_match("#/CoverArt/" . $search_attributes_r['michaeldid'] . ".jpg#i", $page)) {
				$this->addItemAttribute('imageurl', 'http://www.michaeldvd.com.au/CoverArt/' . $search_attributes_r['michaeldid'] . '.jpg');
			} else if (preg_match("#/CoverArtUnverified/" . $search_attributes_r['michaeldid'] . ".jpg#i", $page)) {
				$this->addItemAttribute('imageurl', 'http://www.michaeldvd.com.au/CoverArtUnverified/' . $search_attributes_r['michaeldid'] . '.jpg');
			}

			if (preg_match('#<title.*?>(.*?)</title#is', $page, $matches)) {
				$title = strip_tags($matches[1]);

				if (preg_match("/(.*)\(([0-9]+)\)/", $title, $matches)) {
					$title = $matches[1];
					$this->addItemAttribute('year', $matches[2]);
				}

				if (($idx = strpos($title, "(Blu-ray)")) !== FALSE) {
					$title = substr($title, 0, $idx);
				}
				$this->addItemAttribute('title', $title);
			}

			if (preg_match('#best version.*?best version.*?<td.*?>(.+?)</td#is', $page, $matches)) {
				$p = trim(strip_tags(str_replace('<br>', "\n", $matches[1])));

				// No point in putting "undetermined" in the database.  Leave it
				// blank so that a refresh can correct it later.  If there is no difference,
				// do not provide option, as its annoying.
				if (strcasecmp($p, 'undetermined') !== 0 && strcasecmp($p, 'Same') !== 0) {
					$this->addItemAttribute('miker4r1', $p);
				}
			}

			if (preg_match('#<!.*?blurb.*?>(.*?)<!#is', $page, $matches)) {
				$p = trim(strip_tags($matches[1]));

				// If a blurb defined.
				if (strcasecmp($p, 'no blurb yet') !== 0) {
					$this->addItemAttribute('blurb', $p);
				}
			}

			if (preg_match('#http://www\.imdb\.com/title/tt([0-9]+)/#i', $page, $matches)) {
				$this->addItemAttribute('imdb_id', $matches[1]);
			}

			if (preg_match('#/Graphics/Ratings/(.+?).gif"#i', $page, $matches)) {
				$this->addItemAttribute('age_rating', strip_tags($matches[1]));
			}

			if (preg_match('#Starring/dir/music.*?</tr.*?<td.*?>(.*?)</td#is', $page, $matches)) {
				$block = strip_tags(str_replace('<br>', ',', $matches[1]));
				$this->addItemAttribute('actors', explode(",", $block));
			}

			if (preg_match('#Director\(s\).*?</tr.*?<td.*?>(.*?)</td#is', $page, $matches)) {
				$block = strip_tags(str_replace('<br>', ',', $matches[1]));
				$this->addItemAttribute('director', explode(",", $block));
			}

			if (preg_match('#<!--\s*genre.*?<td[^>]*>(.*?)</td#is', $page, $matches)) {
				$this->addItemAttribute('genre', strtolower(trim(strip_tags($matches[1]))));
			}

			if (preg_match('#Movie release year.*?<td.*?>([0-9]*)</td#is', $page, $matches)) {
				$this->addItemAttribute('year', strip_tags($matches[1]));
			}

			if (preg_match('#<!--\s*Running time.*?<td.*?>\s*(\d*).*?</td#is', $page, $matches)) {
				// OpenDb can only handle four-digit running times; MichaelD has minutes and seconds as "xxx:xx"
				$this->addItemAttribute('run_time', strip_tags($matches[1]));
			}

			if (preg_match('#<!--\s*Region coding.*?<td.*?>(.*?)</td#is', $page, $matches)) {
				$this->addItemAttribute('dvd_region', explode(' ', strip_tags($matches[1])));
			}

			if (preg_match('#<!--[\s]*widescreen/full.*?<td.*?>(.*?)</td#is', $page, $matches)) {
				if (preg_match('#full\s*frame#i', $matches[1]))
					$this->addItemAttribute('ratio', '1.33');
				else if (preg_match('#(\d+(?:\.\d+)):1#', $matches[1], $submatches))
					$this->addItemAttribute('ratio', $submatches[1]);

				if (stristr($matches[1], '16x9 enhanced')) {
					$this->addItemAttribute('anamorphic', 'Y');
				}
			}

			if (preg_match('#<!--\s*audio parameters.*?<tr.*?audio.*?<tr.*?>(.*?)</tr#is', $page, $matches)) {
				$subresource = fetch_attribute_type_lookup_rs('AUDIO_LANG', NULL);
				while ($sub = db_fetch_assoc($subresource)) {
					if (preg_match('#' . $sub['value'] . '#i', $matches[1]))
						$this->addItemAttribute('audio_lang', $sub['value']);
				}

				if (preg_match('#english[^,]dolby digital 5\.1#i', $matches[1]))
					$this->addItemAttribute('audio_lang', 'ENGLISH_5.1');
				if (preg_match('#english dts#i', $matches[1]))
					$this->addItemAttribute('audio_lang', 'ENGLISH_DTS');
				if (preg_match('#commentary#i', $matches[1]))
					$this->addItemAttribute('audio_xtra', 'DIR_COMMENT');
			}

			if (preg_match('#<!--\s*audio parameters.*?</tr.*?subtitles.*?</tr>.*?<tr.*?>(.*?)</tr#is', $page, $matches)) {
				$subtitles = strip_tags($matches[1]);
				$subresource = fetch_attribute_type_lookup_rs('SUBTITLES', NULL);
				while ($sub = db_fetch_assoc($subresource)) {
					if (preg_match('#' . $sub['value'] . '#i', $subtitles))
						$this->addItemAttribute('subtitles', $sub['value']);
				}
			}

			if (preg_match('#<!--\s*extras.*?</tr.*?<td.*?>(.*?)</td#is', $page, $matches)) {
				$this->addItemAttribute('dvd_extras', strip_tags(preg_replace('#\s*,\s*#', "\n", $matches[1])));
			}

			// MichaelD puts the disc's year in parenthesis after the
			// title.  OpenDb shifts articles to the end of the title,
			// after a comma, then optionally appends the year in item
			// listings.  So "The Animatrix" becomes
			// "Animatrix (2003), The (2003)".
			// "Brazil" becomes "Brazil (1985) (1985)".
			// "Blues Brothers 2000 (2000) (2000)".  See the problem?
			//
			// This block removes the year from the disc's title, provided
			// it is at the end and parenthesised.  "Blues Brothers 2000"
			// should remain intact.  ARD.
			if (strlen($this->getItemAttribute('title')) > 0 && is_numeric($this->getItemAttribute('year')) && preg_match('/\(' . $this->getItemAttribute('year') . '\)$/', $this->getItemAttribute('title'))) {
				$this->replaceItemAttribute('title', preg_replace('/\s*\(' . $this->getItemAttribute('year') . '\)$/', '', $this->getItemAttribute('title')));
			}

			// Attempt to include data from IMDB if available
			if (is_numeric($this->getItemAttribute('imdb_id'))) {
				$sitePlugin = &get_site_plugin_instance('imdb');
				if ($sitePlugin !== FALSE) {
					if ($sitePlugin->queryItem(array('imdb_id' => $this->getItemAttribute('imdb_id')), $s_item_type)) {
						// no mapping process is performed here, as no $s_item_type was provided.
						$itemData = $sitePlugin->getItemData();
						if (is_array($itemData)) {
							// merge data in here.
							foreach ($itemData as $key => $value) {
								if ($key == 'actors')
									$this->replaceItemAttribute('actors', $value);
								else if ($key == 'director')
									$this->replaceItemAttribute('director', $value);
								else if ($key == 'year')
									$this->replaceItemAttribute('year', $value);
								else if ($key == 'actors')
									$this->replaceItemAttribute('actors', $value);
								else if ($key == 'plot') //have to map from imdb to michaeld attribute type.
									$this->addItemAttribute('blurb', $value);
								else if ($key != 'age_rating' && $key != 'run_time')
									$this->addItemAttribute($key, $value);
							}
						}
					}
				}
			}

			return TRUE;
		} else { //if ($page)
			return FALSE;
		}
	}
}
?>
