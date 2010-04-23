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

include_once("./functions/review.php");
include_once("./functions/filecache.php");
include_once("./functions/item_attribute.php");
include_once("./functions/datetime.php");

class XMMMovieDatabasePlugin {
	var $purchasedateformatmask;
	var $attribute_rs;
		
	function XMMMovieDatabasePlugin() {
		$attribute_type_r = fetch_attribute_type_r('PUR_DATE');
		if($attribute_type_r!=FALSE) {
			$this->purchasedateformatmask = $attribute_type_r['display_type_arg1'];
		}
		
		if(strlen($this->purchasedateformatmask)==0) {
			$this->purchasedateformatmask = 'DD/MM/YYYY'; // default
		}
	}
	
	/*
	* The content type, when saved as file.
	*/
	function get_file_content_type() {
		return 'text/xml';
	}

	/*
	* The filename, when saved as file.
	*/
	function get_file_name() {
		return 'Movies.xml';
	}
	
	function get_display_name() {
		return 'XMM Movie Database XML';
	}
	
	function get_plugin_type() {
		return 'item';
	}
	
	function file_header($title) {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".
				"\n<XMM_Movie_Database>";
	}

	function file_footer() {
		return "\n</XMM_Movie_Database>\n";
	}

/*
Media Types: 	"Blu-Ray",
		"Digital Media", 
		"DVD", 
		"HD-DVD", 
		"VHS"
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
	
	var $item_type_map = array('DVD'=>'DVD-Rom', 
								'BD'=>'Blu-Ray', 
								'VHS'=>'VHS',
								'SVCD'=>'Digital Media',
								'VCD'=>'Digital Media',
								'DIVX'=>'Digital Media',
								'LD'=>'Digital Media');
	
	function start_item($item_id, $s_item_type, $title) {
		$this->attribute_rs = array();
		
		$buffer = "\n\t<Movie>";
		$buffer .= "\n\t\t<MovieID>$item_id</MovieID>";
		$buffer .= "\n\t\t<Title>$title</Title>";
		
		$review = fetch_review_rating($item_id);
		if($review!=FALSE) {
			$buffer .= "\n\t\t<PersonalRating>$review</PersonalRating>";
		}
		
		if(isset($this->item_type_map[$s_item_type])) {
				$mediaType = $this->item_type_map[$s_item_type];
		} else {
			$mediaType = 'DVD-Rom'; // default type in this is DVD-ROM, not DVD!!!
		}
		
		$buffer .= "\n\t<Media>$mediaType</Media>";
			
		return $buffer;
	}

	function end_item() {
		$buffer = '';	
	
		// now do the attributes
		reset($this->attribute_rs);
		while(list($type,$value) = each($this->attribute_rs)) {
			if($type == 'Cover') {
				//while(list(,$url) = each($value)) {
				$filename = $this->get_cache_thumbnail_file($value);
				// TODO - need to copy to the export directory
				if($filename!=FALSE) {
					$buffer .= "\n\t\t<Cover>".basename($filename)."</Cover>";
				}
				//}
			} else if($type == 'Genre') {
				$buffer .= "\n\t\t<Genre>".implode(",", $value)."</Genre>";
			} else if($type == 'Actor') {
				$buffer .= "\n\t\t<Actors>";
				while(list(,$actor) = each($value)) {
					$buffer .= "\n\t\t\t<Actor>$actor</Actor>";
				}
				$buffer .= "\n\t\t</Actors>";
			} else {
				$buffer .= "\n\t\t<$type>$value</$type>";
			}
		}
		$buffer .= "\n\t</Movie>";
		
		return $buffer;
	}

	function get_cache_thumbnail_file($url) {
		$file_cache_r = fetch_url_file_cache_r($url, 'ITEM', INCLUDE_EXPIRED);
		
		if($file_cache_r!=FALSE) {
			$thumbnailfile = file_cache_get_cache_file_thumbnail($file_cache_r);
			if($thumbnailfile!=FALSE) {
				return $thumbnailfile;		
			}
		} 
		//else
		return NULL;
	}
	
	function start_item_instance($instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment) {
		// return nothing yet as we will wrap it all up in end_item
		return '';
	}
	
	function end_item_instance() {
		// return nothing yet as we will wrap it all up in end_item
		return '';
	}

	/**
	 	Genre [?????] MOVIEGENRE
		Year [YYYY] YEAR
		Length [in minutes] RUN_TIME
		Plot [free text] MOVIE_PLOT
		Cover [file location minus path information] COVER 
		PersonalRating [decimal (1 decimal place) rating] 
		URL [URL] // todo - get from site plugin
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
		if($s_attribute_type == 'MOVIEGENRE') {
			$this->attribute_rs['Genre'][] = $attribute_val;
		} else if($s_attribute_type == 'ACTOR') {
			$this->attribute_rs['Actor'][] = $attribute_val;
		} else if($s_attribute_type == 'IMAGEURL') { //  || $s_attribute_type == 'IMAGEURLB'
			$this->attribute_rs['Cover'] = $attribute_val;
		} else if($s_attribute_type == 'PUR_DATE') {
			$timestamp = get_timestamp_for_datetime($attribute_val, 'YYYYMMDDHH24MISS');
			$this->attribute_rs['Purchase'] = get_localised_timestamp($this->purchasedateformatmask, $timestamp);
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
		return '';
	}
}
?>
