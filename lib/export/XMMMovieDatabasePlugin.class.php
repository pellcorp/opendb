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

include_once("./lib/zip.lib.php");
include_once("./lib/review.php");
include_once("./lib/filecache.php");
include_once("./lib/item_attribute.php");
include_once("./lib/datetime.php");
include_once("./lib/site_plugin.php");


if (extension_loaded('mysqli')) {
	include_once('./lib/database/mysqli.inc.php');
} else if (extension_loaded('mysql')) {
	include_once('./lib/database/mysql.inc.php');
}

class XMMMovieDatabasePlugin {
	var $purchasedateformatmask;
	var $attribute_rs;
	var $zipfile;
	var $buffer;
	var $itemBuffer;
	var $isZip;
	var $imdbUrl;
	var $updated;
	var $related;
	var $includeParent;

	/**
	 * @param $isZip Allows disabling of ZIP function mostly just for testing.
	 * @param $includeParent Include parent items that have related items.
	 */
	function __construct($isZip = TRUE, $includeParent = FALSE) {
		$this->isZip = $isZip;
		$this->includeParent = $includeParent;

		if ($this->isZip) {
			$this->zipfile = new zipfile();
		}

		// TODO - support other site plugins to provide DVD image
		$site_plugin_r = fetch_site_plugin_r('imdb');
		if ($site_plugin_r !== FALSE) {
			$this->imdbUrl = $site_plugin_r['more_info_url'];
		} else {
			$this->imdbUrl = NULL;
		}
	}

	function get_file_content_type() {
		if ($this->isZip) {
			return 'application/zip';
		} else {
			return 'text/xml';
		}
	}

	function get_file_name() {
		if ($this->isZip) {
			return 'export.zip';
		} else {
			return "export.xml";
		}
	}

	function get_display_name() {
		return 'XMM Movie Database';
	}

	function get_plugin_type() {
		return 'item';
	}

	function file_header($title) {
		$this->buffer = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" . "\n<XMM_Movie_Database>";

		return NULL;
	}

	function &file_footer() {
		$this->buffer .= "\n</XMM_Movie_Database>\n";

		if ($this->isZip) {
			$this->zipfile->addFile($this->buffer, 'export.xml');

			unset($this->buffer);
			$zipFile = &$this->zipfile->file();
			unset($this->zipfile);
			return $zipFile;
		} else {
			return $this->buffer;
		}
	}

	var $item_type_map = array('DVD' => 'DVD', 'BD' => 'Blu-Ray', 'VHS' => 'VHS', 'SVCD' => 'Digital Media', 'VCD' => 'Digital Media', 'DIVX' => 'Digital Media', 'LD' => 'Digital Media');

	function start_item($item_id, $s_item_type, $title) {
		$this->attribute_rs = array();

		$this->itemBuffer = "\n\t<Item>";
		$this->itemBuffer .= "\n\t\t<MovieID>$item_id</MovieID>";
		$this->itemBuffer .= "\n\t\t<Title>" . $this->encode($title) . "</Title>";

		$review = fetch_review_rating($item_id);
		if ($review != FALSE) {
			$this->itemBuffer .= "\n\t\t<PersonalRating>$review</PersonalRating>";
			//			$this->itemBuffer .= "\n\t\t<Rating>$review</Rating>";
		}

		if (isset($this->item_type_map[$s_item_type])) {
			$mediaType = $this->item_type_map[$s_item_type];
		} else {
			$mediaType = 'DVD';// XMM default
		}

		$this->itemBuffer .= "\n\t\t<Media>\n\t\t\t<Medium>$mediaType</Medium>\n\t\t</Media>";

		$this->itemBuffer .= "\n\t\t<Location>Default</Location>";

		return NULL;
	}

	function end_item() {
		// fall back to last time instance was updated, its approximate but better than nothing.
		if (!isset($this->attribute_rs['PurchaseDate']) && isset($this->updated)) {
			$this->attribute_rs['PurchaseDate'] = get_localised_timestamp('YYYYMMDDT00:00:00', $this->updated);
		}

		//$actorsFound = FALSE;

		reset($this->attribute_rs);
		foreach ($this->attribute_rs as $type => $value) {
			if ($type == 'Cover') {
				$file = $this->get_cached_image($value);

				if ($file != FALSE) {
					$filename = basename($file);

					if ($this->isZip) {
						$this->zipfile->addFile(file_get_contents($file), $filename);
					}

					$this->itemBuffer .= "\n\t\t<Cover>" . $filename . "</Cover>";
				}
			} else if ($type == 'Genre') {
				$this->print_multi_attribute('Genres', 'Genre', $value);
			} else if ($type == 'Actor') {
				$this->print_multi_attribute('Actors', 'Actor', $value);
			} else if ($type == 'Director') {
				$this->print_multi_attribute('Directors', 'Director', $value);
			} else if (strlen($value) > 0) {
				$this->itemBuffer .= "\n\t\t<$type>" . $this->encode($value) . "</$type>";
			}
		}

		$this->itemBuffer .= "\n\t</Item>";

		if ($this->includeParent || !$this->related) {
			$this->buffer .= $this->itemBuffer;
		}

		$this->itemBuffer = NULL;
		return NULL;
	}

	function get_cached_image($url) {
		$file_cache_r = fetch_url_file_cache_r($url, 'ITEM', INCLUDE_EXPIRED);

		if ($file_cache_r != FALSE) {
			//$imagefile = file_cache_get_cache_file_thumbnail($file_cache_r);
			$imagefile = file_cache_get_cache_file($file_cache_r);
			if ($imagefile != FALSE) {
				return $imagefile;
			}
		}
		//else
		return NULL;
	}

	function print_multi_attribute($plural, $singular, $values) {
		$this->itemBuffer .= "\n\t\t<$plural>";
		foreach ($values as $value) {
			$this->itemBuffer .= "\n\t\t\t<$singular>" . $this->encode($value) . "</$singular>";
		}
		$this->itemBuffer .= "\n\t\t</$plural>";
	}

	function start_item_instance($item_id, $instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment, $update_on) {
		// if purchase date is not provided fall back to last time the instance was updated.
		$this->updated = $update_on;
		$this->related = is_exists_item_instance_relationship($item_id, $instance_no);
		return NULL;
	}

	function end_item_instance() {
		return NULL;
	}

	/**
	 * 	MovieID
	 * 	Title
	 * 	Media
	    Genre [?????] MOVIEGENRE
	    Year [YYYY] YEAR
	    Length [in minutes] RUN_TIME
	    Plot [free text] MOVIE_PLOT
	    Cover [file location minus path information] COVER 
	    PersonalRating [decimal (1 decimal place) rating] 
	    URL [URL] 
	    Purchase [YYYY-MM-DD] PUR_DATE
	    Actors/Actor [complex structure / free text] ACTOR
	    Director [free text] DIRECTOR
	    Position [free text]
	    Country COUNTRY
	    UPC UPC_ID
	    Rating [MPAA Rating] AGE_RATING
	 */

	var $attribute_map = array('YEAR' => 'Year', 'RUN_TIME' => 'Length', 'MOVIE_PLOT' => 'Plot', 'PUR_DATE' => 'PurchaseDate', 'UPC_ID' => 'UPC', 'AGE_RATING' => 'MPAA',);

	function item_attribute($s_attribute_type, $order_no, $attribute_val) {
		if ($s_attribute_type == 'IMDB_ID') {
			if ($this->imdbUrl != NULL) {
				$this->attribute_rs['URL'] = str_replace('{imdb_id}', $attribute_val, $this->imdbUrl);
			}
		} else if ($s_attribute_type == 'MOVIEGENRE') {
			$this->attribute_rs['Genre'][] = $attribute_val;
		} else if ($s_attribute_type == 'ACTORS') {
			$this->attribute_rs['Actor'][] = $attribute_val;
		} else if ($s_attribute_type == 'DIRECTOR') {
			$this->attribute_rs['Director'][] = $attribute_val;
		} else if ($s_attribute_type == 'IMAGEURL') { //  || $s_attribute_type == 'IMAGEURLB'
			$this->attribute_rs['Cover'] = $attribute_val;
		} else if ($s_attribute_type == 'PUR_DATE') {
			$timestamp = get_timestamp_for_datetime($attribute_val, 'YYYYMMDDHH24MISS');
			$this->attribute_rs['PurchaseDate'] = get_localised_timestamp('YYYYMMDDTT00:00:00', $timestamp);
		} else if ($s_attribute_type == 'DVD_REGION') { // this is a giant hack!
			switch ($attribute_val) {
			case '2':
				$this->attribute_rs['Country'] = 'United Kingdom';
				break;
			case '4':
				$this->attribute_rs['Country'] = 'Australia';
				break;
			case '1':
			default:
				$this->attribute_rs['Country'] = 'United States';
			}
		} else {
			if (isset($this->attribute_map[$s_attribute_type])) {
				$key = $this->attribute_map[$s_attribute_type];

				$this->attribute_rs[$key] = $attribute_val;
			}
		}

		// return nothing yet as we will wrap it all up in end_item
		return NULL;
	}

	function encode($str) {
		$str = htmlspecialchars($str);
		return utf8_encode($str);
	}
}

/*
Genres: 	"Action",
        "Adventure",
        "Animation",
        "Biography",
        "Comedy",
        "Crime", @"Documentary", @"Drama",
    @"Family", @"Fantasy", @"Film-Noir", @"Game-Show",
    @"History", @"Horror", @"Music", @"Musical",
    @"Mystery", @"News", @"Reality-TV", @"Romance",
    @"Sci-Fi", @"Sport", @"Talk-Show", @"Thriller",
                          @"War", @"Western"
 */

?>
