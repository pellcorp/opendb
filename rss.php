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
require_once ("./include/begin.inc.php");

include_once("./lib/database.php");
include_once("./lib/auth.php");
include_once("./lib/logging.php");

include_once("./lib/datetime.php");
include_once("./lib/item_attribute.php");
include_once("./lib/rss.php");
include_once("./lib/whatsnew.php");
include_once("./lib/announcement.php");

function build_opendb_rss_feed($feed_config_r, $URL) {
	$datemask = "DD Mon YYYY HH24:MI:SS";
	
	switch ($feed_config_r ['feed']) {
		case 'new_items' :
			return build_new_items_feed ( $URL, $datemask );
		
		case 'announcements' :
			return build_announcements_feed ( $URL, $datemask );
	}
	
	//else
	return FALSE;
}

function build_new_items_feed($URL, $datemask) {
	$rssout = '';
	
	$last_items_list_conf_r = get_opendb_config_var ( 'feeds.new_items' );
	
	$list_item_rs = get_last_num_items_rs ( $last_items_list_conf_r ['total_num_items'], 	// number of items to return
				NULL, 	//owner_id 
				NULL, 	// s_item_type
				NULL, 	//update_on
				NULL, 	// not_owner_id
				NULL, 	// $site_url_prefix
				'feeds' );
	
	if (is_not_empty_array ( $list_item_rs )) {
		reset ( $list_item_rs );
		foreach ( $list_item_rs as $list_item_r ) {
			$rssout .= "\n	<item>" . "\n		<title>" . rss_encoded ( $list_item_r ['title'] ) . "</title>" . "\n		<link>" . rss_encoded ( $URL . $list_item_r ['item_display_url'] ) . "</link>" . "\n		<pubDate>" . $list_item_r ['update_on'] . " " . date ( 'T' ) . "</pubDate>" . "\n		<guid>" . rss_encoded ( $URL . $list_item_r ['item_display_url'] ) . "</guid>" . "\n		<description>";
			
			$results = fetch_item_attribute_type_rs ( $list_item_r ['s_item_type'], 'rss_ind' );
			if ($results) {
				$attribute_block = '';
				while ( $item_attribute_type_r = db_fetch_assoc ( $results ) ) {
					if (has_role_permission ( $item_attribute_type_r ['view_perm'] )) {
						if (strlen ( $attribute_block ) > 0) {
							$attribute_block .= "\n";
						}
						
						$attributes_r = fetch_attribute_val_r ( $list_item_r ['item_id'], $list_item_r ['instance_no'], $item_attribute_type_r ['s_attribute_type'], $item_attribute_type_r ['order_no'] );
						if (is_array ( $attributes_r )) {
							$attribute = "";
							foreach ( $attributes_r as $value ) {
								if (strlen ( $attribute ) > 0)
									$attribute .= ", ";
								
								$attribute .= rss_encoded ( $value );
							}
							$attribute_block .= $attribute;
						}
					}
				} //while
				db_free_result ( $results );
				
				$rssout .= $attribute_block;
			}
			
			$rssout .= "\n		</description>" . "\n	</item>";
		}
	}
	
	return $rssout;
}

function build_announcements_feed($URL, $datemask) {
	$rssout = '';
	
	$last_items_list_conf_r = get_opendb_config_var ( 'feeds.announcements' );
	
	// TODO - make the options here configurable
	$result = fetch_announcement_rs ( NULL, 	//$order_by
"DESC", 	//$sortorder
0, 	//$start_index
$last_items_list_conf_r ['total_num_items'], 	//5, //$items_per_page
"N", 	//$limit_days
"Y" ); //$limit_closed
	

	// Create the RSS item tags
	if ($result && db_num_rows ( $result ) > 0) {
		while ( $item_instance_r = db_fetch_assoc ( $result ) ) {
			$rssout .= "\n	<item>" . "\n		<title>" . rss_encoded ( $item_instance_r ['title'] ) . "</title>" . "\n		<link>" . rss_encoded ( $URL ) . "</link>" . "\n		<pubDate>" . get_localised_timestamp ( $datemask, $item_instance_r ['submit_on'] ) . " " . date ( 'T' ) . "</pubDate>" . "\n		<guid>" . rss_encoded ( $URL ) . "</guid>" . "\n		<description>" . rss_encoded ( nl2br ( $item_instance_r ['content'] ) ) . "</description>" . "\n	</item>";
		}
		db_free_result ( $result );
	}
	
	return $rssout;
}

// Returns text w/o characters that cause problems for xml
function rss_encoded($inString) {
	return htmlspecialchars ( $inString );
}

if (is_site_enabled ()) {
	if (is_opendb_valid_session () || is_site_public_access ()) {
		if (strlen ( $HTTP_VARS ['feed'] ) == 0) {
			echo _theme_header ( get_opendb_lang_var ( 'rss_feeds' ) );
			
			echo "<h2>" . get_opendb_lang_var ( 'rss_feeds' ) . "</h2>";
			
			$feeds_r = get_opendb_rss_feeds ();
			
			echo "<ul id=\"rssfeeds\">";
			
			reset ( $feeds_r );
			foreach ( $feeds_r  as $feed_r ) {
				echo ('<li><a href="' . $PHP_SELF . '?feed=' . $feed_r ['feed'] . '">' . $feed_r ['title'] . '</a></dd>');
			}
			echo "</ul>";
			
			echo _theme_footer ();
		} else {
			@set_time_limit ( 600 );
			
			$feed_config_r = get_opendb_rss_feed_config ( $HTTP_VARS ['feed'] );
			if (is_not_empty_array ( $feed_config_r )) {
				$URL = get_site_url ();
				
				$rssoutput = '';
				// Create the RSS header and channel information tags
				header ( 'Content-type: text/xml' );
				header ( "Content-disposition: inline; filename=${feed_config_r['feed']}.rss" );
				
				$rssoutput .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>" . "\n<rss version=\"2.0\">" . "\n<channel>" . "\n	<title>" . rss_encoded ( get_opendb_config_var ( 'site', 'title' ) . " " . get_opendb_version () ) . "</title>" . "\n	<link>" . $URL . "</link>" . "\n	<description>" . $feed_config_r ['title'] . "</description>" . "\n	<image>" . "\n		<url>" . $URL . "images/icon.gif</url>" . "\n		<title>" . rss_encoded ( get_opendb_config_var ( 'site', 'title' ) . " " . get_opendb_version () ) . "</title>" . "\n		<link>" . $URL . "</link>" . "\n	</image>";
				
				$rssoutput .= build_opendb_rss_feed ( $feed_config_r, $URL );
				
				// Create the end tags
				$rssoutput .= "\n</channel>" . "\n</rss>";
				
				if (function_exists ( "mb_convert_encoding" ) && function_exists ( "mb_detect_encoding" )) {
					echo mb_convert_encoding ( $rssoutput, mb_detect_encoding ( $rssoutput ), "UTF-8" );
				} else {
					echo $rssoutput;
				}
			} else { //else if($HTTP_VARS['type'] == 'announcements')
				opendb_operation_not_available();
			}
		}
	} else { //not a valid session.
		// invalid login, so login instead.
		redirect_login ( $PHP_SELF, $HTTP_VARS );
	}
} else { //if(is_site_enabled())
	opendb_site_disabled();
}

// Cleanup after begin.inc.php
require_once ("./include/end.inc.php");
?>
