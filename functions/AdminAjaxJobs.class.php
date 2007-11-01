<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

class AdminAjaxJobs
{
	var $_processed;
	var $_failures;
	var $_remaining;
	var $_batchlimit;
	
	var $_job;
	var $_id;
	
	var $_debug;
	
	function AdminAjaxJobs($id, $job, $batchlimit) {
		$this->_job = $job;
		$this->_id = $id;
		$this->_batchlimit = $batchlimit;
	}

	function doJob($job, $continue, $completedCount, $failureCount) {
		$this->_job = $job;
		
		$objResponse = new xajaxResponse();
	
		if(!is_numeric($completedCount)) {
			$completedCount = 0;
		}
		
		if($continue !== 'false') {
			$this->__executeJob($job);
			
			// get the previous level
			$previousLevel = 0;
			if($completedCount > 0) {
				$previousPercentage = floor($completedCount / ($completedCount / 100));
				if($previousPercentage > 0) {
					$previousLevel = floor($previousPercentage / 10);
				}
			}
			
			$completedCount += $this->_processed;
			$failureCount += $this->_failures;
			
			// total items to be processed.
			$totalItems = $completedCount + $this->_remaining;
			
			if($this->_processed == 0 && $this->_failures > 0) {
				
				$objResponse->assign("message", "className", "error");
				$objResponse->assign("message", "innerHTML", "Job Failure (Completed: $completedCount, Failures: $failureCount)");
				
			} else {
				
				$percentage = 0;
				if($this->_remaining > 0) {
					if($completedCount > 0) {
						$percentage = floor($completedCount / ($completedCount + $this->_remaining / 100));
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
				
				if($this->_remaining > 0) {
					$objResponse->assign("message", "innerHTML", "Completed $completedCount of $totalItems (Failures: $failureCount)");
					
					// todo - how to get waitCursor to start again.
					$objResponse->script("xajax_".$this->_id.".dojob('$job', document.forms['progressForm']['continue'].value, '$completedCount', '$failureCount');");
				} else {
					$objResponse->assign("message", "innerHTML", "Job Complete (Completed: $completedCount, Failures: $failureCount)");
				}
			}
		} else {
			//$objResponse->assign("message", "className", "warn");
			$objResponse->assign("message", "innerHTML", "Job Aborted (Completed: $completedCount, Failures: $failureCount)");
		}
		
		if(strlen($this->_debug)>0) {
			$objResponse->assign("debug", "innerHTML", $this->_debug);
		}
		
		return $objResponse;
	}
	
	function printJobProgressBar() {
		$gsimage = _theme_image_src('gs.gif');
	
		$divContents = '
		<div id="status" style="{width:300; margin: 4px}">
		<div id="debug"></div>
		<div style="{width:100%;}" id="message" class="success"></div>
		
		<ul id="progressBar">';
		
		for($i=1;  $i<=10; $i++) {
			$divContents .= "\n<li><img id=\"status$i\" src=\"$gsimage\"></li>";
		}
				
		$divContents .= '</ul>
		
		<div id="percentage">0%</div>
		
		<form id="progressForm">
			<input type="hidden" name="continue" value="true" />
			<input type="button" class="button" id="startButton" value="Start" 
					onclick="this.form[\'continue\'].value=\'true\'; xajax_'.$this->_id.'.dojob(\''.$this->_job.'\', \'true\', \'0\', \'0\'); this.disabled=true; return false;" />
			<input type="button" class="button" id="cancelButton" value="Cancel" 
					onclick="this.form[\'continue\'].value=\'false\'; this.disabled=true; " />
		</form>
		</div>';
	
		echo $divContents;
	}
	
	function __executeJob() {
	}
}
?>