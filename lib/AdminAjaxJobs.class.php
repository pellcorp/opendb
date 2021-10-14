<?php
require_once ("./lib/xajax/xajax_core/xajax.inc.php");

class AdminAjaxJobs {
	var $_processed;
	var $_failures;
	var $_remaining;
	var $_batchlimit;
	var $_completed;
	var $_totalItems;
	var $_job;
	var $_args;
	var $_id;
	var $_debug;

	function __construct($id, $job, $batchlimit) {
		$this->_id = $id;
		$this->_job = $job;
		$this->_batchlimit = $batchlimit;
	}

	function calculateProgress() {
		$this->_completed += $this->_processed;
		
		// total items to be processed.
		$this->_totalItems = $this->_completed + $this->_remaining;
	}

	function dojob($job, $arg1, $continue, $completedCount, $failureCount) {
		$this->_job = $job;
		$this->_args = array (
				$arg1 );
		
		$objResponse = new xajaxResponse ();
		
		if (! is_numeric ( $completedCount )) {
			$completedCount = 0;
		}
		
		$this->_completed = $completedCount;
		$this->_failures = $failureCount;
		
		if ($continue !== 'false') {
			/**
			 * This method will set processed, remaining, failures, but subclass
			 * can also override calculateProgress
			 */
			$this->__executeJob ();
			
			$this->calculateProgress ();
			
			if ($this->_processed == 0 && $this->_failures > 0) {
				$objResponse->assign ( "messageText", "className", "error" );
				$objResponse->assign ( "progressSpinner", "className", "hidden" );
				$objResponse->assign ( "messageText", "innerHTML", "Job Failure (Completed: " . $this->_completed . ", Failures: " . $this->_failures . ")" );
			} else {
				$percentage = 0;
				if ($this->_remaining > 0) {
					if ($this->_completed > 0) {
						$percentage = floor ( $this->_completed / ($this->_totalItems / 100) );
					}
				} else {
					$percentage = 100;
				}
				
				$level = 0;
				if ($percentage > 0) {
					$level = floor ( $percentage / 10 );
				}
				
				if ($level > 0) {
					$rsimage = theme_image_src ( 'rs.gif' );
					
					for($i = 0; $i <= $level; $i ++) {
						$objResponse->assign ( "status$i", "src", $rsimage );
					}
				}
				
				$objResponse->assign ( "percentage", "innerHTML", "$percentage%" );
				
				if ($this->_remaining > 0) {
					$objResponse->assign ( "messageText", "innerHTML", "Completed " . $this->_completed . " of " . $this->_totalItems . " (Failures: " . $this->_failures . ")" );
					$objResponse->assign ( "progressSpinner", "className", "" );
					
					// todo - how to get waitCursor to start again.
					$objResponse->script ( "xajax_" . $this->_id . ".dojob('$job', '$arg1', document.forms['progressForm']['continue'].value, '$this->_completed', '" . $this->_failures . "');" );
				} else {
					$objResponse->assign ( "messageText", "innerHTML", "Job Complete (Completed: " . $this->_completed . ", Failures: " . $this->_failures . ")" );
					$objResponse->assign ( "progressSpinner", "className", "hidden" );
				}
			}
		} else {
			$objResponse->assign ( "messageText", "innerHTML", "Job Aborted (Completed: " . $this->_completed . ", Failures: " . $this->_failures . ")" );
			$objResponse->assign ( "progressSpinner", "className", "hidden" );
		}
		
		if (strlen ( $this->_debug ) > 0) {
			$objResponse->assign ( "debug", "innerHTML", $this->_debug );
		}
		
		return $objResponse;
	}

	function printJobProgressBar($arg1 = NULL) {
		$gsimage = theme_image_src ( 'gs.gif' );
		
		$divContents = '
		<div id="status" style="{width:300; margin: 4px}">
		
		<div id="debug"></div>
		<div id="message" class="success">
			<img id="progressSpinner" class="hidden" src="./images/spinner.gif">
			<span id="messageText"></span>
		</div>
		
		<ul id="progressBar">';
		
		for($i = 1; $i <= 10; $i ++) {
			$divContents .= "\n<li><img id=\"status$i\" src=\"$gsimage\"></li>";
		}
		
		$divContents .= '</ul>
		
		<div id="percentage">0%</div>
		
		
		<form id="progressForm">
			<input type="hidden" name="continue" value="true" />
			<input type="button" class="button" id="startButton" value="Start" 
					onclick="document.getElementById(\'progressSpinner\').className=\'\'; this.form[\'continue\'].value=\'true\'; xajax_' . $this->_id . '.dojob(\'' . $this->_job . '\', \'' . $arg1 . '\', \'true\', \'0\', \'0\'); this.disabled=true; return false;" />
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
