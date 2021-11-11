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
include_once("./lib/config.php");
include_once("./lib/parseutils.php");
include_once("./lib/item_attribute.php");

class TitleMask {
	var $_opendb_title_mask_macro_rs = array ();
	
	// stores title display mask config, in parsed state.
	var $_title_mask_rs = array ();
	var $_mask_group = NULL;
	
	// cache s_item_type -> s_item_type_group mapping
	var $_cache_s_item_type_group_map = array ();
	
	// these variables are cached for the expand_title call, and reset when
	// reset() function called.
	var $_display_mask_elements = NULL;
	var $_display_mask = NULL;

	/**
	    @param mask_group - if an array, then will use first group that has values defined.
	*/
	function __construct($mask_group = NULL) {
		if ($mask_group !== NULL) {
			if (is_array ( $mask_group )) {
				foreach ( $mask_group as $group ) {
					$results = fetch_title_display_mask_rs ( $group );
					if ($results) {
						$this->_mask_group = $group;
						break;
					}
				}
			} else {
				$this->_mask_group = $mask_group;
				$results = fetch_title_display_mask_rs ( $mask_group );
			}
			
			$default_found = FALSE;
			if ($results) {
				while ( $title_display_mask_r = db_fetch_assoc ( $results ) ) {
					if ($title_display_mask_r ['s_item_type_group'] == '*' && $title_display_mask_r ['s_item_type'] == '*') {
						$default_found = TRUE;
					}
					
					$this->_title_mask_rs [] = $title_display_mask_r;
				}
				db_free_result ( $results );
			}
			
			// fall back on a default if none defined
			if (! $default_found) {
				$this->_title_mask_rs [] = array (
						's_item_type_group' => '*',
						's_item_type' => '*',
						'display_mask' => '{title}' );
			}
		}
	}

	function expand_item_title($item_instance_r, $config_var_rs = NULL) {
		$index = $this->_get_title_mask_idx ( NULL, $item_instance_r ['s_item_type'] );
		if ($index != - 1) {
			if (! isset ( $this->_title_mask_rs [$index] ['parsed_display_mask'] )) {
				$display_mask = $this->_title_mask_rs [$index] ['display_mask'];
				
				// need to add parsed title mask now.
				$display_mask_elements = $this->_parse_field_mask ( $display_mask );
				
				// now merge extra info in now
				$this->_title_mask_rs [$index] = array_merge ( $this->_title_mask_rs [$index], array (
						'parsed_display_mask' => $display_mask,
						'display_mask_elements' => $display_mask_elements ) );
				
				unset ( $display_mask );
				unset ( $display_mask_elements );
			}
			
			$display_mask = $this->_title_mask_rs [$index] ['parsed_display_mask'];
			$display_mask_elements = $this->_title_mask_rs [$index] ['display_mask_elements'];
			
			return $this->_expand_title_mask ( $item_instance_r, $display_mask, $display_mask_elements, $config_var_rs );
		} else {
			// what the fuck
			return FALSE;
		}
	}

	/**
	    Expand mask with reference to item title and attributes, but supply own mask
	    
	    Caching of last parsed mask is enabled
	*/
	function expand_title($vars_r, $mask, $config_var_rs = NULL) {
		if (strlen ( $this->_mask ) == 0) {
			$this->_mask_elements = $this->_parse_field_mask ( $mask );
			$this->_mask = $mask;
		}
		
		return $this->_expand_title_mask ( $vars_r, $this->_mask, $this->_mask_elements, $config_var_rs );
	}

	/**
        Expand mask without reference to item title and supply own mask
        
        Caching of last parsed mask is enabled
    */
	function expand_mask($vars_r, $mask, $config_var_rs = NULL) {
		if (strlen ( $this->_mask ) == 0) {
			$this->_mask_elements = $this->_parse_field_mask ( $mask );
			$this->_mask = $mask;
		}
		
		return $this->_expand_field_mask ( $vars_r, $this->_mask, $this->_mask_elements, $config_var_rs );
	}

	/**
        No caching of last parsed mask
    */
	function expand_mask_no_cache($var_r, $mask, $config_var_rs = NULL) {
		$mask_elements = $this->_parse_field_mask ( $mask );
		return $this->_expand_field_mask ( $var_r, $mask, $mask_elements, $config_var_rs );
	}

	/**
        This is only effective for simple expand_title function
    */
	function reset() {
		$this->_mask_elements = NULL;
		$this->_mask = NULL;
	}

	function title_mask_macro_element_r($macro_type) {
		if (isset ( $this->_opendb_title_mask_macro_rs [$macro_type] ))
			return $this->_opendb_title_mask_macro_rs [$macro_type];
		else
			return NULL;
	}

	/**
		modify display_mask so that all {variable} are replaced with
		references to the array indexes returned.
	*/
	function _parse_field_mask(&$display_mask) {
		$i = 0;
		$array_of_vars = [];
		$inside_variable = 0;
		$variable = '';
		$new_display_mask = '';
		
		for ($i = 0; $i < strlen ( $display_mask ); $i ++) {
			if ($inside_variable > 0) {
				// if closing bracket
				if ($display_mask [$i] == '}') {
					// indicate close of reference.
					$inside_variable --;
					
					// only if we have encountered final closing bracket!
					if ($inside_variable == 0) {
						if (strlen ( $variable ) > 0) {
							// constrain to legal function names here.
							$function_r = prc_function_spec ( $variable, true );
							
							// so we get a clean func definition for each $variable.
							unset ( $func );
							
							// only legal function names.
							if (is_array ( $function_r )) {
								// will actually define the particular arguments here.
								switch ($function_r ['type']) {
									case 'if' : // if(varname[<|<=|>=|>|==|!=]value, "title_mask")
										$func ['type'] = $function_r ['type']; //'if'
										

										// parse the condition.
										$this->_parse_if_condition ( $function_r ['args'] [0], $func );
										
										$func ['if_mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [1] );
										// remember parse_field_mask resets the argument to {1}, {2}, etc.
										$func ['if_mask'] = $function_r ['args'] [1];
										
										if (count ( $function_r ['args'] ) > 2) {
											$func ['else_mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [2] );
											// remember parse_field_mask resets the argument to {1}, {2}, etc.
											$func ['else_mask'] = $function_r ['args'] [2];
										}
										$array_of_vars [] = $func;
										break;
									
									case 'switch' : // switch(value, case, result[, case, result[,...][, default])
										$func ['type'] = $function_r ['type']; //'if'
										$func ['varname'] = $function_r ['args'] [0];
										
										$j = 1;
										while ( ($j + 1) < count ( $function_r ['args'] ) ) {
											$case = $function_r ['args'] [$j ++];
											$result ['mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [$j] );
											$result ['mask'] = $function_r ['args'] [$j];
											
											$func ['cases'] [] = array (
													'case' => $case,
													'result' => $result );
											$j ++;
										}
										
										// a default element
										if ($j < count ( $function_r ['args'] )) {
											$result ['mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [$j] );
											$result ['mask'] = $function_r ['args'] [$j];
											$func ['default_case'] = $result;
										}
										
										$array_of_vars [] = $func;
										break;
									
									case 'ifdef' :
										$func ['type'] = $function_r ['type']; //'ifdef'
										$func ['varname'] = $function_r ['args'] [0];
										
										// now do the mask
										$func ['if_mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [1] );
										// remember parse_field_mask resets the argument to {1}, {2}, etc.
										$func ['if_mask'] = $function_r ['args'] [1];
										
										if (count ( $function_r ['args'] ) > 2) {
											$func ['else_mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [2] );
											// remember parse_field_mask resets the argument to {1}, {2}, etc.
											$func ['else_mask'] = $function_r ['args'] [2];
										}
										
										$array_of_vars [] = $func;
										break;
									
									case 'theme_img' : // only supports one level deep for the prompt_expression (second argument).
										$func ['value'] = NULL;
										$func ['type'] = $function_r ['type'];
										$func ['img'] = $function_r ['args'] [0];
										$func ['title_mask_elements'] = $this->_parse_field_mask ( $function_r ['args'] [1] );
										
										// if not an array, then expand
										if (! is_array ( $func ['title_mask_elements'] )) {
											$func ['title_mask_elements'] [] = $function_r ['args'] [1];
											$func ['title_mask'] = '{0}';
										} else 										// otherwise nested {...} block for value.
{
											// this will cause the widget to be rendered, but no help entry
											// will be added to the listings page, because the $this->_opendb_title_mask_macro_rs
											// has not been updated.
											$func ['title_mask'] = $function_r ['args'] [1];
										}
										
										$array_of_vars [] = $func;
										break;
									
									case 'config_var_key' : // config_var_keyid(name, value)
										$func ['type'] = $function_r ['type'];
										$func ['name'] = $function_r ['args'] [0];
										$tmp_parsed_arg = $this->_parse_field_mask ( $function_r ['args'] [1] );
										if (is_array ( $tmp_parsed_arg )) {
											$func ['value'] = array (
													'mask' => $function_r ['args'] [1],
													'elements' => $tmp_parsed_arg );
										} else {
											$func ['value'] = $function_r ['args'] [1];
										}
										
										$array_of_vars [] = $func;
										break;
									
									case 'config_var_value' : // config_var_value(name, keyid)
										$func ['type'] = $function_r ['type'];
										$func ['name'] = $function_r ['args'] [0];
										$tmp_parsed_arg = $this->_parse_field_mask ( $function_r ['args'] [1] );
										if (is_array ( $tmp_parsed_arg )) {
											$func ['key'] = array (
													'mask' => $function_r ['args'] [1],
													'elements' => $tmp_parsed_arg );
										} else {
											$func ['key'] = $function_r ['args'] [1];
										}
										$array_of_vars [] = $func;
										break;
									
									default :
										// for unknown functions - copy the type, and expand any embedded
										// mask definitions.
										$func ['type'] = $function_r ['type'];
										for($j = 0; $j < count ( $function_r ['args'] ); $j ++) {
											$tmp_parsed_arg = $this->_parse_field_mask ( $function_r ['args'] [$j] );
											if (is_array ( $tmp_parsed_arg )) {
												$func ['args'] [] = array (
														'mask' => $function_r ['args'] [$j],
														'elements' => $tmp_parsed_arg );
											} else {
												$func ['args'] [] = $function_r ['args'] [$j];
											}
										}
										
										$array_of_vars [] = $func;
										
										break;
								}

							} elseif ($variable == 'title' || $variable == 's_item_type' || $variable == 's_status_type' || $variable == 'item_id' || $variable == 'instance_no') {
								$array_of_vars [] = $variable;

							} else {
								$index_of_sep = strrpos ( $variable, '.' );
								if ($index_of_sep !== false) {
									$s_attribute_type = strtoupper ( substr ( $variable, 0, $index_of_sep ) );
									$option = substr ( $variable, $index_of_sep + 1 );
									
									if ($option == 'display_type') {
										$attribute_r = fetch_attribute_type_r ( $s_attribute_type );

										if ($attribute_r)
											$array_of_vars [] = array (
												's_attribute_type' => $s_attribute_type,
												'option' => $option,
												'display_type' => $attribute_r ['display_type'],
												'display_type_arg1' => $attribute_r ['display_type_arg1'],
												'display_type_arg2' => $attribute_r ['display_type_arg2'],
												'display_type_arg3' => $attribute_r ['display_type_arg3'],
												'display_type_arg4' => $attribute_r ['display_type_arg4'],
												'display_type_arg5' => $attribute_r ['display_type_arg5'],
												'prompt' => $attribute_r ['prompt'] );
									} else {
										$array_of_vars [] = array (
												's_attribute_type' => $s_attribute_type,
												'option' => $option );
									}
								} else {
									$array_of_vars [] = array (
											's_attribute_type' => strtoupper ( $variable ),
											'option' => 'value' );
								}
							}
							
							// add a {array_reference} to display_mask.
							$new_display_mask .= '{' . (count ( $array_of_vars ) - 1) . '}';
						}
						$variable = '';
					} else 					// no final closing bracket.
{
						$variable .= '}';
					}
				} else if ($display_mask [$i] == '{') {
					$inside_variable ++;
					$variable .= '{';
				} else {
					$variable .= $display_mask [$i];
				}
			} else if ($display_mask [$i] != '{') 			//first opening bracket.
{
				$new_display_mask .= $display_mask [$i];
			} else 			//if($display_mask[$i] == '{')
{
				$inside_variable ++;
			}
		}
		
		// return parsed mask (via pass-by-reference) back to caller.
		$display_mask = $new_display_mask;
		
		// return array of parsed sections as well.
		return $array_of_vars;
	}

	/**
		this takes the parsed array originally returned from parse_field_mask($display_mask) and expands it with the
		specific $item_instance_r information.
	*/
	function _expand_title_mask($item_instance_r, $mask, &$mask_element_rs, $config_var_rs = NULL) {
		// if no parsed mask elements, then return $mask.
		if (is_empty_or_not_array ( $mask_element_rs )) {
			// only return mask if there is something to return.
			if (strlen ( $mask ) > 0)
				return $mask;
			else
				return $item_instance_r ['title'];
		}
		
		for($i = 0; $i < count ( $mask_element_rs ); $i ++) {
			// no array set, or simple attribute variable 's_attribute_type.option' not set.
			if (is_not_empty_array ( $mask_element_rs[$i] ) && ! isset ( $mask_element_rs [$i] ['s_attribute_type'] ) && ! isset ( $mask_element_rs [$i] ['option'] )) {
				// replace the array index.
				switch ($mask_element_rs[$i]['type']) {
					case 'ifdef' : // ifdef(s_attribute_type, "if_mask"[, "else_mask"])
						if (isset($item_instance_r['item_id']) && is_item_attribute_set( $item_instance_r['item_id'], $item_instance_r['instance_no'], strtoupper( $mask_element_rs[$i]['varname'] ) ))
							$value = $this->_expand_title_mask( $item_instance_r, $mask_element_rs[$i]['if_mask'], $mask_element_rs[$i]['if_mask_elements'] );
						else if (strlen( $mask_element_rs[$i]['else_mask'] ?? "" ) > 0)
							$value = $this->_expand_title_mask( $item_instance_r, $mask_element_rs[$i]['else_mask'], $mask_element_rs[$i]['else_mask_elements'] );
						else
							$value = NULL;
						break;
					
					case 'if' : // if(varname[<|<=|>=|>|==|!=]value, "if_mask"[, "else_mask"])
						if ($mask_element_rs[$i]['varname'] == 'instance_no')
							$value = $item_instance_r['instance_no'];
						else if ($mask_element_rs[$i]['varname'] == 's_status_type')
							$value = $item_instance_r['s_status_type'];
						else if ($mask_element_rs[$i]['varname'] == 's_item_type')
							$value = $item_instance_r['s_item_type'] ?? "";
						else {
							$value = fetch_attribute_val( $item_instance_r['item_id'], $item_instance_r['instance_no'], strtoupper( $mask_element_rs[$i]['varname'] ) );
						}
						
						// the attribute is defined, so now lets do the comparison.
						if ($value !== false) {
							if ($this->_test_if_condition( $value, $mask_element_rs[$i]['op'], $mask_element_rs[$i]['value'] ))
								$value = $this->_expand_title_mask( $item_instance_r, $mask_element_rs[$i]['if_mask'], $mask_element_rs[$i]['if_mask_elements'] );
							else if (strlen( $mask_element_rs[$i]['else_mask'] ?? "" ) > 0)
								$value = $this->_expand_title_mask( $item_instance_r, $mask_element_rs[$i]['else_mask'], $mask_element_rs[$i]['else_mask_elements'] );
							else {
								$value = NULL;
							}
						} else {
							$value = NULL;
						}
						break;
					
					case 'switch' :
						if ($mask_element_rs [$i] ['varname'] == 'instance_no')
							$value = $item_instance_r ['instance_no'];
						else if ($mask_element_rs [$i] ['varname'] == 's_status_type')
							$value = $item_instance_r ['s_status_type'];
						else if ($mask_element_rs [$i] ['varname'] == 's_item_type')
							$value = $item_instance_r ['s_item_type'];
						else {
							$value = fetch_attribute_val ( $item_instance_r ['item_id'], $item_instance_r ['instance_no'], strtoupper ( $mask_element_rs [$i] ['varname'] ) );
						}
						
						// the attribute is defined, so now lets do the comparison.
						if (! empty ( $value )) {
							
							if (is_not_empty_array ( $mask_element_rs [$i] ['cases'] )) {
								for($j = 0; $j < count ( $mask_element_rs [$i] ['cases'] ); $j ++) {
									// if a match.
									if (strcmp ( $value, $mask_element_rs [$i] ['cases'] [$j] ['case'] ) === 0) {
										$value = $this->_expand_title_mask ( $item_instance_r, $mask_element_rs [$i] ['cases'] [$j] ['result'] ['mask'], $mask_element_rs [$i] ['cases'] [$j] ['result'] ['mask_elements'] );
										break 2; // break out of switch
									}
								}
							}
							
							if (is_not_empty_array ( $mask_element_rs [$i] ['default_case'] )) {
								$value = $this->_expand_title_mask ( $item_instance_r, $mask_element_rs [$i] ['default_case'] ['mask'], $mask_element_rs [$i] ['cases'] ['default_case'] ['mask_elements'] );
							}
						} else {
							$value = NULL;
						}
						break;
					
					case 'theme_img' :
						if (strlen ( $mask_element_rs [$i] ['value'] ) > 0) {
							$value = $mask_element_rs [$i] ['value'];
						} else {
							if ($mask_element_rs [$i] ['value'] === NULL && strlen ( ($vtitle = $this->_expand_title_mask ( array (
									's_item_type' => $item_instance_r ['s_item_type'] ), $mask_element_rs [$i] ['title_mask'], $mask_element_rs [$i] ['title_mask_elements'] )) ) > 0) {
								if (! is_array ( $this->_opendb_title_mask_macro_rs ['theme_img'] )) {
									$this->_opendb_title_mask_macro_rs = array_merge ( $this->_opendb_title_mask_macro_rs, array (
											'theme_img' => array () ) );
								}
								
								// this variable will be an array of all theme_img elements encountered
								if (strlen ( $this->_opendb_title_mask_macro_rs ['theme_img'] [$mask_element_rs [$i] ['img']] ) == 0) {
									$this->_opendb_title_mask_macro_rs ['theme_img'] = array_merge ( $this->_opendb_title_mask_macro_rs ['theme_img'], array (
											$mask_element_rs [$i] ['img'] => $vtitle ) );
								}
								
								$mask_element_rs [$i] ['value'] = theme_image ( $mask_element_rs [$i] ['img'], $vtitle );
								$value = $mask_element_rs [$i] ['value'];
							} else 							// no, then leave for item_id context.
{
								// an indicator to not try generic expand_title_mask for $s_item_type, because it does not work.
								$mask_element_rs [$i] ['value'] = '';
								
								$vtitle = $this->_expand_title_mask ( $item_instance_r, $mask_element_rs [$i] ['title_mask'], $mask_element_rs [$i] ['title_mask_elements'] );
								$value = theme_image ( $mask_element_rs [$i] ['img'], $vtitle );
							}
						}
						break;
					
					case 'config_var_key' : // config_var_key(name, value)
						if (is_not_empty_array ( $config_var_rs ) && is_array ( $config_var_rs [$mask_element_rs [$i] ['name']] )) {
							if (is_array ( $mask_element_rs [$i] ['value'] ))
								$srchvalue = $this->_expand_title_mask ( $item_instance_r, $mask_element_rs [$i] ['value'] ['mask'], $mask_element_rs [$i] ['value'] ['elements'], $config_var_rs );
							else
								$srchvalue = $mask_element_rs [$i] ['value'];
							
							$tmpvalue = array_search2 ( $srchvalue, $config_var_rs [$mask_element_rs [$i] ['name']] );
							if ($tmpvalue !== false)
								$value = $tmpvalue;
							else
								$value = '';
						} else {
							$value = '';
						}
						break;
					
					case 'config_var_value' : // config_var_value(name, keyid)
						if (is_not_empty_array ( $config_var_rs ) && is_array ( $config_var_rs [$mask_element_rs [$i] ['name']] )) {
							if (is_array ( $mask_element_rs [$i] ['key'] ))
								$srchkey = $this->_expand_title_mask ( $item_instance_r, $mask_element_rs [$i] ['key'] ['mask'], $mask_element_rs [$i] ['key'] ['elements'], $config_var_rs );
							else
								$srchkey = $mask_element_rs [$i] ['key'];
							
							if (isset ( $config_var_rs [$mask_element_rs [$i] ['name']] [$srchkey] ))
								$value = $config_var_rs [$mask_element_rs [$i] ['name']] [$srchkey];
							else
								$value = '';
						} else {
							$value = '';
						}
						break;
					
					default : // no valid function specified, so set to empty.
						$value = '';
				}

			} else {
				// standard variable (title, instance_no, item_attribute or plain text)
				$value = $this->_get_mask_variable_value ( $mask_element_rs [$i], $item_instance_r );
			}
			
			// now do the replacement.
			$mask = str_replace ( '{' . $i . '}', $value, $mask );
		} //for($i=0; $i<count($mask_element_rs); $i++)
		

		// now return expanded subject.
		return $mask;
	}

	/**
	* A simple field mask parser, which only supports 'if' and 'switch' and config_var_key(...)
	* config_var_value(...)
	*/
	function _expand_field_mask(&$values_rs, $mask, &$mask_element_rs, $config_var_rs = NULL) {
		// If no parsed mask elements, then return $mask.
		if (is_empty_array ( $mask_element_rs )) {
			// Only return mask if there is something to return.
			if (strlen ( $mask ) > 0)
				return $mask;
			else
				return NULL;
		}
		
		for($i = 0; $i < count ( $mask_element_rs ); $i ++) {
			// no array set, or simple attribute variable 's_attribute_type.option' not set.
			if (is_not_empty_array ( $mask_element_rs [$i] ) && ! isset ( $mask_element_rs [$i] ['s_attribute_type'] ) && ! isset ( $mask_element_rs [$i] ['option'] )) {
				// Replace the array index.
				switch ($mask_element_rs [$i] ['type']) {
					case 'ifdef' : // ifdef(s_attribute_type, "if_mask"[, "else_mask"])
						if (isset( $values_rs[$mask_element_rs[$i]['varname']] ))
							$value = $this->_expand_field_mask ( $values_rs, $mask_element_rs[$i]['if_mask'], $mask_element_rs[$i]['if_mask_elements'], $config_var_rs );
						else if (strlen( $mask_element_rs[$i]['else_mask'] ?? '' ) > 0)
							$value = $this->_expand_field_mask( $values_rs, $mask_element_rs[$i]['else_mask'], $mask_element_rs[$i]['else_mask_elements'], $config_var_rs );
						else
							$value = NULL;
						break;
					
					case 'if' : // if(varname[<|<=|>=|>|==|!=]value, "if_mask"[, "else_mask"])
						$value = $values_rs[$mask_element_rs[$i]['varname']];
						
						// The attribute is defined, so now lets do the comparison.
						if (! empty ( $value )) {
							if ($this->_test_if_condition( $value, $mask_element_rs[$i]['op'], $mask_element_rs[$i]['value'] ))
								$value = $this->_expand_field_mask( $values_rs, $mask_element_rs[$i]['if_mask'], $mask_element_rs[$i]['if_mask_elements'], $config_var_rs );
							else if (strlen ( $mask_element_rs[$i]['else_mask'] ?? '' ) > 0)
								$value = $this->_expand_field_mask ( $values_rs, $mask_element_rs[$i]['else_mask'], $mask_element_rs[$i]['else_mask_elements'], $config_var_rs );
							else {
								$value = NULL;
							}
						} else {
							$value = NULL;
						}
						break;
					
					case 'switch' :
						$value = $values_rs[$mask_element_rs[$i]['varname']];
						
						// The attribute is defined, so now lets do the comparison.
						if (! empty ( $value )) {
							if (is_not_empty_array ( $mask_element_rs [$i] ['cases'] )) {
								for($j = 0; $j < count ( $mask_element_rs [$i] ['cases'] ); $j ++) {
									// if a match.
									if (strcmp ( $value, $mask_element_rs [$i] ['cases'] [$j] ['case'] ) === 0) {
										$value = $this->_expand_field_mask ( $values_rs, $mask_element_rs [$i] ['cases'] [$j] ['result'] ['mask'], $mask_element_rs [$i] ['cases'] [$j] ['result'] ['mask_elements'], $config_var_rs );
										break 2; // break out of switch
									}
								}
							}
							
							if (is_not_empty_array ( $mask_element_rs [$i] ['default_case'] )) {
								$value = $this->_expand_field_mask ( $values_rs, $mask_element_rs [$i] ['default_case'] ['mask'], $mask_element_rs [$i] ['cases'] ['default_case'] ['mask_elements'], $config_var_rs );
							}
						} else {
							$value = NULL;
						}
						break;
					
					case 'config_var_key' : // config_var_key(name, value)
						if (is_not_empty_array ( $config_var_rs ) && is_array ( $config_var_rs [$mask_element_rs [$i] ['name']] )) {
							if (is_array ( $mask_element_rs [$i] ['value'] ))
								$srchValue = $this->_expand_field_mask ( $values_rs, $mask_element_rs [$i] ['value'] ['mask'], $mask_element_rs [$i] ['value'] ['elements'], $config_var_rs );
							else
								$srchValue = $mask_element_rs [$i] ['value'];
							
							$tmpValue = array_search2 ( $srchValue, $config_var_rs [$mask_element_rs [$i] ['name']] );
							if ($tmpValue !== FALSE)
								$value = $tmpValue;
							else
								$value = '';
						} else {
							$value = '';
						}
						break;
					
					case 'config_var_value' : // config_var_value(name, keyid)
						if (is_not_empty_array ( $config_var_rs ) && is_array ( $config_var_rs [$mask_element_rs [$i] ['name']] )) {
							if (is_array ( $mask_element_rs [$i] ['key'] ))
								$srchKey = $this->_expand_field_mask ( $values_rs, $mask_element_rs [$i] ['key'] ['mask'], $mask_element_rs [$i] ['key'] ['elements'], $config_var_rs );
							else
								$srchKey = $mask_element_rs [$i] ['key'];
							
							if (isset ( $config_var_rs [$mask_element_rs [$i] ['name']] [$srchKey] ))
								$value = $config_var_rs [$mask_element_rs [$i] ['name']] [$srchKey];
							else
								$value = '';
						} else {
							$value = '';
						}
						break;
					
					default : // No valid function specified, so set to empty.
						$value = '';
				}
			} else 			// standard variable (title, instance_no or item_attribute)
{
				// in the case of this function, all {variables} are not actually s_attribute_type references, but
				// references to key's in the $values_rs array, thus we ignore the 'option' and assume 'value' in
				// every case.
				if (is_array ( $mask_element_rs [$i] ) && isset ( $mask_element_rs [$i] ['s_attribute_type'] ) && isset ( $mask_element_rs [$i] ['option'] )) {
					$value = ifempty ( $values_rs [$mask_element_rs [$i] ['s_attribute_type']], $values_rs [strtolower ( $mask_element_rs [$i] ['s_attribute_type'] )] );
				} else {
					$value = $values_rs [$mask_element_rs [$i]];
				}
			}
			
			// Replace the array index.
			$mask = str_replace ( '{' . $i . '}', $value, $mask );
		}
		
		// Now return expanded subject.
		return $mask;
	}

	/**
		return true if condition parsed successfully.  assigns varname,
		op and value array elements to pass-by-reference $func array.

			operators: >=, >, <=, <, ==, !=
	*/
	function _parse_if_condition($if_condition, &$func) {
		if (($pos = strpos ( $if_condition, '>=' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '>=';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 2 ) );
			return true;
		} else if (($pos = strpos ( $if_condition, '>' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '>';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 1 ) );
			return true;
		} else if (($pos = strpos ( $if_condition, '<=' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '<=';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 2 ) );
			return true;
		} else if (($pos = strpos ( $if_condition, '<' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '<';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 1 ) );
			return true;
		} else if (($pos = strpos ( $if_condition, '==' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '==';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 2 ) );
			return true;
		} else if (($pos = strpos ( $if_condition, '!=' )) !== false) {
			$func ['varname'] = trim ( substr ( $if_condition, 0, $pos ) );
			$func ['op'] = '!=';
			$func ['value'] = trim ( substr ( $if_condition, $pos + 2 ) );
			return true;
		} else
			return false;
	}

	/**
		does the comparison, with the same operators as
		parse_if_condition.
	*/
	function _test_if_condition($attribute_val, $op, $value) {
		if (strlen ( $attribute_val ) > 0 && strlen ( $value ) > 0) {
			switch ($op) {
				case '>=' :
					return $attribute_val >= $value;
				case '>' :
					return $attribute_val > $value;
				case '<=' :
					return $attribute_val <= $value;
				case '<' :
					return $attribute_val < $value;
				case '==' :
					return $attribute_val == $value;
				case '!=' :
					return $attribute_val != $value;
			}
		}
	}

	/**
		return a database value for $item_id based on whether $mask_variable
	    points to title or to an item_attribute.  for item_attribute references
		there are some further options.

		a legal mask item can include the following:
			{s_attribute_type}
			{s_attribute_type.img}
			{s_attribute_type.value}  // the .value will result in the same as if no .option was specified.
			{s_attribute_type.display}
			{title}
			{s_item_type}
			{item_id}
			{instance_no}
			{s_status_type}
	*/
	function _get_mask_variable_value($mask_variable, $item_instance_r) {
		$value = '';
		
		if (is_array ( $mask_variable ) && isset ( $mask_variable ['s_attribute_type'] ) && isset ( $mask_variable ['option'] )) {
			$value = NULL;
			
			// the options that require a item_id context
			if (is_numeric ( $item_instance_r ['item_id'] ?? "")) {
				if ($mask_variable ['option'] == 'img') {
					$lookup_attr_r = fetch_attribute_type_lookup_r ( $mask_variable ['s_attribute_type'], fetch_attribute_val ( $item_instance_r ['item_id'], $item_instance_r ['instance_no'], $mask_variable ['s_attribute_type'] ) );
					if ($lookup_attr_r !== false) {
						if (strlen ( $lookup_attr_r ['img'] ) > 0 && $lookup_attr_r ['img'] != 'none')
							$value = theme_image ( $lookup_attr_r ['img'], $lookup_attr_r ['display'] );
						else // if no image, then use (value).
							$value = '(' . $lookup_attr_r ['value'] . ')';
					}
				} else if ($mask_variable ['option'] == 'display' && is_numeric ( $item_instance_r ['item_id'] )) {
					$value = fetch_attribute_type_lookup_r ( $mask_variable ['s_attribute_type'], fetch_attribute_val ( $item_instance_r ['item_id'], $item_instance_r ['instance_no'], $mask_variable ['s_attribute_type'] ), 'display' );
				} else if ($mask_variable ['option'] == 'display_type' && is_numeric ( $item_instance_r ['item_id'] )) {
					//$value = get_display_field($mask_variable['s_attribute_type'], $mask_variable['prompt'], $mask_variable['display_type'], fetch_attribute_val($item_instance_r['item_id'], $item_instance_r['instance_no'], $mask_variable['s_attribute_type']), FALSE);
					$value = get_item_display_field ( $item_instance_r, $mask_variable, fetch_attribute_val ( $item_instance_r ['item_id'], $item_instance_r ['instance_no'], $mask_variable ['s_attribute_type'] ), FALSE );
				}
				
				// as a last resort.
				if (strlen ( $value ) == 0) {
					$value = fetch_attribute_val ( $item_instance_r ['item_id'], $item_instance_r ['instance_no'], $mask_variable ['s_attribute_type'] );
				}
			} else if (strlen ( $item_instance_r ['s_item_type'] ?? "" ) > 0) 			// s_item_type context items.
{
				if ($mask_variable ['option'] == 'prompt') 				// the s_attribute_type prompt
{
					$value = fetch_s_item_type_attr_prompt ( $item_instance_r ['s_item_type'], $mask_variable ['s_attribute_type'] );
				}
			}
			return $value;
		} 		// special variable references.
		else if (! is_array ( $mask_variable ) && $mask_variable == 'title')
			return $item_instance_r['title'] ?? "";
		else if (! is_array ( $mask_variable ) && $mask_variable == 's_item_type')
			return $item_instance_r['s_item_type'] ?? "";
		else if (! is_array ( $mask_variable ) && $mask_variable == 'item_id')
			return $item_instance_r['item_id'] ?? "";
		else if (! is_array ( $mask_variable ) && $mask_variable == 'instance_no')
			return $item_instance_r['instance_no'] ?? "";
		else if (! is_array ( $mask_variable ) && $mask_variable == 's_status_type')
			return $item_instance_r['s_status_type'] ?? "";
		else 		// plain text
{
			return $mask_variable;
		}
	}

	/**
	    return reference to $_title_mask_rs pk which is s_item_type_group and s_item_type
	*/
	function _get_title_mask_idx($s_item_type_group, $s_item_type) {
		if ($s_item_type !== NULL) {
			$index = $this->_find_title_mask_idx ( NULL, $s_item_type );
			if ($index != - 1) {
				return $index;
			}
		}
		
		if ($s_item_type_group !== NULL) {
			$index = $this->_find_title_mask_idx ( $s_item_type_group, NULL );
			if ($index != - 1) {
				return $index;
			}
		}
		
		if (isset ( $this->_cache_s_item_type_group_map [$s_item_type] )) {
			// already identified that s_item_type has no specific configuration, so grab default.
			if ($this->_cache_s_item_type_group_map [$s_item_type] == '*') {
				$index = $this->_find_title_mask_idx ( '*', '*' );
				if ($index != - 1) {
					$this->_cache_s_item_type_group_map [$s_item_type] = '*';
					return $index;
				}
			} else {
				$index = $this->_find_title_mask_idx ( $this->_cache_s_item_type_group_map [$s_item_type], NULL );
				if ($index != - 1) {
					return $index;
				}
			}
		} else {
			$item_type_group_r = fetch_item_type_groups_for_item_type_r ( $s_item_type, 'Y' );
			if (is_array ( $item_type_group_r )) {
				reset ( $item_type_group_r );
				foreach ( $item_type_group_r as $group ) {
					$index = $this->_find_title_mask_idx ( $group, NULL );
					if ($index != - 1) {
						// cache mapping
						$this->_cache_s_item_type_group_map [$s_item_type] = $group;
						
						return $index;
					}
				}
			}
		}
		
		// default
		$index = $this->_find_title_mask_idx ( '*', '*' );
		if ($index != - 1) {
			$this->_cache_s_item_type_group_map [$s_item_type] = '*';
			
			return $index;
		} else {
			// what the fuck!
			return - 1;
		}
	}

	/**
	    return index of array element
	    
	    @param $s_item_type_group if NULL, ignore it, and just search for $s_item_type
	    @param $s_item_type if NULL, ignore it, and just search for $s_item_type_group
	*/
	function _find_title_mask_idx($s_item_type_group, $s_item_type) {
		for($i = 0; $i < count ( $this->_title_mask_rs ); $i ++) {
			if (($s_item_type_group == NULL || $this->_title_mask_rs [$i] ['s_item_type_group'] == $s_item_type_group) && ($s_item_type == NULL || $this->_title_mask_rs [$i] ['s_item_type'] == $s_item_type)) {
				return $i;
			}
		}
		
		//else
		return - 1;
	}
}
?>
