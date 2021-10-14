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
include_once("./lib/utils.php");
include_once("./lib/adodb-time.inc.php");

function get_lang_var_months_r($abbrev = FALSE) {
	$suffix = '';
	if ($abbrev)
		$suffix = '_abbrev';
	
	return array (
			get_opendb_lang_var ( 'january' . $suffix ),
			get_opendb_lang_var ( 'february' . $suffix ),
			get_opendb_lang_var ( 'march' . $suffix ),
			get_opendb_lang_var ( 'april' . $suffix ),
			get_opendb_lang_var ( 'may' . $suffix ),
			get_opendb_lang_var ( 'june' . $suffix ),
			get_opendb_lang_var ( 'july' . $suffix ),
			get_opendb_lang_var ( 'august' . $suffix ),
			get_opendb_lang_var ( 'september' . $suffix ),
			get_opendb_lang_var ( 'october' . $suffix ),
			get_opendb_lang_var ( 'november' . $suffix ),
			get_opendb_lang_var ( 'december' . $suffix ) );
}

function get_lang_var_days_r($abbrev = FALSE) {
	$suffix = '';
	if ($abbrev)
		$suffix = '_abbrev';
	
	return array (
			get_opendb_lang_var ( 'sunday' . $suffix ),
			get_opendb_lang_var ( 'monday' . $suffix ),
			get_opendb_lang_var ( 'tuesday' . $suffix ),
			get_opendb_lang_var ( 'wednesday' . $suffix ),
			get_opendb_lang_var ( 'thursday' . $suffix ),
			get_opendb_lang_var ( 'friday' . $suffix ),
			get_opendb_lang_var ( 'saturday' . $suffix ) );
}

/**
 *	A Database independant mask of:
 *		Month - Month name
 * 		Mon - Abbreviated month, Initcap.
 * 		MON - Abreviated month UPPERCASE
 *		Day	- Weekday name
 *		Da	- Abbreviated Weekday name
 *		DDth - Day of the month with English suffix (1st, 2nd, 3rd)
 *		DD - Days (01 - 31)
 *		MM - Months (01 -12)
 *		YYYY - Years
 *		HH24 - Hours (00 - 23)
 *		HH - Hours (01 - 12)
 *		MI - Minutes (00 - 59)
 *		SS - Seconds (00 - 59)
 *		AM - Meridian indicator  (Will be replaced with the meridian value!)
 * 
 *	Accepts a mask and will convert to one that can be used in php date() function
 *
 *	Uses the postgresql formatting options, which in most cases are compatible
 *	with Oracle:
 *		http://www.postgresql.org/idocs/index.php?functions-formatting.html

 * Formats localised datetime.  Replaces Day/Month mask elements with actual values.
 *
 * @param   string   the current timestamp
 *
 * NOTE: Assumes that language has been defined
 */
function get_localised_timestamp($format_mask, $timestamp = NULL) {
	if (strlen ( $timestamp ) > 0) {
		// if illegal timestamp, we have to abort!
		if (! is_numeric ( $timestamp )) {
			return FALSE;
		}
	} else {
		$timestamp = time ();
	}
	
	$php_mask_conversion = array (
			"DDth" => "jS", //Day of the month with English suffix (1st, 2nd, 3rd, etc.)
			"DD" => "d", //Day of the month, numeric (00..31)
			"MM" => "m", //Month, numeric (01..12)
			"YYYY" => "Y", //Year, numeric, 4 digits
			"YY" => "y", //Year, numeric, 2 digits
			"HH24" => "H", //Hour (00..23)
			"HH" => "h", //Hour (01..12)
			"MI" => "i", //Minutes, numeric (00..59)
			"SS" => "s", //Seconds (00..59)
			"AM" => "a", //AM or PM
			"PM" => "a" );	//AM or PM
	
	// Now expand the mask with the test of the elements.
	reset ( $php_mask_conversion );
	foreach ( $php_mask_conversion as $key => $match ) {
		$format_mask = str_replace ( $key, adodb_date ( $match, $timestamp ), $format_mask );
	}
	
	// Replace the 'Mon' with the actual abbreviated 'Month' word for the $timestamp
	$month = ( int ) adodb_date ( 'm', $timestamp ) - 1;
	
	if (strpos ( $format_mask, 'Month' ) !== FALSE) {
		$months_full_r = get_lang_var_months_r ();
		$format_mask = str_replace ( 'Month', $months_full_r [$month], $format_mask );
	}
	
	if (strpos ( $format_mask, 'Mon' ) !== FALSE) {
		$months_abbrev_r = get_lang_var_months_r ( TRUE );
		$format_mask = str_replace ( 'Mon', $months_abbrev_r [$month], $format_mask );
	}
	
	if (strpos ( $format_mask, 'MON' ) !== FALSE) {
		$months_abbrev_r = get_lang_var_months_r ( TRUE );
		$format_mask = str_replace ( 'MON', strtoupper ( $months_abbrev_r [$month] ), $format_mask );
	}
	
	// Replace the 'Day' with the actual 'Day' word for the $timestamp
	if (strpos ( $format_mask, 'Day' ) !== FALSE) {
		$day = ( int ) adodb_date ( 'w', $timestamp );
		
		$days_full_r = get_lang_var_days_r ();
		$format_mask = str_replace ( 'Day', $days_full_r [$day], $format_mask );
	}
	
	if (strpos ( $format_mask, 'Da' ) !== FALSE) {
		$day = ( int ) adodb_date ( 'w', $timestamp );
		
		$days_full_r = get_lang_var_days_r ( TRUE );
		$format_mask = str_replace ( 'Da', $days_full_r [$day], $format_mask );
	}
	
	return $format_mask;
}

$datetime_masks_r = array (
		'DD',
		'MM',
		'YYYY',
		'HH24',
		'HH',
		'MI',
		'SS' );

function tokenize_datetime_or_mask_string($value, $format_tokens = NULL) {
	global $datetime_masks_r;
	
	$token = '';
	$tokens = array ();
	for($i = 0; $i < strlen( $value ); $i ++) {
		switch ($value[$i]) {
			case ' ' :
			case "\t" :
			case ',' :
			case '/' :
			case '\\' :
			case '-' :
			case ':' :
			case '.' :
				if (strlen( $token ) > 0) {
					$tokens[] = $token;
					$token = '';
				}
				$tokens[] = $value[$i];
				break;
			
			default :
				// based on the format token, we may need to cut the token off,
				// at a certain point.
				if (is_array( $format_tokens )) {
					if (! is_numeric( $value[$i] )) {	// all date tokens are numeric
						if (strlen( $token ) > 0) {
							$tokens [] = $token;
							$token = '';
						}
						$tokens [] = $value[$i];
					} else {
						$format_token = $format_tokens[count( $tokens )];
						
						if (array_search( $format_token, $datetime_masks_r ) !== FALSE) {
							if ($format_token == 'YYYY') {
								if (strlen( $token ) >= 4) {
									$tokens [] = $token;
									$token = '';
								}
							} else if (strlen ( $token ) >= 2) { // all other mask variables are two chars long
								$tokens [] = $token;
								$token = '';
							}
							
							$token .= $value[$i];
						}
					}
				} else {
					$token .= $value[$i];
					
					if (array_search( $token, $datetime_masks_r ) !== FALSE) {
						if ($token == 'HH') {
							// if there is actually a HH24 token, we should ignore this HH one.
							if (strlen( $value ) > ($i + 2) && substr ( $value, $i + 1, 2 ) == '24') {
								break; // break to start of switch again.
							}
						}
						
						$tokens [] = $token;
						$token = '';
					}
				}
			//default:
		} //switch
	}
	
	if (strlen ( $token ) > 0) {
		$tokens [] = $token;
	}
	
	return $tokens;
}

function get_timestamp_for_datetime($datetime, $format_mask) {
	$components = get_timestamp_components_for_datetime ( $datetime, $format_mask );
	if ($components !== FALSE) {
		return get_timestamp_for_timestamp_components ( $components );
	} else {
		return FALSE;
	}
}

function get_timestamp_for_timestamp_components($components) {
	if ($components !== FALSE) {
		$timestamp = adodb_mktime ( $components ['hour'], $components ['minute'], $components ['second'], ($components ['month'] > 0 ? $components ['month'] : 1), ($components ['day'] > 0 ? $components ['day'] : 1), $components ['year'] );
		
		if ($timestamp !== FALSE) {
			return $timestamp;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
 * 	Mask components supported are:
 *		DD - Days (01 - 31)
 *		MM - Months (01 -12)
 *		YYYY - Years
 *		HH24 - Hours (00 - 23)
 *		HH - Hours (01 - 12)
 *		MI - Minutes (00 - 59)
 *		SS - Seconds (00 - 59)
 * 
 * Note: It is not recommended, that you specify DD, MM, YYYY
 * without the other two.  For instance if you specify a mask
 * of DD - this will cause some of the date components to be
 * set to their defaults, and you will get a date in 1999
*/
function get_timestamp_components_for_datetime($datetime, $format_mask) {
	global $datetime_masks_r;
	
	if ($datetime !== FALSE && strlen ( $datetime ) > 0) {
		$format_tokens = tokenize_datetime_or_mask_string ( $format_mask );
		$datetime_tokens = tokenize_datetime_or_mask_string ( $datetime, $format_tokens );
		
		// As long as the last token is either a punctuation mark, that is
		// the same in both arrays, or is a legal mask token, and has
		// match in the other array.	
		$format_and_datetime_match = FALSE;
		if (count ( $format_tokens ) == count ( $datetime_tokens )) {
			$format_and_datetime_match = TRUE;
		} else if (count ( $format_tokens ) > count ( $datetime_tokens )) {
			$format_token = $format_tokens [count ( $datetime_tokens ) - 1];
			$datetime_token = $datetime_tokens [count ( $datetime_tokens ) - 1];
			
			if ($format_token == $datetime_token) {
				$format_and_datetime_match = TRUE;
			} else if (array_search ( $format_token, $datetime_masks_r ) !== FALSE) {// else mask token
				$format_and_datetime_match = TRUE;
			}
		}
		
		if ($format_and_datetime_match) {
			$datetime_components = array ();
			for($i = 0; $i < count ( $datetime_masks_r ); $i ++) {
				if (($idx = array_search ( $datetime_masks_r [$i], $format_tokens )) !== FALSE) {
					$datetime_components [$datetime_masks_r [$i]] = $datetime_tokens [$idx];
				}
			}
			
			if (is_not_empty_array ( $datetime_components )) {
				$year = $datetime_components ['YYYY'];
				
				if (strlen ( $year ) > 0 && (! is_numeric ( $year ) || strlen ( $year ) != 4)) {
					return FALSE;
				} else if (array_search ( 'YYYY', $format_tokens ) !== FALSE && strlen ( $year ) == 0) {
					return FALSE;
				}
				
				$month = $datetime_components ['MM'];
				if (strlen ( $month ) > 0) {
					if (! is_numeric ( $month )) {
						return FALSE;
					}
				} else if (array_search ( 'MM', $format_tokens ) !== FALSE && strlen ( $month ) == 0) {
					return FALSE;
				}
				
				$day = $datetime_components ['DD'];
				if (strlen ( $day ) > 0) {
					if (! is_numeric ( $day )) {
						return FALSE;
					} else {
						if ($month == 2) {
							// Check for leap year
							if (strlen ( $year ) && (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0)) { // leap year
								if ($day > 29) {
									return FALSE;
								}
							} else {
								if ($day > 28) {
									return FALSE;
								}
							}
						} else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
							if ($day > 30) {
								return FALSE;
							}
						}
					}
				} else if (array_search ( 'DD', $format_tokens ) !== FALSE && strlen ( $day ) == 0) {
					return FALSE;
				}
				
				$hour24 = $datetime_components ['HH24'];
				$hour = @$datetime_components ['HH'];
				if (strlen ( $hour24 ) > 0) {
					if (! is_numeric ( $hour24 ) || $hour24 < 0 || $hour24 > 23) {
						return FALSE;
					}
					
					// so we only need one for mktime
					$hour = $hour24;
				} else if (strlen ( $hour ) > 0) {
					if (! is_numeric ( $hour ) || $hour < 1 || $hour > 12) {
						return FALSE;
					}
				}
				
				$minute = $datetime_components ['MI'];
				if (strlen ( $minute ) > 0) {
					if (! is_numeric ( $minute ) || $minute < 0 || $minute > 59) {
						return FALSE;
					}
				}
				
				$second = $datetime_components ['SS'];
				if (strlen ( $second ) > 0) {
					if (! is_numeric ( $second ) || $second < 0 || $second > 59) {
						return FALSE;
					}
				}
				
				return array (
						'year' => intval ( $year ),
						'month' => intval ( $month ),
						'day' => intval ( $day ),
						'hour' => intval ( $hour ),
						'minute' => intval ( $minute ),
						'second' => intval ( $second ) );
			} else {
				return FALSE;
			}
		} else {
			//mismatch
			return FALSE;
		}
	} else {	//if($datetime!==FALSE && strlen($datetime)>0)
		return FALSE;
	}
}
?>
