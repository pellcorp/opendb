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
include_once("./lib/TitleMask.class.php");

class Listing {
	// Local reference to HTTP var array.
	var $_http_vars;
	var $_php_self;
	var $_items_per_page = NULL;
	var $_total_items = NULL;
	var $_page_no = NULL;
	var $_start_index = NULL;
	var $_is_checkbox_columns = FALSE;
	var $_no_rows_message = NULL;
	
	// A list of all help entries
	var $_help_entry_rs = NULL;
	var $_help_entry_added_r = NULL;
	var $_header_column_rs = array ();
	var $_header_row_written = FALSE;
	var $_start_row = 0;
	
	// this is a temporary per row array
	var $_row_column_rs = NULL;
	var $_show_item_images = FALSE;
	var $_toggle = TRUE; // toggle row info
	var $_row = 0;
	
	// a cache for writeItemTypeImageColumn(...) method.
	var $_item_type_rs = array ();
	
	// TitleMask class reference
	var $_titleMaskCfg;

	function __construct($PHP_SELF, $HTTP_VARS) {
		$this->_php_self = $PHP_SELF;
		$this->_http_vars = $HTTP_VARS;
		
		if (isset ( $HTTP_VARS ['items_per_page'] )) {
			$this->_items_per_page = $HTTP_VARS ['items_per_page'];
		} else {
			$this->_items_per_page = get_opendb_config_var ( 'listings', 'items_per_page' );
		}
		
		// initialise these, as they will most likely NOT be initialised via setTotalItems
		if (! is_numeric ( $this->_items_per_page )) {
			$this->_page_no = 1;
			$this->_start_index = NULL;
		}
		
		$this->_current_orderby = $this->_http_vars ['order_by'] ?? '';
		$this->_current_sortorder = $this->_http_vars ['sortorder'] ?? '';
		
		// initialise to default.
		$this->_no_rows_message = get_opendb_lang_var ( 'no_matches_found' );
		
		$this->_titleMaskCfg = new TitleMask ( 'item_listing' );
	}

	function setShowItemImages($b) {
		$this->_show_item_images = $b;
	}

	function setTotalItems($total_items) {
		$this->_total_items = $total_items;
		
		if (is_numeric( $this->_total_items ) && $this->_total_items > 0) {
			if (is_numeric( $this->_items_per_page ) && $this->_items_per_page > 0) {
				if (is_numeric( $this->_http_vars['page_no'] ?? '' )) {
					$this->_page_no = $this->_http_vars['page_no'];
					
					// We need to ensure that the $page_no is realistic for the number of items.
					$this->_start_index = ($this->_page_no - 1) * $this->_items_per_page;
					if ($this->_start_index >= $this->_total_items) {
						$this->_page_no = floor ( $this->_total_items / $this->_items_per_page );
						if ($this->_page_no > 0) {
							$this->_start_index = ($this->_page_no - 1) * $this->_items_per_page;
						} else {
							$this->_page_no = 1;
							$this->_start_index = 0;
						}
					}
				} else {
					$this->_page_no = 1;
					$this->_start_index = 0;
				}
			}
		}
	}

	function isCheckboxColumns() {
		return $this->_is_checkbox_columns;
	}

	/**
	* Returns $HTTP_VARS with any variables used for Listings functionality unset.
	*/
	function getHttpVars() {
		return $this->_http_vars;
	}
	
	/*
	* Message to display if No Rows are found.  The default message is get_opendb_lang_var('no_matches_found')
	*/
	function setNoRowsMessage($message) {
		$this->_no_rows_message = $message;
	}

	function getCurrentOrderBy() {
		return $this->_current_orderby;
	}

	function getCurrentSortOrder() {
		return $this->_current_sortorder;
	}

	/**
	* Make sure this has been called first:
	* 	setTotalItems($total_items)
	*/
	function getStartIndex() {
		return $this->_start_index;
	}

	/**
	* Make sure this has been called first:
	* 	setTotalItems($total_items)
	*/
	function getPageNo() {
		return $this->_page_no;
	}

	/**
	* Make sure this has been called first:
	* 	setTotalItems($total_items)
	*/
	function getTotalItemCount() {
		return $this->_total_items;
	}

	function getItemsPerPage() {
		return $this->_items_per_page;
	}

	function getNoOfColumns() {
		if (is_array ( $this->_header_column_rs ))
			return count ( $this->_header_column_rs );
		else
			return 0;
	}

	function getNoOfRowColumns() {
		if (is_array ( $this->_row_column_rs ))
			return count ( $this->_row_column_rs );
		else
			return 0;
	}

	/**
	* Total rows processed (not including header) as of this call.
	*/
	function getRowCount() {
		return $this->_row;
	}

	function getHelpEntries() {
		return $this->_help_entry_rs;
	}

	/**
		Supports passing in an array of help entries, which will all be added individually
	*/
	function addHelpEntry($help_text, $img = NULL, $id = NULL) {
		if (is_array ( $help_text )) {
			reset ( $help_text );
			foreach ( $help_text as $help ) {
				$this->_addHelpEntry ( $help, $img, $id );
			}
		} else {
			$this->_addHelpEntry ( $help_text, $img, $id );
		}
	}

	function _addHelpEntry($help_text, $img = NULL, $id = NULL) {
		if ($id === NULL || ! is_array ( $this->_help_entry_added_r ) || in_array ( $id, $this->_help_entry_added_r ) !== TRUE) {
			$this->_help_entry_rs [] = array (
					'img' => $img,
					'text' => $help_text,
					'type' => $id );
			$this->_help_entry_added_r [] = $id;
		}
	}

	/**
	 * @$fieldname should always be provided as it provides the header element id, which can be used to style the
	 * width of the column, etc.  The $sortColumn parameter should be used to control whether the column is sortable
	 * or not.
	 *
	 * @param unknown_type $title
	 * @param unknown_type $fieldname
	 * @param unknown_type $sortColumn
	 */
	function addHeaderColumn($title, $fieldname = NULL, $sortColumn = TRUE, $type = NULL) {
		if ($fieldname == NULL) {
			$sortColumn = FALSE;
		}
		
		$header_column_r = array (
				'title' => $title,
				'fieldname' => $fieldname,
				'sortcolumn' => $sortColumn,
				'type' => $type );
		
		if ($fieldname == 'title') {
			if ( is_user_granted_permission ( PERM_VIEW_ITEM_COVERS ) &&
                 ( $this->_show_item_images === TRUE ||
                   ( ( get_opendb_config_var( 'listings', 'allow_override_show_item_image' ) === FALSE &&
                       get_opendb_config_var( 'listings', 'show_item_image' ) !== FALSE) ||
                     get_opendb_config_var( 'listings', 'allow_override_show_item_image' ) !== FALSE &&
                     ( ( get_opendb_config_var( 'listings', 'show_item_image' ) !== FALSE &&
                         strlen( $this->_http_vars['show_item_image'] ?? '') == 0) ||
                       $this->_http_vars['show_item_image'] === 'Y')))) {
				$header_column_r ['cover_image_support'] = TRUE;
				
				$this->_header_column_rs [] = array (
						//title=>$title,
						'fieldname' => 'coverimage',
						'sortcolumn' => FALSE );
			}
		}
		
		$this->_header_column_rs [] = $header_column_r;
	}

	function findHeaderColumnByFieldname($fieldname) {
		if (is_array ( $this->_header_column_rs )) {
			for($i = 0; $i < count ( $this->_header_column_rs ); $i ++) {
				if ($this->_header_column_rs [$i] ['fieldname'] == $fieldname) {
					return $this->_header_column_rs [$i];
				}
			}
		}
		return array ();
	}

	/**
	 * Will render a checkbox, but no other smart logic included
	 */
	function addCheckboxColumn($value, $isChecked = FALSE) {
		$this->_row_column_rs [] = array (
				'column_type' => 'checkbox',
				'value' => $value,
				'checked' => $isChecked );
		
		$this->_is_checkbox_columns = TRUE;
	}

	function addUserNameColumn($user_id, $extra_http_vars = NULL) {
		$this->_row_column_rs [] = array (
				'column_type' => 'username',
				'user_id' => $user_id,
				'fullname' => fetch_user_name ( $user_id ),
				'extra_http_vars' => $extra_http_vars );
	}

	function addInterestColumn($item_id, $instance_no, $user_id, $level, $extra_http_vars = NULL) {
		$this->_row_column_rs [] = array (
				'column_type' => 'interest',
				'item_id' => $item_id,
				'instance_no' => $instance_no,
				'user_id' => $user_id,
				'level' => $level,
				'extra_http_vars' => $extra_http_vars );
	}

	function addThemeImageColumn($src, $alt = NULL, $title = NULL, $type = NULL) {
		$this->_row_column_rs [] = array (
				'column_type' => 'theme_image',
				'src' => $src,
				'alt' => $alt,
				'title' => $title,
				'type' => $type );
	}

	function addItemTypeImageColumn($s_item_type) {
		$this->_row_column_rs [] = array (
				'column_type' => 'item_type_image',
				's_item_type' => $s_item_type );
	}

	/**
	* @param $item_r
	*/
	function addTitleColumn($item_r) {
		$s_item_type = $item_r ['s_item_type'];
		
		$is_item_reviewed = FALSE;
		if (is_item_reviewed ( $item_r ['item_id'] )) {
			$is_item_reviewed = TRUE;
		}
		
		$is_borrowed_or_returned = FALSE;
		if (is_item_borrowed_or_returned_by_user ( $item_r ['item_id'], $item_r ['instance_no'], get_opendb_session_var ( 'user_id' ) )) {
			$is_borrowed_or_returned = TRUE;
		}
		
		$item_cover_image = FALSE;
		
		$header_column_r = $this->findHeaderColumnByFieldname ( 'title' );
		if ($header_column_r ['cover_image_support'] === TRUE) {
			$item_cover_image = NULL;
			
			if (strlen ( $this->_item_type_rs [$s_item_type] ['image_attribute_type'] ) === 0) {
				$this->_item_type_rs [$s_item_type] ['image_attribute_type_r'] = fetch_sfieldtype_item_attribute_type_r ( $s_item_type, 'IMAGE' );
			}
			
			if (is_array ( $this->_item_type_rs [$s_item_type] ['image_attribute_type_r'] )) {
				$attribute_type_r = $this->_item_type_rs [$s_item_type] ['image_attribute_type_r'];
				
				$item_cover_image = fetch_attribute_val ( $item_r ['item_id'], $item_r ['instance_no'], $attribute_type_r ['s_attribute_type'] );
				
				// a kludge to use FALSE to test whether a default image should be displayed				
				if ($item_cover_image === FALSE)
					$item_cover_image = NULL;
			}
		}
		
		$item_r ['title'] = $this->_titleMaskCfg->expand_item_title ( $item_r );
		
		$title_href_link = 'item_display.php?item_id=' . $item_r ['item_id'] . '&instance_no=' . $item_r ['instance_no'];
		
		if ($item_cover_image !== FALSE) {
			$this->_row_column_rs [] = array (
					'column_type' => 'coverimage',
					'title_href_link' => $title_href_link,
					'item_cover_image' => $item_cover_image );
		}
		
		$this->_row_column_rs [] = array (
				'column_type' => 'title',
				'item_title' => $item_r ['title'],
				'title_href_link' => $title_href_link,
				'is_item_reviewed' => $is_item_reviewed,
				'is_borrowed_or_returned' => $is_borrowed_or_returned );
	}

	function addActionColumn($action_links_rs) {
		$this->_row_column_rs [] = array (
				'column_type' => 'action_links',
				'action_links' => $action_links_rs );
	}

	function addDisplayColumn($s_attribute_type, $prompt, $display_type, $value) {
		$this->_row_column_rs [] = array (
				'column_type' => 'display',
				'attribute_type' => $s_attribute_type,
				'prompt' => $prompt,
				'display_type' => $display_type,
				'value' => $value );
	}

	/**
	*/
	function addAttrDisplayColumn($item_r, $attribute_type_r, $value) {
		$this->_row_column_rs [] = array (
				'column_type' => 'attribute_display',
				'item_r' => $item_r,
				'attribute_type_r' => $attribute_type_r,
				'value' => $value );
	}

	function addColumn($value = NULL) {
		$this->_row_column_rs [] = array (
				'column_type' => 'default',
				'value' => $value );
	}

	function startRow() {
		// last row has not been written
		if ($this->_start_row > $this->_row) {
			$this->_endRow ();
		}
		
		if ($this->getRowCount () == 0 && is_array ( $this->_header_column_rs )) {
			if (! $this->_header_row_written && method_exists ( $this, 'writeHeaderRowImpl' )) {
				$this->writeHeaderRowImpl ( $this->_header_column_rs );
				$this->_header_row_written = TRUE;
			}
		}
		$this->_row_column_rs = NULL;
		
		// indicates startRow has been called
		$this->_start_row ++;
	}

	function endRow() {
		// last row has not been written
		if ($this->_start_row > $this->_row) {
			$this->_endRow ();
		}
	}

	function _endRow() {
		if (method_exists ( $this, 'writeRowImpl' )) {
			if (is_array ( $this->_row_column_rs )) {
				$this->writeRowImpl ( $this->_row_column_rs );
				
				// toggle row class
				$this->_toggle = ! $this->_toggle;
			}
		}
		
		$this->_row ++;
	}
	
	/*
	* Do not call this function until the following methods have been called:
	* 
	* These only if you want to do paging,
	*	setTotalItems($total_items)	
	*/
	function startListing($listing_title = NULL) {
		// A null Items Per Page means we will be generating the entire listing, no paging.
		if (! is_numeric ( $this->getItemsPerPage () )) {
			// This may take a lot more time to generate
			@set_time_limit ( 600 );
		}
		
		// now process the $this->_header_column_rs
		if (method_exists ( $this, 'startListingImpl' )) {
			$this->startListingImpl ( $listing_title );
		}
	}

	function endListing() {
		// last row has not been written
		if ($this->_start_row > $this->_row) {
			$this->_endRow ();
		}
		
		// in the case where no items were encountered.
		if ($this->getRowCount () == 0 && is_array ( $this->_header_column_rs )) {
			if (! $this->_header_row_written && method_exists ( $this, 'writeHeaderRowImpl' )) {
				$this->writeHeaderRowImpl ( $this->_header_column_rs );
				$this->_header_row_written = TRUE;
			}
		}
		
		if ($this->getRowCount () > 0) {
			// The initial values may not have been set properly, so lets double check them.		
			if (! is_numeric ( $this->_total_items ) || $this->_total_items < $this->getRowCount ()) {
				$this->_total_items = $this->getRowCount ();
			}
			
			if (! is_numeric ( $this->_items_per_page ) || $this->_items_per_page <= 0) {
				$this->_page_no = 1;
				$first_item = 1;
				$last_item = $this->_total_items;
			} else {
				$vItemsPerPage = $this->_items_per_page;
				
				$first_item = (($this->_page_no - 1) * $vItemsPerPage) + 1;
				
				// Do some sanity checking!
				if ($first_item > $this->_total_items) {
					$this->_page_no = 1;
					$first_item = 1;
				}
				
				$last_item = $this->_page_no * $vItemsPerPage;
				
				// Do some sanity checking!
				if ($last_item > $this->_total_items) {
					$last_item = $this->_total_items;
				}
				
				if ($first_item > 1 || $last_item < $this->_total_items) 				// Only if more than one page.
{
					$total_pages = ceil ( $this->_total_items / $vItemsPerPage );
					if ($total_pages <= 10) {
						$start_page = 1;
						$end_page = $total_pages;
					} else if ($this->_page_no <= 5) {
						$start_page = 1;
						$end_page = 10;
					} else if (($this->_page_no + 5) >= $total_pages) {
						$end_page = $total_pages;
						$start_page = $total_pages - 10;
					} else 					// $page_no>=5 && $total_pages < ($page_no+5)
{
						$start_page = $this->_page_no - 5;
						$end_page = $this->_page_no + 5;
					}
				}
			}
			
			$title_mask_elements = $this->_titleMaskCfg->title_mask_macro_element_r ( 'theme_img' );
			if (is_array ( $title_mask_elements )) {
				foreach ($title_mask_elements as $img => $prompt ) {
					$this->addHelpEntry ( $prompt, $img, 'title_mask' );
				}
			}
			
			if (method_exists ( $this, 'endListingImpl' )) {
				$this->endListingImpl ( $first_item, $last_item, $start_page, $end_page, $total_pages );
			}
		} else {
			if (method_exists ( $this, 'endListingImpl' )) {
				$this->endListingImpl ();
			}
		}
	}
}
?>
