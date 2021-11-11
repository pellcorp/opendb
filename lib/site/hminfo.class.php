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
    0.81p4		Initial 0.81 compliant release.
    0.81p6		
    0.81p7		Fix to support table prefixing on site_hminfo table.
    0.81p8      Minor fix to include UPC_ID attribute into final results
 */
include_once("./lib/SitePlugin.class.php");

include_once("./lib/datetime.php");

function fetch_hometheaterinfo_r($hmi_id) {
	$result = fetch_hometheaterinfo_rs(NULL, NULL, $hmi_id);
	if ($result && db_num_rows($result) > 0) {
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}
}

function fetch_hometheaterinfo_cnt($title, $upc_id = NULL, $hmi_id = NULL) {
	// Invalid UPC - '000000000000'
	if ((strlen($upc_id) > 0 && $upc_id != "000000000000") || strlen($title) > 0 || strlen($hmi_id) > 0) {
		$query = "SELECT	COUNT('x') as count " . "FROM	site_hminfo " . "WHERE	";

		if (strlen($upc_id) > 0) {
			$query .= " upc = '" . $upc_id . "'";
		} else if (strlen($title) > 0) {
			if (strpos($title, '%') !== FALSE)
				$query .= " dvd_title LIKE '" . addslashes($title) . "'";
			else
				$query .= " dvd_title = '" . addslashes($title) . "'";
		} else if (strlen($hmi_id) > 0) {
			$query .= " id = " . $hmi_id . "";
		}

		$result = db_query($query);
		if ($result && db_num_rows($result) > 0) {
			$found = db_fetch_assoc($result);
			db_free_result($result);
			if ($found !== FALSE)
				return $found['count'];
		}
	}

	// else -- incorrect search terms specified
	return FALSE;
}

/*
 * Will only use one of $upc_id, $title or $hmi_id
 */
function fetch_hometheaterinfo_rs($title, $upc_id = NULL, $hmi_id = NULL, $start_index = NULL, $items_per_page = NULL) {
	$record = NULL;

	// Invalid UPC - '000000000000'
	if ((strlen($upc_id) > 0 && $upc_id != "000000000000") || strlen($title) > 0 || strlen($hmi_id) > 0) {
		$query = "SELECT	id as hmi_id," . "dvd_title as title, " . "studio, " . "released as orig_rel_dt, " . "status as rel_status, " . "sound as audio_lang," . "versions as dvd_format," . "price as listprice," . "rating as age_rating," . "year," . "genre," . "aspect as ratio,"
				. "upc as upc_id," . "dvd_releasedate as dvd_rel_dt " . "FROM	site_hminfo " . "WHERE	";

		if (strlen($upc_id) > 0) {
			$query .= " upc = '" . $upc_id . "'";
		} else if (strlen($title) > 0) {
			if (strpos($title, '%') !== FALSE)
				$query .= " dvd_title LIKE '" . addslashes($title) . "'";
			else
				$query .= " dvd_title = '" . addslashes($title) . "'";
		} else if (strlen($hmi_id) > 0) {
			$query .= " id = " . $hmi_id . "";
		}

		if (is_numeric($start_index) && is_numeric($items_per_page)) {
			$query .= ' LIMIT ' . $start_index . ', ' . $items_per_page;
		}

		$result = db_query($query);
		if ($result && db_num_rows($result) > 0) {
			return $result;
		}
	}

	// else -- incorrect search terms specified
	return FALSE;
}

class hminfo extends SitePlugin {
	function __construct($site_type) {
		parent::__construct($site_type);
	}

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		// standard block of code to cater for refresh option, where item already has
		// reference to site item unique ID.
		if (strlen($search_vars_r['hmi_id']) > 0) {
			$this->addListingRow(NULL, NULL, NULL, array('hmi_id' => $search_vars_r['hmi_id']));
			return TRUE;
		} else {
			$item_count = fetch_hometheaterinfo_cnt('%' . $search_vars_r['title'] . '%', $search_vars_r['upc_id']);
			if ($item_count > 0) {
				$this->setTotalCount($item_count);

				$results = fetch_hometheaterinfo_rs('%' . $search_vars_r['title'] . '%', $search_vars_r['upc_id'], NULL, $offset, $items_per_page);
				if ($results) {
					while ($hometheaterinfo_r = db_fetch_assoc($results)) {
						$this->addListingRow($hometheaterinfo_r['title'], NULL, NULL, array('hmi_id' => $hometheaterinfo_r['hmi_id']));
					}
					db_free_result($results);
				}
			}

			// no results, still means TRUE return, otherwise get Undefined Error
			return TRUE;
		}
	}

	/*
	 * http://www.hometheaterinfo.com/keyto.htm
	 * 
	 * Key to 'dvd_format';
	 *	LBX  Letterbox    
	 *	4:3  'Normal' TV    
	 *	16:9  Anamorphic    
	 *	P&S  Pan and Scan    
	 *	VAR  Various    
	 * 	UNK  Unknown
	 * 
	 * Key to 'audio_lang':
	 *	1.0 Mono  Two Channel Mono
	 *	2.0 Stereo Dolby Stereo
	 *	4.0 Four Channel Surround  
	 *	5.0 Five Channel Surround  
	 *	5.1 Dolby 6 Channel  
	 *	DTS Digital Theater Sound DTS Six Channel Surround
	 *	6.1 ES Seven Channel DTS  
	 *	6.1 EX Seven Channel Dolby  
	 *	SUR Prologic Surround Dolby Surround, Stereo Surround
	 *	PCM PCM Audio Stereo
	 *	DUB Dubbed in English  
	 *	SUB Subtitles  
	 *	SIL Silent Film Often contains stereo track for background music
	 *	VAR Various More than one format, usually box sets
	 *	UNK Uknown Not Provided in Specs
	 */
	function queryItem($search_attributes_r, $s_item_type) {
		$hometheaterinfo_r = fetch_hometheaterinfo_r($search_attributes_r['hmi_id']);
		if ($hometheaterinfo_r !== FALSE) {
			// Format slash correctly.
			$hometheaterinfo_r['title'] = str_replace("/ ", " / ", $hometheaterinfo_r['title']);

			// Lets get anything in brackets out into DVD_EXTRAS field.
			$indexStart = strpos($hometheaterinfo_r['title'], "(");
			if ($indexStart !== FALSE && $indexStart > 0) { // In case bracket is first character!!!
				$indexEnd = strpos($hometheaterinfo_r['title'], ")", $indexStart);
				if ($indexEnd !== FALSE) {
					$this->addItemAttribute('dvd_extras', substr($hometheaterinfo_r['title'], $indexStart + 1, $indexEnd - ($indexStart + 1)));
					$this->addItemAttribute('title', substr($hometheaterinfo_r['title'], 0, $indexStart));
				} else {
					$this->addItemAttribute('title', $hometheaterinfo_r['title']);
				}
			} else {
				$this->addItemAttribute('title', $hometheaterinfo_r['title']);
			}

			if (strlen($hometheaterinfo_r['studio']) > 0) {
				$this->addItemAttribute('studio', $hometheaterinfo_r['studio']);
			}

			if (strlen($hometheaterinfo_r['rel_status']) > 0) {
				$this->addItemAttribute('rel_status', $hometheaterinfo_r['rel_status']);
			}

			$this->addItemAttribute('dvd_region', '1'); // This is only option!

			// All Region 1 (US) items should be NTSC!
			$this->addItemAttribute('vid_format', 'NTSC');

			//------------------
			// AUDIO_LANG Processing.
			//------------------
			//1.0, 2.0, 4.0, 5.0, 5.1, DTS, 6.1 ES, 6.1 EX, SUR, PCM,DUB, SUB, SIL, VAR
			$this->addItemAttribute('audio_lang', $hometheaterinfo_r['audio_lang']);

			//------------------
			// DVD_FORMAT Processing.
			//------------------
			if (strlen($hometheaterinfo_r['dvd_format']) > 0) {
				$dvd_format_r = trim_explode(',', $hometheaterinfo_r['dvd_format']);
				foreach ($dvd_format_r as $dvd_format) {
					switch ($dvd_format) {
					case 'LBX':
						$this->addItemAttribute('dvd_format', 'LBX');
						break;
					case '4:3':
						$this->addItemAttribute('dvd_format', '4:3');
						break;
					case '16:9':
						$this->addItemAttribute('dvd_format', '16:9');
						$this->addItemAttribute('anamorphic', 'Y');
						break;
					case 'P&S':
						$this->addItemAttribute('dvd_format', 'P&S');
						break;
					//case 'VAR':
					//	break;
					default: // serves as UNK as well.
						break;
					} // switch$record['dvd_format']
				}
			}

			//------------------
			// RATIO Processing.
			//------------------			
			if (strlen($hometheaterinfo_r['ratio']) > 0) {
				if ($hometheaterinfo_r['ratio'] != "VAR") {
					$indexOfColon = strpos($hometheaterinfo_r['ratio'], ":");

					// Remove everything after the colon.
					if ($indexOfColon !== FALSE)
						$this->addItemAttribute('ratio', substr($hometheaterinfo_r['ratio'], 0, $indexOfColon));
				}
			}

			//------------------
			// PRICE Processing.
			//------------------
			if (strlen($hometheaterinfo_r['listprice']) > 0) {
				if (substr($hometheaterinfo_r['listprice'], 0, 1) == "$")
					$this->addItemAttribute('listprice', substr($hometheaterinfo_r['listprice'], 1));
			}

			//------------------
			// YEAR Processing.
			//------------------
			if (is_numeric($hometheaterinfo_r['year'])) {
				$this->addItemAttribute('year', $hometheaterinfo_r['year']);
			}

			//------------------
			// GENRE Processing.
			//------------------
			if (strlen($hometheaterinfo_r['genre']) > 0) {
				if ($hometheaterinfo_r['genre'] != "VAR") {
					// Remove spaces and explode on /
					$genre_r = explode("/", str_replace(" ", "", $hometheaterinfo_r['genre']));
					$this->addItemAttribute('genre', $genre_r);
				}
			}

			//------------------
			// DATE Processing.
			// Note: Ignores any hours/minutes/seconds 
			//------------------
			$date_format_cfg = $this->getConfigValue('datetime_mask');

			// We will change the date format to use later on!
			if (strlen($hometheaterinfo_r['dvd_rel_dt']) > 0) {
				//YYYY-MM-DD
				list($year, $month, $day) = sscanf($hometheaterinfo_r['dvd_rel_dt'], "%d-%d-%d");

				$this->addItemAttribute('dvd_rel_dt', get_localised_timestamp($date_format_cfg, mktime(0, 0, 0, $month, $day, $year)));
			}

			// We will change the date format to use later on!
			if (strlen($hometheaterinfo_r['orig_rel_dt']) > 0) {
				//YYYY-MM-DD
				list($year, $month, $day) = sscanf($hometheaterinfo_r['orig_rel_dt'], "%d-%d-%d");
				$this->addItemAttribute('orig_rel_dt', get_localised_timestamp($date_format_cfg, mktime(0, 0, 0, $month, $day, $year)));
			}

			if (strlen($hometheaterinfo_r['age_rating']) > 0) {
				$this->addItemAttribute('age_rating', $hometheaterinfo_r['age_rating']);
			}

			if (strlen($hometheaterinfo_r['upc_id']) > 0) {
				$this->addItemAttribute('upc_id', $hometheaterinfo_r['upc_id']);
			}

			return TRUE;
		} else { //if($hometheaterinfo_r!==FALSE)
			return FALSE;
		}
	}
}
?>
