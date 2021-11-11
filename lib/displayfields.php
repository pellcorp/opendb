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
include_once("./lib/item_attribute.php");
include_once("./lib/item_type.php");
include_once("./lib/item.php");
include_once("./lib/http.php");
include_once("./lib/fileutils.php");
include_once("./lib/utils.php");
include_once("./lib/parseutils.php");
include_once("./lib/datetime.php");
include_once("./lib/email.php");
include_once("./lib/status_type.php");
include_once("./lib/theme.php");
include_once("./lib/file_type.php");
include_once("./lib/TitleMask.class.php");
include_once("./lib/widgets.php");

/**
 * stub
 */
function get_display_field($s_attribute_type, $prompt, $display_type, $value, $dowrap = TRUE, $prompt_mask = NULL) {
	$display_type_def = prc_function_spec ( $display_type );
	
	return get_item_display_field ( NULL, array (
			's_attribute_type' => $s_attribute_type,
			'prompt' => $prompt,
			'display_type' => $display_type_def ['type'],
			'display_type_arg1' => $display_type_def ['args'] [0] ?? '',
			'display_type_arg2' => $display_type_def ['args'] [1] ?? '',
			'display_type_arg3' => $display_type_def ['args'] [2] ?? '',
			'display_type_arg4' => $display_type_def ['args'] [3] ?? '',
			'display_type_arg5' => $display_type_def ['args'] [4] ?? '' ), $value, $dowrap, $prompt_mask );
}

function get_item_display_field($item_r, $item_attribute_type_r, $value = NULL, $dowrap = TRUE, $prompt_mask = NULL) {
	if ($item_attribute_type_r ['display_type'] == 'hidden') {
		return '';
	} else if ($item_attribute_type_r ['display_type'] == 'fileviewer') {
		$format_mask = ifempty ( $item_attribute_type_r ['display_type_arg1'], '%value%' );
		$width = ifempty ( $item_attribute_type_r ['display_type_arg2'], '400' );
		$height = ifempty ( $item_attribute_type_r ['display_type_arg3'], '300' );
		$target = ifempty ( $item_attribute_type_r ['display_type_arg4'], '_blank' );
		
		if (is_array ( $value ))
			$values = $value;
		else
			$values [] = $value;
		
		if (count ( $values ) > 0) {
			$display_value_r = array ();
			foreach ( $values as $value ) {
				$value = trim ( $value );
				$value_format_mask = $format_mask;
				
				if (strpos ( $value_format_mask, '%img%' ) !== FALSE) {
					$file_type_r = fetch_file_type_r ( fetch_file_type_for_extension ( get_file_ext ( $value ) ) );
					
					if (strlen ( $file_type_r ['image'] ) > 0 && ($image_src = theme_image_src ( $file_type_r ['image'] )) !== FALSE)
						$img = '<img src="' . $image_src . '" title="' . $value . '">';
					else
						$img = '';
					
					$value_format_mask = str_replace ( '%img%', $img, $value_format_mask );
				}
				
				if (strpos ( $value_format_mask, '%value%' ) !== FALSE) {
					$value_format_mask = str_replace ( '%value%', $value, $value_format_mask );
				}
				
				$file_r = file_cache_get_image_r ( $value, 'display' );
				$url = $file_r ['fullsize'] ['url'];
				$display_value_r [] = "<a href=\"" . $value . "\" onclick=\"fileviewer('$url' ,'" . ($width + 20) . "', '" . ($height + 25) . "', '" . $target . "'); return false;\" title=\"" . $item_attribute_type_r ['prompt'] . "\" class=\"popuplink\">$value_format_mask</a>";
			}
			
			$field = format_multivalue_block ( $display_value_r, 'fileviewer' );
			
			if ($dowrap)
				return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
			else
				return $field;
		} else {
			return '';
		}
	} else if ($item_attribute_type_r ['display_type'] == 'datetime') {
		if (is_array ( $value ))
			$values = $value;
		else
			$values [] = $value;
		
		if (count ( $values ) > 0) {
			$display_value_r = array ();
			foreach ( $values as $value ) {
				$value = trim ( $value );
				
				$timestamp = get_timestamp_for_datetime ( $value, 'YYYYMMDDHH24MISS' );
				if ($timestamp !== FALSE) {
					if (strlen ( $item_attribute_type_r ['display_type_arg1'] ) == 0)
						$item_attribute_type_r ['display_type_arg1'] = 'DD/MM/YYYY';
					
					$datetime = get_localised_timestamp ( $item_attribute_type_r ['display_type_arg1'], $timestamp );
					if ($datetime !== FALSE)
						$display_value_r [] = $datetime;
					else
						$display_value_r [] = $value;
				} else {
					$display_value_r [] = $value;
				}
			}
			
			$field = format_multivalue_block ( $display_value_r, 'datetime' );
			
			if ($dowrap)
				return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
			else
				return $field;
		} else {
			return '';
		}
	} else if ($item_attribute_type_r ['display_type'] == 'format_mins') {
		if (is_array ( $value ))
			$values = $value;
		else
			$values [] = $value;
		
		if (count ( $values ) > 0) {
			$display_value_r = array ();
			foreach ( $values as $value ) {
				$value = trim ( $value );
				if (is_numeric ( $value )) {
					// Ensure we have a mask to work with.
					$display_mask = $item_attribute_type_r ['display_type_arg1'];
					if (strlen ( $display_mask ) == 0)
						$display_mask = '%h %H %m %M';
					
					$hrs = floor ( $value / 60 ); // hours
					$mins = $value % 60; // minutes
					

					// Process display_mask and remove any bits that are not needed because the hour/minute is zero.
					if ($mins == 0 && $hrs > 0) {	// only get rid of minutes if $hrs is a value.
						$index = strpos ( $display_mask, '%H' );
						if ($index !== FALSE)
							$display_mask = substr ( $display_mask, 0, $index + 2 ); //include the %H
						else {
							$index = strpos ( $display_mask, '%m' );
							if ($index != FALSE)
								$display_mask = substr ( $display_mask, 0, $index ); //include the %H
						}
					} else if ($hrs == 0) {
						$index = strpos ( $display_mask, '%m' );
						if ($index != FALSE)
							$display_mask = substr ( $display_mask, $index ); //include the %H
					}
					
					// Unfortunately we need to do $mins>0 and $hrs>0 if's twice, because otherwise once we
					// replace the %h and %H the test for $mins>0 would not be able to cut the display_mask,
					// based on the %h/%H...
					if ($hrs > 0) {
						// Now do all replacements.
						$display_mask = str_replace ( '%h', $hrs, $display_mask );
						if ($hrs != 1)
							$display_mask = str_replace ( '%H', get_opendb_lang_var ( 'hours' ), $display_mask );
						else
							$display_mask = str_replace ( '%H', get_opendb_lang_var ( 'hour' ), $display_mask );
					}
					
					if ($mins >= 0 || ($hrs === 0 && $mins === 0)) {
						// Now do minute replacements only.
						$display_mask = str_replace ( '%m', $mins, $display_mask );
						if ($mins != 1)
							$display_mask = str_replace ( '%M', get_opendb_lang_var ( 'minutes' ), $display_mask );
						else
							$display_mask = str_replace ( '%M', get_opendb_lang_var ( 'minute' ), $display_mask );
					}
					
					$display_value_r [] = $display_mask;
				} else {
					// what else can we do here?!
					$display_value_r [] = $value;
				}
			}
			
			$field = format_multivalue_block ( $display_value_r, 'format_mins' );
			if ($dowrap)
				return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
			else
				return $field;
		} else {
			return '';
		}
	} else if ($item_attribute_type_r ['display_type'] == 'star_rating') {	// arg[0] = rating range
		if (is_array ( $value ))
			$values = $value;
		else
			$values [] = $value;
		
		if (count ( $values ) > 0) {
			$display_value_r = array ();
			foreach ( $values as $value ) {
				$value = trim ( $value );
				
				// no point unless numeric
				if (is_numeric ( $value )) {
					$total_count = $item_attribute_type_r ['display_type_arg1'];
					if (is_numeric ( $total_count )) {
						$display_value = '';
						$j = $value;
						for($i = 0; $i < $total_count; ++ $i) {
							if ($j >= 0.75)
								$display_value .= theme_image ( 'rs.gif' );
							else if ($j >= 0.25)
								$display_value .= theme_image ( 'rgs.gif' );
							else
								$display_value .= theme_image ( 'gs.gif' );
							$j = $j - 1;
						}
						
						$ratingmask = $item_attribute_type_r ['display_type_arg2'];
						if (strlen ( $ratingmask ) > 0) {
							$ratingmask = str_replace ( '%value%', $value, $ratingmask );
							$ratingmask = str_replace ( '%maxrange%', $total_count, $ratingmask );
							$display_value = str_replace ( '%starrating%', $display_value, $ratingmask );
						}
						
						if ($item_attribute_type_r ['listing_link_ind'] == 'Y') {
							$display_value = format_listing_link ( $value, $display_value, $item_attribute_type_r, NULL );
						}
					}
					
					$display_value_r [] = $display_value;
				}
			}
			
			$field = format_multivalue_block ( $display_value_r, 'starrating' );
			if ($dowrap)
				return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
			else
				return $field;
		} else {
			return ''; // nothing to do!
		}
	} else if (! is_array ( $value ) && $item_attribute_type_r ['display_type'] == 'display' && ifempty ( $item_attribute_type_r ['display_type_arg1'], '%value%' ) == '%value%') {
		// Support newline formatting by default.
		$value = nl2br ( trim ( $value ) );
		
		if ($item_attribute_type_r ['listing_link_ind'] == 'Y')
			$field = format_listing_links ( $value, $item_attribute_type_r, 'exact' );
		else
			$field = $value;
		
		if ($dowrap)
			return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
		else
			return $field;
	} else if ($item_attribute_type_r ['display_type'] == 'list') {	//list(list_type [,delimiter])
		if (is_array ( $value )) {
			$values = $value;
			$attr_match = 'exact';
		} else {
			$value = trim ( $value );
			
			if (strlen ( $item_attribute_type_r ['display_type_arg2'] ) == 0) {			// Use newline!
				$values = explode_lines ( $value );
				$attr_match = 'partial';
			} else {
				$values = explode ( $item_attribute_type_r ['display_type_arg2'], $value );
				
				if (strlen ( trim ( $item_attribute_type_r ['display_type_arg2'] ) ) === 0)
					$attr_match = 'word';
				else
					$attr_match = 'partial';
			}
		}
		
		$field = format_list_from_array ( $values, $item_attribute_type_r, $item_attribute_type_r ['listing_link_ind'] == 'Y' ? $attr_match : FALSE );
		if ($dowrap)
			return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
		else
			return $field;
	} else if ($item_attribute_type_r ['display_type'] == 'category' || $item_attribute_type_r ['display_type'] == 'display') {
		$field = '';
		
		if (is_array ( $value ))
			$value_array = $value;
		else
			$value_array [] = $value;
		
		$attribute_value_rs = array ();
		
		if ($item_attribute_type_r ['lookup_attribute_ind'] == 'Y') {
			$results = fetch_value_match_attribute_type_lookup_rs ( $item_attribute_type_r ['s_attribute_type'], $value_array, get_lookup_order_by ( $item_attribute_type_r ['display_type_arg1'] ), 'asc' );
			if ($results) {
				while ( $lookup_r = db_fetch_assoc ( $results ) ) {
					$lookup_key = array_search2 ( $lookup_r ['value'], $value_array, TRUE );
					if ($lookup_key !== FALSE) {
						// Remove the matched element
						array_splice ( $value_array, $lookup_key, 1 );
						
						$attribute_value_rs [] = array (
								'value' => $lookup_r ['value'],
								'display' => $lookup_r ['display'],
								'img' => $lookup_r ['img'] );
					}
				}
				db_free_result ( $results );
			}
		}
		
		// where extra items that do not have a matching lookup value.
		if (is_not_empty_array ( $value_array )) {
			reset ( $value_array );
			foreach ( $value_array as $value ) {
				if (strlen ( trim ( $value ) ) > 0) {				// In case there are extra spaces
					$attribute_value_rs [] = array (
							'value' => $value,
							'display' => $value,
							'img' => '' );
				}
			}
		}
		
		if (is_not_empty_array ( $attribute_value_rs )) {
			$field = format_lookup_display_block ( $item_attribute_type_r, $attribute_value_rs );
			if (strlen ( $field ) > 0) {
				if ($dowrap)
					return format_field ( $item_attribute_type_r ['prompt'], $field, $prompt_mask );
				else
					return $field;
			} else {
				return NULL;
			}
		}
	} else if ($item_attribute_type_r ['display_type'] == 'review') {
		$total_count = fetch_attribute_type_cnt ( 'S_RATING' );
		if (is_numeric ( $total_count )) {
			$value = trim ( $value );
			if (! is_numeric ( $value )) {
				$value = 0;
			}
			$field = '';
			$j = $value;
			for($i = 0; $i < $total_count; ++ $i) {
				if ($j >= 0.75)
					$field .= theme_image ( 'rs.gif' );
				else if ($j >= 0.25)
					$field .= theme_image ( 'rgs.gif' );
				else
					$field .= theme_image ( 'gs.gif' );
				$j = $j - 1;
			}
			
			// If a mask is defined, format the display value.
			if (strlen ( $item_attribute_type_r ['display_type_arg1'] ) > 0) {
				$lookup_r = fetch_attribute_type_lookup_r ( 'S_RATING', $value );
				if (is_not_empty_array ( $lookup_r )) {
					$field .= format_display_value ( $item_attribute_type_r ['display_type_arg1'], $lookup_r ['img'], $lookup_r ['value'], $lookup_r ['display'] );
				}
			}
			return $field; // this is only used in a few places.
		}
	}
	
	//else -- no display type match.
	if ($dowrap)
		return format_field ( $item_attribute_type_r ['prompt'], nl2br ( $value ), $prompt_mask );
	else
		return nl2br ( $value );
}

function format_multivalue_block($display_value_r, $type) {
	if (count ( $display_value_r ) > 1) {
		$field = "<ul class=\"$type\">";
		
		$first = TRUE;
		reset ( $display_value_r );
		foreach ( $display_value_r as $value ) {
			$field .= '<li' . ($first ? ' class="first"' : '') . '>' . $value . '</li>';
			
			if ($first)
				$first = FALSE;
		}
		$field .= "</ul>";
	} else {
		$field = $display_value_r [0];
	}
	return $field;
}

function format_lookup_display_block($item_attribute_type_r, $attribute_value_rs) {
	$block = '';
	
	$first = TRUE;
	foreach ( $attribute_value_rs as $attribute_value_r ) {
		$display_value = format_lookup_display_field ( $item_attribute_type_r, $attribute_value_r );
		
		$block .= '<li' . ($first ? ' class="first"' : '') . '>' . $display_value . '</li>';
		
		if ($first)
			$first = FALSE;
	}
	
	if (strlen ( $block ) > 0) {
		$class = '';
		
		if ($item_attribute_type_r ['display_type'] == 'category') {
			$class = 'category';
		}
		
		if (count ( $attribute_value_rs ) == 1) {
			if (strlen ( $class ) > 0)
				$class .= ' ';
			
			$class .= 'single';
		}
		
		return '<ul' . (strlen ( $class ) > 0 ? ' class="' . $class . '"' : '') . '>' . $block . '</ul>';
	} else {
		return NULL;
	}
}

function format_lookup_display_field($item_attribute_type_r, $attribute_value_r) {
	$display_value = format_display_value ( $item_attribute_type_r ['display_type_arg1'], $attribute_value_r ['img'], $attribute_value_r ['value'], $attribute_value_r ['display'] );
	
	// Add listings.php link if required.
	if ($item_attribute_type_r ['listing_link_ind'] == 'Y') {
		$display_value = format_listing_link ( $attribute_value_r ['value'], $display_value, $item_attribute_type_r, $item_attribute_type_r ['display_type'] == 'category' ? 'category' : 'exact' );
	}
	return $display_value;
}

function format_listing_links($value, $item_attribute_type_r, $attr_match) {
	if (is_array ( $value ))
		$tokens = $value;
	else
		$tokens [] = $value;
	
	foreach ( $tokens as $token ) {
		$token = trim ( $token );
		$lines [] = format_listing_link ( $token, $token, $item_attribute_type_r, $attr_match );
	}
	
	// If no array passed in, then pass back normal string!
	if (is_array ( $value ))
		return $lines;
	else
		return $lines [0];
}

/**
	$attr_match
		word		A '= $value match' OR 'LIKE % $value% ' OR 'LIKE '%$value ' OR 'LIKE '% $value%'
		exact		A '= "$value match"'
		partial		A 'LIKE %$value%' match
		category	listings will handle this special type, by linking against item.category instead
		of the item_attribute.attribute_val...
*/
function format_listing_link($value, $display, $item_attribute_type_r, $attr_match) {
	// The % cannot exist in a database column, whereas the '_' can.  This is
	// why we only need to escape the _.  We escape it by specifying it twice!
	$value = trim ( str_replace ( "_", "\\_", $value ) );
	
	// If any whitespace, then enclose with quotes, otherwise will be treated by boolean parser as 
	// separate words, which is not desirable.
	if ($attr_match != 'exact' && strpos ( $value, " " ) !== FALSE)
		$value = urlencode ( "\"" . $value . "\"" );
	else
		$value = urlencode ( $value );
	
	return "<a href=\"listings.php?attribute_list=y&attr_match=$attr_match&attribute_type=" . $item_attribute_type_r ['s_attribute_type'] . "&s_status_type=ALL&attribute_val=" . $value . "&order_by=title&sortorder=ASC\" title=\"" . get_opendb_lang_var ( 'list_items_with_same_prompt', 'prompt', $item_attribute_type_r ['prompt'] ) . "\" class=\"listlink\">$display</a>";
}

/**
	@param $tokens	Expects $tokens to be an array otherwise will return empty string.

	@param $attr_match
					Specified $attr_match type for list-link.  If FALSE, then no list-link
					to be added.
					
	$args[0] = type
	$args[1] ... $args[3] = list args					
*/
function format_list_from_array($tokens, $item_attribute_type_r, $attr_match = FALSE) {
	if (is_not_empty_array ( $tokens )) {
		$first = TRUE;
		$value = '';
		foreach ( $tokens as $token ) {
			if ($attr_match !== FALSE)
				$token = format_listing_link ( $token, $token, $item_attribute_type_r, $attr_match );
			
			$value .= '<li';
			
			if ($first) {
				$value .= ' class="' . ($first ? 'first' : '') . '"';
			}
			$value .= '>' . $token . '</li>';
			
			if ($first)
				$first = FALSE;
		}
		
		$list_type = $item_attribute_type_r ['display_type_arg1'];
		
		$class = $list_type;
		
		if (count ( $tokens ) == 1) {
			$class .= ' single';
		}
		
		if ($list_type == 'ordered') {
			return '<ol class="' . $class . '">' . $value . '</ol>';
		} else { // plain, unordered, nl2br, ticks, names
			return '<ul class="' . $class . '">' . $value . '</ul>';
		}
	}
}

function format_display_value($mask, $img, $value, $display, $theme_image_type = NULL) {
	// The default.
	if (strlen ( $mask ) == 0)
		$mask = "%display%";
		
	// Note: We are only modifying local copy of $mask for return.
	if (strlen ( trim ( $img ) ) > 0 && $img !== "none") {
		$image = theme_image ( $img, $display, $theme_image_type );
		if (strlen ( $image ) > 0)
			$mask = str_replace ( "%img%", $image, $mask );
		else if (strlen ( $display ) > 0)
			$mask = str_replace ( "%img%", $display, $mask );
		else
			$mask = str_replace ( "%img%", $value, $mask );
	} else if ($img === "none") { 	// A image value with "none" indicates we should replace image with empty string.
		$mask = str_replace ( "%img%", "", $mask );
	} else {
		// If no %display% mask variable, replace missing image with display field instead.
		if (strpos ( $mask, '%display%' ) === FALSE) {
			$mask = str_replace ( "%img%", $display, $mask );
		} else if (strpos ( $mask, '%value%' ) === FALSE && strcmp ( $value, $display ) !== 0) {// but only if display is NOT the same as value
			$mask = str_replace ( "%img%", $value, $mask );
		} else {
			$mask = str_replace ( "%img%", "", $mask );
		}
	}
	
	$mask = str_replace ( "%display%", $display, $mask );
	$mask = str_replace ( "%value%", $value, $mask );
	
	return $mask;
}
?>
