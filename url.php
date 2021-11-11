<?php
/* 	
    Open Media Collectors Database
    Copyright (C)2001,2006 by Jason Pell

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

include_once("./lib/filecache.php");
include_once("./lib/file_type.php");

include_once("./lib/OpenDbSnoopy.class.php");

function output_cache_file($url) {
	// no point streaming a local URI
	if (is_url_absolute($url) && get_opendb_config_var('http.stream_external_images', 'enable') !== FALSE && is_uri_domain_in_list($url, get_opendb_config_var('http.stream_external_images', 'domain_list'))) {
		$snoopy = new OpenDbSnoopy();

		$dataBuffer = &$snoopy->fetchURI($url, FALSE);
		if ($dataBuffer !== FALSE) {
			if (is_array($snoopy->headers)) {
				for ($i = 0; $i < count($snoopy->headers); $i++) {
					header($snoopy->headers[$i]);
				}
			}
			echo ($dataBuffer);
			flush();
		} else {
			opendb_redirect($url);
		}
		unset($snoopy);
	} else if (($file = get_item_input_file_upload_url($url)) !== FALSE) { // file upload - that is not cached
 		opendb_redirect($file);
	} else {
		opendb_redirect($url);
	}
}

function handle_file_cache($file_cache_r, $isThumbnail = FALSE) {
	if ($file_cache_r !== FALSE) {
		if ($file_cache_r['cache_type'] == 'ITEM') {
			if ($isThumbnail) {
				$file = file_cache_open_thumbnail_file($file_cache_r);
			}

			// fallback on big image
			if (!$file) {
				if ($file_cache_r['upload_file_ind'] != 'Y') {
					$file = file_cache_open_file($file_cache_r);
				}
			}
		} else {
			$file = file_cache_open_file($file_cache_r);
		}

		if ($file) {
			header("Content-disposition: inline; filename=" . $file_cache_r['cache_file']);
			header("Content-type: " . $file_cache_r['content_type']);
			fpassthru($file);
			fclose($file);
		} else {
			// final fallback
			output_cache_file($file_cache_r['url']);
		}

		return TRUE;
	} else {
		return FALSE;
	}
}

if (is_site_enabled()) {
	if (is_opendb_valid_session() || is_site_public_access()) {
		$isThumbnail = ifempty($HTTP_VARS['op'], 'fullscreen') == 'thumbnail';

		if (is_numeric($HTTP_VARS['id'] ?? "")) {
			$file_cache_r = fetch_file_cache_r($HTTP_VARS['id']);
			if ($file_cache_r !== FALSE) {
				if ($file_cache_r['cache_type'] != 'ITEM' || is_user_granted_permission(PERM_VIEW_ITEM_COVERS)) {
					handle_file_cache($file_cache_r, $isThumbnail);
				} else {
					opendb_not_authorised_page();
				}
			} else {
				opendb_operation_not_available();
			}
		} else if (strlen($HTTP_VARS['tmpId']) > 0) {
			$url = get_url_from_temp_file_cache($HTTP_VARS['tmpId']);
			if ($url !== FALSE) {
				output_cache_file($url);
			} else {
				opendb_operation_not_available();
			}
		} else {
			opendb_operation_not_available();
		}
	} else {
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
