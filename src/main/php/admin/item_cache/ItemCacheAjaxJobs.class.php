<?php
/*
 Open Media Collectors Database
Copyright (C) 2001-2012 by Jason Pell

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

include_once("lib/AdminAjaxJobs.class.php");

class ItemCacheAjaxJobs extends AdminAjaxJobs {
	function ItemCacheAjaxJobs($job) {
		parent::AdminAjaxJobs(get_class($this), $job, 10);
	}

	function __executeJob() {
		$this->_processed = 0;
		$this->_failures = 0;

		if ($this->_job == 'update')
			$this->__perform_update_cache_batch();
		else if ($this->_job == 'refresh')
			$this->__perform_refresh_cache_batch();
		else if ($this->_job == 'refresh_thumbnails')
			$this->__perform_refresh_thumbnails_batch();
	}

	function __perform_update_cache_batch() {
		if ($this->_batchlimit > 0) {
			$results = fetch_file_cache_new_item_attribute_rs();
			if ($results) {
				while ($item_attribute_r = db_fetch_assoc($results)) {
					// if URL happens to have been inserted by someone else before we get to the current
					// row, then this function will do nothing, and thats ok.
					if (file_cache_insert_file($item_attribute_r['attribute_val'], NULL, NULL, NULL, 'ITEM', FALSE)) {
						$this->_processed++;
					} else {
						$this->_failures++;
					}

					// don't process anymore this time around.
					if ($this->_processed >= $this->_batchlimit) {
						break;
					}
				}
				db_free_result($results);
			}
		}

		$this->_remaining = fetch_file_cache_new_item_attribute_cnt();
	}

	function __perform_refresh_cache_batch() {
		if ($this->_batchlimit > 0) {
			$results = fetch_file_cache_refresh_rs('ITEM');
			if ($results) {
				while ($file_cache_r = db_fetch_assoc($results)) {
					// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
					if (file_cache_insert_file($file_cache_r['url'], NULL, NULL, NULL, 'ITEM', TRUE)) {
						$this->_processed++;
					} else {
						$this->_failures++;
					}

					// don't process anymore this time around.
					if ($this->_processed >= $this->_batchlimit) {
						break;
					}
				}
				db_free_result($results);
			}
		}

		$this->_remaining = fetch_file_cache_refresh_cnt('ITEM');
	}

	function __perform_refresh_thumbnails_batch() {
		if ($this->_batchlimit > 0) {
			$results = fetch_file_cache_rs('ITEM');
			if ($results) {
				while ($file_cache_r = db_fetch_assoc($results)) {
					// its not a case of only a thumbnail, if not even the source exists
					if (file_cache_get_cache_file($file_cache_r) !== FALSE && file_cache_get_cache_file_thumbnail($file_cache_r) === FALSE) {
						// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
						if (file_cache_save_thumbnail_file($file_cache_r, $errors)) {
							$this->_processed++;
						} else {
							$this->_failures++;
						}

						// don't process anymore this time around.
						if ($this->_processed >= $this->_batchlimit) {
							break;
						}
					}
				}
				db_free_result($results);
			}
		}

		$this->_remaining = fetch_file_cache_missing_thumbs_cnt('ITEM');
	}
}
?>