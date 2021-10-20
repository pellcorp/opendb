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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/utils.php");
include_once("./lib/datetime.php");
include_once("./lib/http.php");
include_once("./lib/user.php");
include_once("./lib/review.php");
include_once("./lib/borrowed_item.php");
include_once("./lib/item.php");
include_once("./lib/widgets.php");
include_once("./lib/item_type.php");
include_once("./lib/listutils.php");
include_once("./lib/status_type.php");
include_once("./lib/export.php");
include_once("./lib/item_attribute.php");
include_once("./lib/TitleMask.class.php");
include_once("./lib/item_display.php");
include_once("./lib/site_plugin.php");

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		if (is_user_granted_permission(PERM_VIEW_ITEM_DISPLAY)) {
			if (is_numeric($HTTP_VARS['instance_no']))
				$item_r = fetch_item_instance_r($HTTP_VARS['item_id'], $HTTP_VARS['instance_no']);

			if (is_not_empty_array($item_r)) {
				$titleMaskCfg = new TitleMask('item_display');

				$page_title = $titleMaskCfg->expand_item_title($item_r);
				echo _theme_header($page_title, $HTTP_VARS['inc_menu'] ?? TRUE);

				echo ("<h2>" . $page_title . " " . get_item_image($item_r['s_item_type'], $item_r['item_id']) . "</h2>");

				// ---------------- Display IMAGE attributes ------------------------------------
				if (get_opendb_config_var('item_display', 'show_item_image') !== FALSE && is_user_granted_permission(PERM_VIEW_ITEM_COVERS)) {
					$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'IMAGE');
					if ($results) {
						$coverimages_rs = NULL;

						while ($image_attribute_type_r = db_fetch_assoc($results)) {
							$imageurl_r = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'],
                                                                $image_attribute_type_r['s_attribute_type'], $image_attribute_type_r['order_no']);
							if ($imageurl_r !== FALSE) {
								foreach ( $imageurl_r as $imageurl ) {
									$coverimages_rs[] = array('file' => file_cache_get_image_r($imageurl, 'display'),
															  'prompt' => $image_attribute_type_r['prompt']);
								}
							}
						}
						db_free_result($results);

						// provide default if no images
						if ($coverimages_rs == NULL) {
							$coverimages_rs[] = array('file' => file_cache_get_image_r(NULL, 'display'));
						}

						echo ("<ul class=\"coverimages\">");
						foreach ( $coverimages_rs as $coverimage_r ) {
							echo ("<li>");
							$file_r = $coverimage_r['file'];

							if (strlen($file_r['fullsize']['url']) > 0) {
								$width = $file_r['fullsize']['width'];
								$height = $file_r['fullsize']['height'];

								// IE hack
								if ($_OpendbBrowserSniffer->isBrowser('ie')) {
									if (is_numeric($width))
										$width += 40;

									if (is_numeric($height))
										$height += 40;
								}

								echo ("<a href=\"" . $file_r['url'] . "\" onclick=\"popup('" . $file_r['fullsize']['url'] . "', " . $width . ", " . $height . "); return false;\">");
							}
							echo ("<img src=\"" . $file_r['thumbnail']['url'] . "\" title=\"" . htmlspecialchars($coverimage_r['prompt']) . "\" ");

							if (is_numeric($file_r['thumbnail']['width'] ?? FALSE))
								echo (' width="' . $file_r['thumbnail']['width'] . '"');
							if (is_numeric($file_r['thumbnail']['height'] ?? FALSE))
								echo (' height="' . $file_r['thumbnail']['height'] . '"');

							echo (">");
							if (strlen($file_r['fullsize']['url']) > 0) {
								echo ("</a>");
							}
							echo ("</li>");
						}
						echo ("</ul>");
					}
				}

				$otherTabsClass = "tabContentHidden";
				echo ("<div class=\"tabContainer\">");
				echo ("<ul class=\"tabMenu\" id=\"tab-menu\">");
				echo ("<li id=\"menu-details\" class=\"first activeTab\" onclick=\"return activateTab('details')\">" . get_opendb_lang_var('details') . "</li>");
				echo ("<li id=\"menu-instance_info\" onclick=\"return activateTab('instance_info')\">" . get_opendb_lang_var('instance_info') . "</li>");
				if (get_opendb_config_var('item_review', 'enable') !== FALSE) {
					echo ("<li id=\"menu-reviews\" onclick=\"return activateTab('reviews')\">" . get_opendb_lang_var('review(s)') . "</li>");
				}
				echo ("</ul>");

				echo ("<div id=\"tab-content\">");

				echo ("<div class=\"tabContent\" id=\"details\">");

				if (get_opendb_config_var('item_review', 'enable') !== FALSE) {
					$average = fetch_review_rating($item_r['item_id']);
					if ($average !== FALSE) {
						echo ("<p class=\"rating\">");
						echo (get_opendb_lang_var('rating') . ": ");
						$attribute_type_r = fetch_attribute_type_r('S_RATING');
						echo get_display_field($attribute_type_r['s_attribute_type'], NULL, 'review()', $average, FALSE);
						echo ("</p>");
					}
				}

				$results = fetch_item_attribute_type_rs($item_r['s_item_type'], 'not_instance_field_types');
				if ($results) {
					echo ("<table>");

					while ($item_attribute_type_r = db_fetch_assoc($results)) {
                        if (has_role_permission($item_attribute_type_r['view_perm'])) {
                            $display_type = trim($item_attribute_type_r['display_type']);

                            if ( ( $HTTP_VARS['mode'] == 'printable' && $item_attribute_type_r['printable_ind'] != 'Y') ||
                                 ( strlen($display_type) == 0 && $item_attribute_type_r['input_type'] == 'hidden')) {
                                // We allow the get_display_field to handle hidden variable, in case at some stage
                                // we might want to change the functionality of 'hidden' to something other than ignore.
                                $display_type = 'hidden';
                            }

                            if ($item_attribute_type_r['s_field_type'] == 'ITEM_ID')
                                $value = $item_r['item_id'];
                            else if ($item_attribute_type_r['s_field_type'] == 'UPDATE_ON')
                                $value = $item_r['update_on'];
                            else if (is_multivalue_attribute_type($item_attribute_type_r['s_attribute_type']))
                                $value = fetch_attribute_val_r($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);
                            else
                                $value = fetch_attribute_val($item_r['item_id'], $item_r['instance_no'], $item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']);

                            if (is_not_empty_array($value) || (!is_array($value) && strlen($value) > 0)) {
                                $item_attribute_type_r['display_type'] = $display_type;
                                $item_attribute_type_r['compulsory_ind'] = 'N';

                                $field = get_item_display_field($item_r, $item_attribute_type_r, $value, FALSE);

                                if (strlen($field) > 0) {
                                    echo format_item_data_field($item_attribute_type_r, $field); // field mask
                                }
                            }
                        }
					}
					db_free_result($results);

					echo ("\n</table>");
				}

				echo (get_site_plugin_links($page_title, $item_r));

				echo ("</div>");

				$instance_info_links_r = array();
				echo ("<div class=\"$otherTabsClass\" id=\"instance_info\">");
				echo (get_instance_info_block($item_r, $HTTP_VARS, $instance_info_links_r));
				echo (get_related_items_block($item_r, $HTTP_VARS, $instance_info_links_r));
				echo (format_footer_links($instance_info_links_r));
				echo ("</div>");

				if (get_opendb_config_var('item_review', 'enable') !== FALSE) {
					echo ("<div class=\"$otherTabsClass\" id=\"reviews\">");
					echo (get_item_review_block($item_r));
					echo ("</div>");
				}

				echo ("</div>"); // end of tab content
				echo ("</div>"); // end of tabContainer
			} else {
				echo _theme_header(get_opendb_lang_var('item_not_found'));
				echo ("<p class=\"error\">" . get_opendb_lang_var('item_not_found') . "</p>");
			}

			$footer_links_r = array();
			if (is_export_plugin(get_opendb_config_var('item_display', 'export_link')) && is_user_granted_permission(PERM_USER_EXPORT)) {
				$footer_links_r[] = array('url' => "export.php?op=export&plugin=" . get_opendb_config_var('item_display', 'export_link') . "&item_id=" . $item_r['item_id'] . "&instance_no=" . $item_r['instance_no'], 'text' => get_opendb_lang_var('export_item_record'));
			}

			// Include a Back to Listing link.
			if (is_opendb_session_var('listing_url_vars')) {
				$footer_links_r[] = array('url' => "listings.php?" . get_url_string(get_opendb_session_var('listing_url_vars')), 'text' => get_opendb_lang_var('back_to_listing'));
			}

			echo (format_footer_links($footer_links_r));

			echo _theme_footer();
		} else {
			opendb_not_authorised_page(PERM_VIEW_ITEM_DISPLAY, $HTTP_VARS);
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
} else { //if(is_site_enabled())
 	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
