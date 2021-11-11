<?php

include_once("./lib/AdminAjaxJobs.class.php");

class ItemCacheAjaxJobs extends AdminAjaxJobs {
	function __construct($job) {
		parent::__construct(get_class($this), $job, 10);
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
