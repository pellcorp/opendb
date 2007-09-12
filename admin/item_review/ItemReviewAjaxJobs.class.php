<?php

include_once("./functions/AdminAjaxJobs.class.php");

class ItemReviewAjaxJobs extends AdminAjaxJobs
{
	function ItemReviewAjaxJobs($job) {
		parent::AdminAjaxJobs('itemreviewsajaxjobs', $job, 10);
	}
	
	function __executeJob() {
		$this->_processed = 0;
		$this->_failures = 0;
		
		if($this->_job == 'recalculate')
			$this->__perform_item_review_recalculate();
	}
	
	function __perform_item_review_recalculate()
	{
		if($this->_batchlimit > 0) {
		}
	}
}
?>