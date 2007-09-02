<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

$xajax->registerFunction("doJob");

function doJob($job, $continue, $completedCount, $failureCount) {
	$objResponse = new xajaxResponse();
	
	if(!is_numeric($completedCount)) {
		$completedCount = 0;
	}
		
	if($continue !== 'false') {
		$batchLimit = 10;

		if($job == 'update')
			perform_update_cache_batch($batchLimit, $processed, $failures, $remaining);
		else if($job == 'refresh')
			perform_refresh_cache_batch($batchLimit, $processed, $failures, $remaining);
		else if($job == 'refresh_thumbnails')
			perform_refresh_thumbnails_batch($batchLimit, $processed, $failures, $remaining);
		
		// get the previous level
		$previousLevel = 0;
		if($completedCount > 0) {
			$previousPercentage = floor($completedCount / ($completedCount / 100));
			if($previousPercentage > 0) {
				$previousLevel = floor($previousPercentage / 10);
			}
		}
		
		$completedCount += $processed;
		$failureCount += $failures;
		
		// total items to be processed.
		$totalItems = $completedCount + $remaining;
		
		if($processed == 0 && $failures > 0) {
			$objResponse->assign("message", "innerHTML", "Job Failure (Completed: $completedCount, Failures: $failureCount)");
			$objResponse->assign("startButton", "value", "Start");
		} else {
			
			$percentage = 0;
			if($remaining > 0) {
				if($completedCount > 0) {
					$percentage = floor($completedCount / ($completedCount + $remaining / 100));
				}
			} else {
				$percentage = 100;
			}
			
			$level = 0;
			if($percentage > 0) {
				$level = floor($percentage / 10);
			}
				
			if( $level > 0 && $level != $previousLevel ) {
				$rsimage = _theme_image_src('rs.gif');
				
				for($i=($previousLevel+1); $i<=$level; $i++) {
					$objResponse->assign("status$i", "src", $rsimage);
				}
			}
			
			$objResponse->assign("percentage", "innerHTML", "$percentage%");
			
			if($remaining > 0) {
				$objResponse->assign("message", "innerHTML", "Completed $completedCount of $totalItems");
				
				$objResponse->script("xajax_doJob('$job', document.forms['progressForm']['continue'].value, $completedCount, $failureCount);");
			} else {
				$objResponse->assign("message", "innerHTML", "Job Complete (Completed: $completedCount, Failures: $failureCount)");
				$objResponse->assign("startButton", "value", "Start");
			}
		}
	} else {
		$objResponse->assign("message", "innerHTML", "Job Aborted (Completed: $completedCount, Failures: $failureCount)");
		$objResponse->assign("startButton", "value", "Start");
	}
	
	return $objResponse;
}

function perform_update_cache_batch($limit, &$processed, &$failures, &$remaining)
{
	$processed = 0;
	$failures = 0;
	
	if($limit > 0) {
		$results = fetch_file_cache_new_item_attribute_rs();
		if($results)
		{
			while($item_attribute_r = db_fetch_assoc($results))
			{
				// if URL happens to have been inserted by someone else before we get to the current
				// row, then this function will do nothing, and thats ok.
				if(file_cache_insert_file($item_attribute_r['attribute_val'], NULL, NULL, NULL, 'ITEM', FALSE))
				{
					$processed++;
				}
				else
				{
					$failures++;
				}

				// don't process anymore this time around.
				if($processed >= $limit)
				{
					break;
				}
			}
			db_free_result($results);
		}
	}
		
	$remaining = fetch_file_cache_new_item_attribute_cnt();
}

function perform_refresh_cache_batch($limit, &$processed, &$failures, &$remaining)
{
	$processed = 0;
	$failures = 0;
	
	if($limit > 0) {
		$results = fetch_file_cache_refresh_rs('ITEM');
		if($results)
		{
			while($file_cache_r = db_fetch_assoc($results))
			{
				// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
				if(file_cache_insert_file($file_cache_r['url'], NULL, NULL, NULL, 'ITEM', TRUE))
				{
					$processed++;
				}
				else
				{
					$failures++;
				}
				
				// don't process anymore this time around.
				if($processed >= $limit)
				{
					break;
				}
			}
			db_free_result($results);
		}
	}
		
	$remaining = fetch_file_cache_refresh_cnt('ITEM');
}

function perform_refresh_thumbnails_batch($limit, &$processed, &$failures, &$remaining)
{
	$processed = 0;
	$failures = 0;
	
	if($limit > 0) {
		$results = fetch_file_cache_rs('ITEM');
		if($results)
		{
			while($file_cache_r = db_fetch_assoc($results))
			{
				// its not a case of only a thumbnail, if not even the source exists
				if(file_cache_get_cache_file($file_cache_r)!==FALSE && 
						file_cache_get_cache_file_thumbnail($file_cache_r)===FALSE)
				{
					// in this case we want to refresh the URL, so TRUE as last parameter idicates overwrite
					if(file_cache_save_thumbnail_file($file_cache_r, $errors))
					{
						$processed++;
					}
					else
					{
						$failures++;
					}
					
					// don't process anymore this time around.
					if($processed >= $limit)
					{
						break;
					}
				}
			}
			db_free_result($results);
		}
	}
		
	$remaining = fetch_file_cache_missing_thumbs_cnt('ITEM');
}
?>