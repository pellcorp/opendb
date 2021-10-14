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
include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/utils.php");
include_once("./lib/site_plugin.php");
include_once("./lib/item_type.php");
include_once("./lib/OpenDbSnoopy.class.php");
include_once("./lib/TitleMask.class.php");

define ( 'HTML_CONTENT_IS_LEGAL', 1 );

class SitePlugin {
	var $_type;
	var $_site_plugin_r;
	var $_site_plugin_conf_r;
	var $_is_next_page = FALSE;
	var $_is_previous_page = FALSE;
	var $_search_query = NULL;
	var $_items_per_page = 25; // default
	var $_page_no = 0;
	var $_total_count = 0;
	var $_more_info_url = NULL;
	var $_item_list_rs;
	var $_item_data_r;
	
	// stores the errors encountered.
	var $_errors;
	
	// this will be temporarily assigned a reference to the
	// $HTTP_VARS array passed into the _querySite function.
	var $_http_vars;
	var $_httpClient;
	var $_titleMaskCfg;

	function __construct($site_type) {
		global $SITE_PLUGIN_SNOOPY;
		
		$this->_type = $site_type;
		$this->_site_plugin_r = fetch_site_plugin_r ( $this->_type );
		$this->_site_plugin_conf_r = get_site_plugin_conf_r ( $this->_type );
		
		// for simplicity sake we want an array always, even if empty.
		if (! is_array ( $this->_site_plugin_conf_r ))
			$this->_site_plugin_conf_r = array ();
		
		if (is_numeric ( $this->_site_plugin_r ['items_per_page'] ))
			$this->_items_per_page = $this->_site_plugin_r ['items_per_page'];
		else
			$this->_items_per_page = 25;
			
			// parse this URL now once
		if (strlen ( $this->_site_plugin_r ['more_info_url'] ) > 0) {
			$this->_more_info_url = $this->_site_plugin_r ['more_info_url'];
		}
		
		// Construct a single copy of this object for use within the site plugin
		$this->_httpClient = & $SITE_PLUGIN_SNOOPY; //debugging always on
		

		$this->_titleMaskCfg = new TitleMask ();
	}

	function getType() {
		return $this->_type;
	}

	function getTitle() {
		return $this->_site_plugin_r ['title'];
	}

	function getImage() {
		return $this->_site_plugin_r ['image'];
	}

	function getDescription() {
		return $this->_site_plugin_r ['description'];
	}

	function getExternalUrl() {
		return $this->_site_plugin_r ['external_url'];
	}

	function getMoreInfoUrl() {
		return $this->_site_plugin_r ['more_info_url'];
	}

	function getItemsPerPage() {
		return $this->_site_plugin_r ['items_per_page'];
	}

	function getRowCount() {
		if (is_array ( $this->_item_list_rs ))
			return count ( $this->_item_list_rs );
		else
			return 0;
	}

	function getConfigValue($name, $key = NULL) {
		if (isset ( $this->_site_plugin_conf_r [$name] )) {
			if ($key != NULL)
				return $this->_site_plugin_conf_r [$name] [$key];
			else
				return $this->_site_plugin_conf_r [$name];
		} else {
			return FALSE;
		}
	}

	/**
	* Return a single array each call, with the following format:
	* 	title
	* 	cover_image_url
	* 	opendb_link_url
	* 	more_info_url (if configured in the site_plugin_conf table)
	*/
	function getRowData($rownum) {
		if (is_array ( $this->_item_list_rs ) && $rownum < count ( $this->_item_list_rs ))
			return $this->_item_list_rs [$rownum];
		else
			return FALSE;
	}

	/**
	* @param $s_item_type - if defined, will expand the site plugin variables to
	* map to the attributes applicable for the specified s_item_type.
	*/
	function getItemData($s_item_type = NULL) {
		if (is_array ( $this->_item_data_r )) {
			if (strlen ( $s_item_type ) > 0) {
				return get_expanded_and_mapped_site_plugin_item_variables_r ( $this->_type, $s_item_type, $this->_item_data_r );
			} else {
				return $this->_item_data_r;
			}
		} else {
			return array (); //no sense worrying about non-array errors here.
		}
	}

	function isNextPage() {
		return $this->_is_next_page;
	}

	function isPreviousPage() {
		return $this->_is_previous_page;
	}

	function getSearchQuery() {
		return $this->_search_query;
	}

	function getPageNo() {
		return $this->_page_no;
	}

	/**
	* No need for a setPreviousPage link, as we can derive that from
	* the page_no.
	*/
	function setNextPage($b) {
		if (is_bool ( $b ))
			$this->_is_next_page = $b;
		else
			$this->_is_next_page = FALSE;
	}

	/**
	* Can be used for deriving whether should be next page or not
	*/
	function setTotalCount($count) {
		$this->_total_count = $count;
	}

	function setError($error, $details = NULL) {
		if (strlen ( $error ) > 0) {
			$this->_errors [] = array (
					'error' => $error,
					'detail' => $details );
		}
	}

	function getErrors() {
		if (is_not_empty_array ( $this->_errors ))
			return $this->_errors;
		else
			return FALSE;
	}

	/**
	* It is this functions responsibility to encode the listing row URL
	* to include any context information.
	*/
	function addListingRow($title, $cover_image_url, $comments, $attributes_r) {
		$title = trim ( strip_tags ( $title ) );
		$comments = trim ( strip_tags ( $comments ) );
		$cover_image_url = trim ( strip_tags ( $cover_image_url ) );
		
		if (is_array ( $attributes_r )) {
			// lets make sure we don't already have a row with the same $attributes_r set.
			if (is_array ( $this->_item_list_rs )) {
				for($i = 0; $i < count ( $this->_item_list_rs ); $i ++) {
					if (is_array ( $this->_item_list_rs ['attributes'] )) {
						$found = TRUE;
						reset ( $attributes_r );
						foreach ($attributes_r as $key => $value) {
							// if not set, this is considered no match and do next for loop cycle
							if (! isset ( $this->_item_list_rs ['attributes'] [$key] ) || $this->_item_list_rs ['attributes'] [$key] != $key) {
								$found = FALSE;
								break;
							}
						}
						
						if ($found) {
							return FALSE;
						}
					}
				}
			}
			
			if (strlen ( $this->_more_info_url ) > 0) {
				$more_info_url = $this->_titleMaskCfg->expand_mask ( $attributes_r, $this->_more_info_url );
			}
			
			$opendb_link_url = get_url_string ( $this->_http_vars, $attributes_r );
			
			$this->_item_list_rs [] = array (
					'title' => $title,
					'cover_image_url' => trim ( $cover_image_url ),
					'comments' => trim ( strip_tags ( str_replace ( '<br>', "\n", $comments ) ) ),
					'more_info_url' => trim ( $more_info_url ),
					'opendb_link_url' => trim ( $opendb_link_url ),
					'attributes' => $attributes_r );
		}
		// else ignore
		

		return TRUE;
	}

	function isItemAttributeSet($attribute) {
		if (is_array ( $this->_item_data_r ) && isset ( $this->_item_data_r [$attribute] ))
			return TRUE;
		else
			return FALSE;
	}

	function getItemAttribute($attribute) {
		if (is_array ( $this->_item_data_r ) && isset ( $this->_item_data_r [$attribute] ))
			return $this->_item_data_r [$attribute];
		else
			return FALSE;
	}

	function replaceItemAttribute($attribute, $value) {
		if (is_array ( $this->_item_data_r )) {
			// remove attribute
			$this->_item_data_r [$attribute] = NULL;
		}
		
		$this->addItemAttribute ( $attribute, $value );
	}
	
	/*
	* If a value already exists for $attribute
	*/
	function addItemAttribute($attribute, $value, $options = NULL, $keyname = NULL) {
		// where value is array, recursively call the addItemAttribute function,
		// so rest of function can assume string $value.
		if (is_array ( $value )) {
			foreach ( $value as $key => $val ) {
				$this->addItemAttribute ( $attribute, $val, $options, $key );
			}
		} else {
			// site plugins cannot normally pass any HTML entities or tags through.
			if ($options != HTML_CONTENT_IS_LEGAL) {
				$value = html_entity_decode ( strip_tags ( $value ), ENT_COMPAT, get_opendb_config_var ( 'themes', 'charset' ) == 'utf-8' ? 'UTF-8' : 'ISO-8859-1' ); //thawn: fix utf-8 issues
			}
			// hack: remove hard spaces
			$replace = array (
					utf8_encode ( chr ( 160 ) ) => ' ' ); //thawn: fixes issue with 3param version of strtr() not being utf-8 compatible.
			$value = strtr ( $value, $replace );
			
			$value = trim ( $value );
			
			if (strlen ( $value ) > 0) {
				if (! is_array ( $this->_item_data_r ))
					$this->_item_data_r = array ();
				
				if (isset ( $this->_item_data_r [$attribute] )) {
					if (! is_array ( $this->_item_data_r [$attribute] )) {
						// do not add duplicates
						if ($this->_item_data_r [$attribute] != $value) {
							$tmpvalue = $this->_item_data_r [$attribute];
							
							$this->_item_data_r [$attribute] = array ();
							$this->_item_data_r [$attribute] [] = $tmpvalue;
							
							// add new value to array
							if (is_numeric ( $keyname ) || is_null ( $keyname )) {
								$this->_item_data_r [$attribute] [] = $value;
							} else {
								$this->_item_data_r [$attribute] [$keyname] = $value;
							}
						}
					} else {
						// do not add duplicates
						if (array_search2 ( $value, $this->_item_data_r [$attribute] ) === FALSE) {
							if (is_numeric ( $keyname ) || is_null ( $keyname )) {
								$this->_item_data_r [$attribute] [] = $value;
							} else {
								$this->_item_data_r [$attribute] [$keyname] = $value;
							}
						}
					}
				} else 	{ // otherwise single value only
					if (is_numeric ( $keyname ) || is_null ( $keyname )) {
						$this->_item_data_r [$attribute] = $value;
					} else {
						$this->_item_data_r [$attribute] = array (
								$keyname => $value );
					}
				}
			} //if(strlen($value)>0)
		}
	}
	
	/*
	* override in subclass
	*
	* @param $page_no
	* @param $items_per_page
	* @param $offset
	* @param $s_item_type			Provides $s_item_type so that site plugin can search
	* 								for correct kind of item.
	* @param $search_variables_r	Provides a list of all site plugin input field values.
	* @param $HTTP_VARS				This is all http variables
	*/
	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
		return FALSE;
	}
	
	/*
	* override in subclass
	*
	* Use addItemDataAttribute($attribute, $value)
	*
	* Return TRUE if item found, FALSE otherwise
	*
	* @param $attributes_r  - is set of all attributes required to uniquely identify
	* 						an item.  Does not include OpenDb specific information,
	* 						such as $s_item_type, $item_id, etc.
	*/
	function queryItem($search_attributes_r, $s_item_type) {
		return TRUE;
	}

	/**
	* @param $s_item_type - if specified, we assume that _queryItem is being
	* called internally, with s_item_type already extracted from the $HTTP_VARS
	* array.  In fact in this case, we assume that the $HTTP_VARS only contaisn
	* the $search_attributes_r.
	*/
	function _queryItem($HTTP_VARS, $s_item_type = NULL) {
		// reset errors
		$this->_errors = NULL;
		
		$this->_item_data_r = NULL;
		
		$search_attributes_r = $HTTP_VARS;
		
		if (strlen ( $s_item_type ) == 0) {
			$s_item_type = $HTTP_VARS ['s_item_type'];
			
			unset ( $search_attributes_r ['op'] );
			unset ( $search_attributes_r ['site_type'] );
			unset ( $search_attributes_r ['item_id'] );
			unset ( $search_attributes_r ['instance_no'] );
			unset ( $search_attributes_r ['parent_item_id'] );
			unset ( $search_attributes_r ['parent_instance_no'] );
			unset ( $search_attributes_r ['s_status_type'] );
			unset ( $search_attributes_r ['title'] );
			unset ( $search_attributes_r ['owner_id'] );
			unset ( $search_attributes_r ['s_item_type'] );
			unset ( $search_attributes_r ['inc_menu'] );
		}
		
		$return_val = $this->queryItem ( $search_attributes_r, $s_item_type );
		if ($return_val) {
			// save search vars as attributes now.
			if (is_array ( $search_attributes_r )) {
				unset ( $search_attributes_r ['search_title'] );
				
				reset ( $search_attributes_r );
				foreach ( $search_attributes_r as $key => $value ) {
					if ($this->getItemAttribute ( $key ) === FALSE) {
						$this->addItemAttribute ( $key, $value );
					}
				}
			}
			
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns a HTML div block with all the data elements currently set in this
	 * class.
	 *
	 * @param unknown_type $itemData
	 * @param unknown_type $buffer
	 * @return unknown
	 */
	function getDebugItemDataAsHtml($s_item_type = NULL) {
		$buffer = '<div class="sitePluginDebug">';
		$buffer .= '<h2>Debug HTTP Requests</h2>';
		$buffer .= $this->_httpClient->getDebugMessagesAsHtml ();
		
		$itemData = $this->getItemData ( $s_item_type );
		if (is_not_empty_array ( $itemData )) {
			$buffer .= '<h2>Debug ' . $this->getTitle () . ' Site Data</h2>';
			$buffer .= $this->__getDebugItemData ( $itemData );
		}
		$buffer .= "</div>";
		
		return $buffer;
	}

	function __getDebugItemData($itemData) {
		@reset ( $itemData );
		$buffer = "<ul>";
		foreach ( $itemData as $key => $value ) {
			$buffer .= "\n<li>";
			if (! is_numeric ( $key )) {
				$buffer .= "<strong>$key:</strong> ";
			}
			if (is_array ( $value )) {
				$buffer .= $this->__getDebugItemData ( $value );
			} else {
				$buffer .= nl2br ( $value );
			}
			$buffer .= "</li>";
		}
		
		$buffer .= "</ul>";
		return $buffer;
	}

	/**
	* need to work out page_no, items_per_page, offset, etc
	*
	* This method will set the $this->_is_next_page and $this->is_previous_page
	* flags.
	*/
	function _queryListing(&$HTTP_VARS) {
		// reset errors
		$this->_errors = NULL;
		
		$at_least_one_search_field_populated = FALSE;
		$input_field_values_r = array ();
		$this->_search_query = NULL;
		
		// need to get a list of all input field values to pass into the querySite call.
		$results = fetch_site_plugin_input_field_rs ( $this->_type );
		if ($results) {
			while ( $input_field_r = db_fetch_assoc ( $results ) ) {
				if (isset ( $HTTP_VARS [$input_field_r ['field']] ) && strlen ( $HTTP_VARS [$input_field_r ['field']] ) > 0) {
					$field_value = trim ( $HTTP_VARS [$input_field_r ['field']] );
					
					$at_least_one_search_field_populated = TRUE;
					
					if ($input_field_r ['field_type'] == 'scan-isbn' || $input_field_r ['field_type'] == 'scan-upc') {
						// Determine type of scanner.
						if (strrpos ( $HTTP_VARS [$input_field_r ['field']], '.' )) { // cuecat
							if ($input_field_r ['field_type'] == 'scan-isbn')
								$scanCode = get_cuecat_isbn_code ( $field_value );
							else
								$scanCode = get_cuecat_upc_code ( $field_value );
						} else { //non-cuecat or modified cuecat
							if ($input_field_r ['field_type'] == 'scan-isbn')
								$scanCode = get_isbn_code ( $field_value );
							else
								$scanCode = get_upc_code ( $field_value );
						}
						
						if ($scanCode !== FALSE)
							$field_value = $scanCode;
					}
					
					$input_field_values_r [$input_field_r ['field']] = $field_value;
					
					$this->_search_query [] = array (
							'field' => $input_field_r ['field'],
							'value' => $field_value,
							'prompt' => $input_field_r ['prompt'],
							'field_type' => $input_field_r ['field_type'] );
				}
			} //while
			db_free_result ( $results );
		}
		
		// only continue if at least one input field was populated with a value.
		if ($at_least_one_search_field_populated) {
			unset ( $this->_item_list_rs );
			
			// initialise if not set.
			if (! is_numeric ( $HTTP_VARS ['page_no'] ))
				$this->_page_no = 1;
			else
				$this->_page_no = $HTTP_VARS ['page_no'];
			
			if ($this->_page_no > 1)
				$this->_is_previous_page = TRUE;
			else
				$this->_is_previous_page = FALSE;
				
				// default, must be overriden by the individual plugins.
			$this->_is_next_page = FALSE;
			
			$offset = 0;
			if ($this->_items_per_page > 0 && $this->_page_no > 1) {
				$offset = ($this->_items_per_page * ($HTTP_VARS ['page_no'] - 1)) + 1;
			}
			
			$this->_total_count = 0;
			
			// if its not a legal type, don't pass it on
			if (! is_exists_item_type ( $HTTP_VARS ['s_item_type'] )) {
				unset ( $HTTP_VARS ['s_item_type'] );
			}
			
			$this->_http_vars = $HTTP_VARS;
			
			// do not want to pass page_no through
			$this->_http_vars ['page_no'] = NULL;
			
			// now at this point we need to grab the $items_per_page and
			// page_no values.
			if ($this->queryListing ( $this->_page_no, $this->_items_per_page, $offset, $HTTP_VARS ['s_item_type'], $input_field_values_r )) {
				// no need for this anymore
				$this->_http_vars = NULL;
				
				// if a single item returned, we will populate the itemData at this point too
				if ($this->getRowCount () == 1 && $this->isPreviousPage () === FALSE) {
					$rowData = $this->getRowData ( 0 );
					$this->_item_data_r = NULL;
					
					// call the queryItem function directly here.
					$return_val = $this->_queryItem ( $rowData ['attributes'], $HTTP_VARS ['s_item_type'] );
					if ($return_val)
						return TRUE;
					else
						return FALSE;
				} else {
					// in some cases, the plugin will not be able to provide this
					// information, but where it can, we can derive the is_next_page flag,
					// other plugins, would have to call setNextPage() function instead.
					if ($this->_items_per_page > 0 && $this->_total_count > ($offset + $this->_items_per_page)) {
						$this->_is_next_page = TRUE;
					}
					
					// expect the call to querySite to set the is_next_page flag
					return TRUE;
				}
			} else {
				// no need for this anymore
				$this->_http_vars = NULL;
				
				return FALSE;
			}
		} else {  // if($at_least_one_search_field_populated)
			// nothing found.
			return TRUE;
		}
	}

	/**
	* local stub to make it easier to access
	*/
	function fetchURI($uri, $utf8 = false) {
		$page = $this->_httpClient->fetchURI ( $uri );
		if ($page !== FALSE)
            if (get_opendb_config_var ( 'themes', 'charset' ) == 'utf-8') {
                return $utf8 === true ? $page : utf8_encode($page);
            } else {
                return $utf8 === true ? utf8_decode($page) : $page;
            }
		else
			$this->setError ( $this->_httpClient->error );
	}

	/**
	 Contents of the last page returned from fetchURI call.
	*/
	function getFetchedURIContent() {
		return $this->_httpClient->getContent ();
	}

	function getFetchedURILocation() {
		return $this->_httpClient->getLocation ();
	}
}
?>
