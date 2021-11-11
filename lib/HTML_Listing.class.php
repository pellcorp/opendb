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
include_once("./lib/listutils.php");
include_once("./lib/utils.php");
include_once("./lib/theme.php");
include_once("./lib/widgets.php");
include_once("./lib/Listing.class.php");

class HTML_Listing extends Listing {
	var $_include_listing_footer = TRUE;
	var $_include_href_links = TRUE;
	
	// by default the _write method automatically spools out straight out via echo()
	// command.  This setting can force it to be cached, and thus accessible via
	// the getContents() method.
	var $_buffer_output = FALSE;
	var $_buffer = NULL;
	
	// listing row CSS class tracking
	var $rowclass = 'oddRow';
	
	/*
	* @param $PHP_SELF
	* @param $HTTP_VARS
	*/
	function __construct($PHP_SELF, $HTTP_VARS) {
		parent::__construct( $PHP_SELF, $HTTP_VARS );
	}

	function setIncludeFooter($boolean) {
		$this->_include_listing_footer = $boolean;
	}

	function setIncludeHrefLinks($boolean) {
		$this->_include_href_links = $boolean;
	}

	function setBufferOutput($boolean) {
		$this->_buffer_output = $boolean;
	}

	function &getContents() {
		return $this->_buffer;
	}

	function writePageNavBlock($first_item, $last_item, $start_page, $end_page, $total_pages) {
		if ($first_item > 1 || $last_item < $this->_total_items) {// Only if more than one page.
			$this->_write ( "<ul class=\"listingPager\">" );
			
			if ($this->_page_no > 1)
				$this->_write ( "<li class=\"previousPage\"><a href=\"" . $this->_php_self . "?" . get_url_string ( $this->_http_vars, array (
						'page_no' => ($this->_page_no - 1) ) ) . "\">" . get_opendb_lang_var ( 'previous_page' ) . "</a>" );
			else
				$this->_write ( "<li class=\"previousPage disabled\">" . get_opendb_lang_var ( 'previous_page' ) );
			$this->_write ( "</li>" );
			
			// Check if we need to supply << arrows.
			if ($start_page > 1) {
				if ($start_page > 10)
					$page_no = $start_page - 10;
				else
					$page_no = 1;
				
				//				$this->_write("<li class=\"previousTenPages\"><a href=\"".$this->_php_self."?".get_url_string($this->_http_vars, array('page_no'=>$page_no))."\">&lt;&lt;</a></li>");
			}
			
			for($i = $start_page; $i <= $end_page; $i ++) {
				if ($i == $this->_page_no)
					$this->_write ( "<li class=\"currentPage\">$i</li>" );
				else
					$this->_write ( "<li><a href=\"" . $this->_php_self . "?" . get_url_string ( $this->_http_vars, array (
							'page_no' => $i ) ) . "\">" . $i . "</a></li>" );
			}
			
			// If more than 10 pages to end.
			//			if($end_page < $total_pages)
			//			{
			//				$this->_write("<li class=\"nextTenPages\"><a href=\"".$this->_php_self."?".get_url_string($this->_http_vars, array('page_no'=>($end_page+1)))."\">&gt;&gt;</a></li>");
			//			}
			

			if ($this->_page_no < $total_pages)
				$this->_write ( "<li class=\"nextPage\"><a href=\"" . $this->_php_self . "?" . get_url_string ( $this->_http_vars, array (
						'page_no' => ($this->_page_no + 1) ) ) . "\">" . get_opendb_lang_var ( 'next_page' ) . "</a></li>" );
			else
				$this->_write ( "<li class=\"nextPage disabled\">" . get_opendb_lang_var ( 'next_page' ) );
			$this->_write ( "</li>" );
			
			$this->_write ( "</ul>" );
		}
	}
	
	/*
	* Ensure the following methods have been called:
	*	setTotalItems($total_items)	
	*/
	function startListingImpl() {
	}
	
	/*
	* Ensure the following methods have been called:
	*	setTotalItems($total_items)
	* 	startListing()
	*/
	function endListingImpl($first_item = NULL, $last_item = NULL, $start_page = NULL, $end_page = NULL, $total_pages = NULL) {
		$this->_write ( "\n</table>" );
		if ($this->_header_column_rs [0] ['type'] == 'checkbox') {
			$this->_write ( "</form>" );
		}
		
		if ($this->_include_listing_footer !== FALSE) {
			if ($this->getRowCount () > 0) {
				$this->_write ( "<p class=\"pageListingIndex\">" );
				$this->_write ( get_opendb_lang_var ( 'page_listing_index', array (
						'first_row' => $first_item,
						'last_row' => $last_item,
						'total' => $this->_total_items ) ) );
				$this->_write ( "</p>" );
				
				$this->writePageNavBlock ( $first_item, $last_item, $start_page, $end_page, $total_pages );
			} else {
				$this->_write ( "<p class=\"error\">" . $this->_no_rows_message . "</p>" );
			}
		}
	}

	function writeHeaderRowImpl($header_column_rs) {
		if ($this->_header_column_rs [0] ['type'] == 'checkbox') {
			$this->_write ( "\n<form action=\"\" method=\"POST\" class=\"listingcheckboxes\" name=\"" . $this->_header_column_rs [0] ['fieldname'] . "\">" );
			$this->_write ( "<input type=\"hidden\" name=\"op\" value=\"\">" );
		}
		
		$this->_write ( "<table class=\"listing-table\">" );
		
		$this->_write ( "\n<tr class=\"navbar\">" );
		for($i = 0; $i < count ( $header_column_rs ); $i ++) {
			$columnId = NULL;
			if (strlen ( $this->_header_column_rs [$i] ['fieldname'] ) > 0) {
				$columnId = $this->_header_column_rs [$i] ['fieldname'];
			}
			
			$this->_write ( "<th" . (strlen ( $columnId ) > 0 ? " id=\"header-$columnId\"" : "") . ">" );
			
			if ($this->_header_column_rs [$i] ['type'] == 'checkbox') {
				$this->_write ( "<input type=\"checkbox\" class=\"checkbox\" onclick=\"doChecks(this.checked, this.form, '" . $header_column_rs [$i] ['fieldname'] . "[]');return true;\">" );
			} else if ($this->_header_column_rs [$i] ['type'] == 'interest') {
				$level_header_display .= "<img" . " src=\"" . theme_image_src ( 'interest_remove.gif' ) . "\"" . " alt=\"" . get_opendb_lang_var ( 'interest_remove_all' ) . "\"" . " title=\"" . get_opendb_lang_var ( 'interest_remove_all' ) . "\"" . " onclick=\"xajax_ajax_remove_all_interest_level()\"" . " style=\"cursor:pointer;\"" . " >";
				$this->_write ( $level_header_display );
			} else if ($this->_header_column_rs [$i] ['sortcolumn'] !== FALSE) {
				$column_value = '';
				
				$sortorder = NULL;
				
				// Only display the order image if current orderby matches.
				if ($this->_current_orderby == $header_column_rs [$i] ['fieldname']) {
					// Pass the opposite of $sortorder to next instance, for the header links only.
					if (strlen ( $this->_current_sortorder ) == 0 || strcasecmp ( $this->_current_sortorder, "desc" ) === 0) {
						$current_sort_class = "orderby-desc";
						$this->_current_sortorder = 'DESC';
						$sortorder = 'ASC';
					} else {
						$current_sort_class = "orderby-asc";
						$this->_current_sortorder = 'ASC';
						$sortorder = 'DESC';
					}
				} else {
					$sortorder = 'ASC';
				}
				
				$this->_write ( "<a href=\"" . $this->_php_self . "?" . get_url_string ( $this->_http_vars, array (
						'page_no' => '1',
						'order_by' => $this->_header_column_rs [$i] ['fieldname'],
						'sortorder' => $sortorder ) ) . "\"" );
				
				if ($this->_current_orderby == $header_column_rs [$i] ['fieldname']) {
					$this->_write ( " class=\"$current_sort_class\"" );
				}
				$this->_write ( ">" );
				
				$this->_write ( nl2br ( $header_column_rs [$i] ['title'] ) );
				
				$this->_write ( '</a>' );
			} else {
				$this->_write ( nl2br ( $header_column_rs [$i] ['title'] ) );
			}
			$this->_write ( "</th>" );
		} //for($i=0; $i<count($this->_header_column_rs); $i++)
		$this->_write ( "\n</tr>" );
	}

	function writeRowImpl($row_column_rs) {
		if ($this->_toggle)
			$this->rowclass = "oddRow";
		else
			$this->rowclass = "evenRow";
		
		$this->_write ( "\n<tr class=\"" . $this->rowclass . "\">" );
		
		for($i = 0; $i < count ( $row_column_rs ); $i ++) {
			$header_column_r = $this->_header_column_rs [$i];
			
			$columnClass = NULL;
			if (strlen ( $header_column_r ['fieldname'] ) > 0)
				$columnClass = $header_column_r ['fieldname'];
			
			switch ($row_column_rs [$i] ['column_type']) {
				case 'action_links' :
					$this->_write ( '<td class="action_links ' . $columnClass . '">' );
					$this->_write ( ifempty ( format_action_links ( $row_column_rs [$i] ['action_links'] ), get_opendb_lang_var ( 'not_applicable' ) ) );
					$this->_write ( '</td>' );
					break;
				
				case 'username' :
					$this->_write ( '<td class="username ' . $columnClass . '">' );
					$user_id = $row_column_rs [$i] ['user_id'];
					$fullname = $row_column_rs [$i] ['fullname'];
					
					if ($user_id == get_opendb_session_var ( 'user_id' )) {
						$this->_write ( get_opendb_lang_var ( 'current_user', array (
								'fullname' => $fullname,
								'user_id' => $user_id ) ) );
					} else {
						$user_name = get_opendb_lang_var ( 'user_name', array (
								'fullname' => $fullname,
								'user_id' => $user_id ) );
						
						if ($this->_include_href_links && is_user_granted_permission ( PERM_VIEW_USER_PROFILE )) {
							$item_title = '';
							// lets find the title column.
							for($j = 0; $j < count ( $row_column_rs ); $j ++) {
								if ($row_column_rs [$j] ['column_type'] == 'title') {
									$item_title = trim ( strip_tags ( $row_column_rs [$j] ['item_title'] ) );
									break;
								}
							}
							
							$url = "user_profile.php?uid=" . $user_id;
							
							if (is_array ( $row_column_rs [$i] ['extra_http_vars'] )) {
								$url .= "&" . get_url_string ( $row_column_rs [$i] ['extra_http_vars'] );
							}
							
							$url .= "&subject=" . urlencode ( ifempty ( $item_title, get_opendb_lang_var ( 'no_subject' ) ) );
							
							$this->_write ( "<a href=\"$url\" title=\"" . htmlspecialchars ( get_opendb_lang_var ( 'user_profile' ) ) . "\">$user_name</a>" );
						} else {
							$this->_write ( $user_name );
						}
					}
					$this->_write ( '</td>' );
					break;
				
				case 'interest' :
					// 					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, "_xajax=" . $_xajax===NULL?"nulles":"nonnul");
					

					$item_id = $row_column_rs [$i] ['item_id'];
					$instance_no = $row_column_rs [$i] ['instance_no'];
					$level = $row_column_rs [$i] ['level'];
					
					if ($level > 0) {
						$this->addHelpEntry ( get_opendb_lang_var ( 'interest_help' ), 'interest_1.gif', 'interest' );
						$new_level_value = 0;
						$level_display .= "<img" . " id=\"interest_level_$item_id" . "_$instance_no\"" . " src=\"" . theme_image_src ( 'interest_1.gif' ) . "\"" . " alt=\"" . get_opendb_lang_var ( 'interest_remove' ) . "\"" . " title=\"" . get_opendb_lang_var ( 'interest_remove' ) . "\"" . " onclick=\"xajax_ajax_update_interest_level('$item_id', '$instance_no', document.getElementById('new_level_value_$item_id\_$instance_no').value);\"" . " style=\"cursor:pointer;\"" . " >";
					} else {
						$new_level_value = 1;
						$level_display .= "<img" . " id=\"interest_level_$item_id" . "_$instance_no\"" . " src=\"" . theme_image_src ( 'interest_0.gif' ) . "\"" . " alt=\"" . get_opendb_lang_var ( 'interest_mark' ) . "\"" . " title=\"" . get_opendb_lang_var ( 'interest_mark' ) . "\"" . " onclick=\"xajax_ajax_update_interest_level('$item_id','$instance_no', document.getElementById('new_level_value_$item_id\_$instance_no').value);\"" . " style=\"cursor:pointer;\"" . " >";
					}
					
					$this->_write ( '<td class="interest ' . $columnClass . '">' );
					$this->_write ( "<input id=\"new_level_value_$item_id" . "_$instance_no\" type=\"hidden\" value=\"$new_level_value\" />" );
					$this->_write ( $level_display );
					$this->_write ( '</td>' );
					break;
				
				case 'item_type_image' :
					$this->_write ( '<td class="item_type_image ' . $columnClass . '">' );
					$s_item_type = $row_column_rs [$i] ['s_item_type'];
					
					if (! is_array ( $this->_item_type_rs [$s_item_type] ) || strlen ( $this->_item_type_rs [$s_item_type] ['image'] ) == 0) {
						$this->_item_type_rs [$s_item_type] = fetch_item_type_r ( $s_item_type );
						
						// expand to the actual location once only.
						if (strlen ( $this->_item_type_rs [$s_item_type] ['image'] ) > 0)
							$this->_item_type_rs [$s_item_type] ['image'] = theme_image_src ( $this->_item_type_rs [$s_item_type] ['image'] );
						else
							$this->_item_type_rs [$s_item_type] ['image'] = 'none';
						
						if (strlen ( $this->_item_type_rs [$s_item_type] ['description'] ) > 0)
							$this->_item_type_rs [$s_item_type] ['description'] = htmlspecialchars ( $this->_item_type_rs [$s_item_type] ['description'] );
						else
							$this->_item_type_rs [$s_item_type] ['description'] = NULL;
					}
					
					if (strlen ( $this->_item_type_rs [$s_item_type] ['image'] ) > 0 && $this->_item_type_rs [$s_item_type] ['image'] != 'none') {
						$this->_write ( theme_image ( $this->_item_type_rs [$s_item_type] ['image'], $this->_item_type_rs [$s_item_type] ['description'], 's_item_type' ) );
					} else {
						// otherwise write the item type itself in place of the image.
						$this->_write ( $s_item_type );
					}
					
					$this->_write ( '</td>' );
					break;
				
				case 'theme_image' :
					$this->_write ( '<td class="' . $columnClass . '">' );
					$this->_write ( theme_image ( $row_column_rs [$i] ['src'], htmlspecialchars ( $row_column_rs [$i] ['title'] ), $row_column_rs [$i] ['type'] ) );
					$this->_write ( '</td>' );
					break;
				
				case 'title' :
					
					$title_href_link = $row_column_rs [$i] ['title_href_link'];
					$is_item_reviewed = $row_column_rs [$i] ['is_item_reviewed'];
					$is_borrowed_or_returned = $row_column_rs [$i] ['is_borrowed_or_returned'];
					
					$item_title = '';
					if ($this->_include_href_links && is_user_granted_permission ( PERM_VIEW_ITEM_DISPLAY ))
						$item_title = '<a href="' . $title_href_link . '">' . $row_column_rs [$i] ['item_title'] . '</a>';
					else
						$item_title = $row_column_rs [$i] ['item_title'];
					
					if ($is_item_reviewed) {
						// show star if rated - Add it to the actual title, so we can do a bit more with title masks
						$this->addHelpEntry ( get_opendb_lang_var ( 'item_reviewed' ), 'rs.gif', 'item_reviewed' );
						$item_title .= theme_image ( 'rs.gif', get_opendb_lang_var ( 'item_reviewed' ), 'item_reviewed' );
					}
					
					if ($is_borrowed_or_returned) {
						$this->addHelpEntry ( get_opendb_lang_var ( 'youve_borrow_or_return' ), 'tick.gif', 'borrow_or_return' );
						$item_title .= theme_image ( "tick.gif", get_opendb_lang_var ( 'youve_borrow_or_return' ), 'borrow_or_return' ); // show tick if previously borrowed or returned.
					}
					
					$this->_write ( '<td class="title ' . $columnClass . '">' );
					$this->_write ( $item_title );
					$this->_write ( '</td>' );
					break;
				
				case 'coverimage' :
					$item_cover_image = $row_column_rs [$i] ['item_cover_image'];
					$title_href_link = $row_column_rs [$i] ['title_href_link'];
					
					$this->_write ( '<td class="coverimage ' . $columnId . 'Column">' );
					$file_r = file_cache_get_image_r ( $item_cover_image, 'listing' );
					if (is_array ( $file_r )) {
						$cover_image_tag = '<img src="' . $file_r ['thumbnail'] ['url'] . '"';
						
						if (is_numeric ( $file_r ['thumbnail'] ['width'] ))
							$cover_image_tag .= ' width="' . $file_r ['thumbnail'] ['width'] . '"';
						if (is_numeric ( $file_r ['thumbnail'] ['height'] ))
							$cover_image_tag .= ' height="' . $file_r ['thumbnail'] ['height'] . '"';
						
						$cover_image_tag .= '>';
						
						if ($this->_mode != 'printable' && $this->_include_href_links) {
							$cover_image_tag = '<a href="' . $title_href_link . '">' . $cover_image_tag . '</a>';
						}
						
						$this->_write ( $cover_image_tag );
					}
					$this->_write ( '</td>' );
					
					break;
				
				case 'display' :
					$this->_write ( '<td class="' . $columnClass . '">' );
					$this->_write ( get_display_field ( $row_column_rs [$i] ['attribute_type'], $row_column_rs [$i] ['prompt'], $row_column_rs [$i] ['display_type'], $row_column_rs [$i] ['value'], FALSE ) );
					$this->_write ( '</td>' );
					break;
				
				case 'attribute_display' :
					$this->_write ( '<td class="' . $columnClass . '">' );
					$this->_write ( get_item_display_field ( $row_column_rs [$i] ['item_r'], $row_column_rs [$i] ['attribute_type_r'], $row_column_rs [$i] ['value'], FALSE ) );
					$this->_write ( '</td>' );
					break;
				
				case 'checkbox' :
					$this->_write ( '<td class="checkbox">' );
					$value = $row_column_rs [$i] ['value'];
					$this->_write ( '<input type="checkbox" class="checkbox" name="' . $this->_header_column_rs [$i] ['fieldname'] . '[]" value="' . $value . '">' );
					$this->_write ( '</td>' );
					break;
				
				default :
					$this->_write ( '<td class="' . $columnClass . '">' );
					$this->_write ( $row_column_rs [$i] ['value'] );
					$this->_write ( '</td>' );
					break;
			}
		}
		$this->_write ( "\n</tr>" );
	}
	
	//
	// Hidden worker methods
	//
	

	/*
	* Hidden method to write out content
	*/
	function _write($s) {
		if ($this->_buffer_output) {
			$this->_buffer .= $s;
		} else {
			echo ($s);
		}
	}
}
?>
