<?php
/* 	
 	Open Media Collectors Database
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

include_once("./lib/zip.lib.php");
include_once("./functions/review.php");
include_once("./functions/filecache.php");
include_once("./functions/item_attribute.php");
include_once("./functions/datetime.php");
include_once("./functions/site_plugin.php");

class XMMMovieDatabasePlugin {
	var $purchasedateformatmask;
	var $attribute_rs;
	var $zipfile;
	var $buffer;
	var $isZip;
	var $imdbUrl;
	var $updated;
	
	/**
	 * @param $isZip Allows disabling of ZIP function mostly just for testing.
	 */
	function XMMMovieDatabasePlugin($isZip = TRUE) {
		$this->isZip = $isZip;
		
		if($this->isZip) {
			$this->zipfile = new zipfile();
		}
		
		// TODO - support other site plugins to provide DVD image
		$site_plugin_r = fetch_site_plugin_r('imdb');
		if($site_plugin_r!==FALSE) {
			$this->imdbUrl = $site_plugin_r['more_info_url'];
		} else {
			$this->imdbUrl = NULL;
		}
	}
	
	function get_file_content_type() {
		if($this->isZip) {
			return 'application/zip';
		} else {
			return 'text/xml';
		}
	}

	function get_file_name() {
		if($this->isZip) {
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
		$this->buffer = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".
				"\n<XMM_Movie_Database>";
		
		return NULL;
	}

	function &file_footer() {
		$this->buffer .= "\n</XMM_Movie_Database>\n";

		if($this->isZip) {
			$this->zipfile->addFile($this->buffer, 'export.xml');
		
			unset($this->buffer);
			$zipFile =& $this->zipfile->file();
			unset($this->zipfile);
			return $zipFile;
		} else {
			return $this->buffer;
		}
	}

	var $item_type_map = array('DVD'=>'DVD-Rom', 
								'BD'=>'Blu-Ray', 
								'VHS'=>'VHS',
								'SVCD'=>'Digital Media',
								'VCD'=>'Digital Media',
								'DIVX'=>'Digital Media',
								'LD'=>'Digital Media');
	
	function start_item($item_id, $s_item_type, $title) {
		$this->attribute_rs = array();
		
		$this->buffer .= "\n\t<Movie>";
		$this->buffer .= "\n\t\t<MovieID>$item_id</MovieID>";
		$this->buffer .= "\n\t\t<Title>".$this->encode($title)."</Title>";
		
		$review = fetch_review_rating($item_id);
		if($review!=FALSE) {
			$this->buffer .= "\n\t\t<PersonalRating>$review</PersonalRating>";
		}
		
		if(isset($this->item_type_map[$s_item_type])) {
			$mediaType = $this->item_type_map[$s_item_type];
		} else {
			$mediaType = 'DVD-Rom';
		}
		
		$this->buffer .= "\n\t\t<Media>$mediaType</Media>";

		// TODO - what do we put here???
		//$this->buffer .= "\n\t\t<Position>Default</Position>";
		
		return NULL;
	}

	function end_item() {
		// fall back to last time instance was updated, its approximate but better 
		// than nothing.
		if(!isset($this->attribute_rs['Purchase']) && isset($this->updated)) {
			$this->attribute_rs['Purchase'] = get_localised_timestamp('YYYY-MM-DD', $this->updated);
		}
		
		// now do the attributes
		reset($this->attribute_rs);
		while(list($type,$value) = each($this->attribute_rs)) {
			if($type == 'Cover') {
				//while(list(,$url) = each($value)) { 
				$file = $this->get_cached_image($value);
				
				if($file!=FALSE) {
					$filename = basename($file);
					
					if($this->isZip) {
						$this->zipfile->addFile(file_get_contents($file), $filename);
					}
				
					$this->buffer .= "\n\t\t<Cover>".$filename."</Cover>";
				}
				//}
			} else if($type == 'Genre') {
				$this->buffer .= "\n\t\t<Genre>".implode(",", $value)."</Genre>";
			} else if($type == 'Actor') {
				$this->buffer .= "\n\t\t<Actors>";
				while(list(,$actor) = each($value)) {
					$this->buffer .= "\n\t\t\t<Actor>".$this->encode($actor)."</Actor>";
				}
				$this->buffer .= "\n\t\t</Actors>";
			} else if(strlen($value)>0) {
				$this->buffer .= "\n\t\t<$type>".$this->encode($value)."</$type>";
			}
		}
		$this->buffer .= "\n\t</Movie>";
		
		return NULL;
	}

	function get_cached_image($url) {
		$file_cache_r = fetch_url_file_cache_r($url, 'ITEM', INCLUDE_EXPIRED);
		
		if($file_cache_r!=FALSE) {
			//$imagefile = file_cache_get_cache_file_thumbnail($file_cache_r);
			$imagefile = file_cache_get_cache_file($file_cache_r);
			if($imagefile!=FALSE) {
				return $imagefile;		
			}
		} 
		//else
		return NULL;
	}
	
	function start_item_instance($instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment, $update_on) {
		// if purchase date is not provided fall back to last time the instance was updated.
		$this->updated = $update_on;
		
		return NULL;
	}
	
	function end_item_instance() {
		// return nothing yet as we will wrap it all up in end_item
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
	
	var $attribute_map = array(
			'YEAR'=>'Year', 
			'RUN_TIME'=>'Length',
	 		'MOVIE_PLOT'=>'Plot',
			'PUR_DATE'=>'Purchase',
			'UPC_ID'=>'UPC',
			'DIRECTOR'=>'Director',
			'AGE_RATING'=>'Rating',
		);
	
	function item_attribute($s_attribute_type, $order_no, $attribute_val) {
		if($s_attribute_type == 'IMDB_ID') {
			if($this->imdbUrl!=NULL) {
				$this->attribute_rs['URL'] = str_replace('{imdb_id}', $attribute_val, $this->imdbUrl);
			}
		} else if($s_attribute_type == 'MOVIEGENRE') {
			$this->attribute_rs['Genre'][] = $attribute_val;
		} else if($s_attribute_type == 'ACTORS') {
			$this->attribute_rs['Actor'][] = $attribute_val;
		} else if($s_attribute_type == 'IMAGEURL') { //  || $s_attribute_type == 'IMAGEURLB'
			$this->attribute_rs['Cover'] = $attribute_val;
		} else if($s_attribute_type == 'PUR_DATE') {
			$timestamp = get_timestamp_for_datetime($attribute_val, 'YYYYMMDDHH24MISS');
			$this->attribute_rs['Purchase'] = get_localised_timestamp('YYYY-MM-DD', $timestamp);
		} else if($s_attribute_type == 'DVD_REGION') { // this is a giant hack!
			switch($attribute_val) {
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
			if(isset($this->attribute_map[$s_attribute_type])) {
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
