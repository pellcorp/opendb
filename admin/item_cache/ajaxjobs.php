<?php
$rsimage = _theme_image_src('rs.gif');
	
function doJob($job, $count, $continue) {
	global $rsimage;

	$objResponse = new xajaxResponse();
	
	if($continue !== 'false') {
		if(!is_numeric($count)) {
			$count = 0;
		}
	
		if($count > 0) {
			$beforeLevel = floor($count-1 / 10);
			$level = floor($count / 10);
		} else {
			$level = 0;
		}
		
		if( $level > 0 && $beforeLevel != $level ) {
			$objResponse->assign("status$level", "src", $rsimage);
		}
		
		$objResponse->assign("percentage", "innerHTML", "$count%");
		
		if($count < 100) {
			$count++;
			$objResponse->script("xajax_doJob('$job', $count, document.forms['progressForm']['continue'].value);");
		} else {
			$objResponse->assign("message", "innerHTML", "Completed!");
		}
	} else
	{
		$objResponse->assign("message", "innerHTML", "Aborted!");
	}
	
	return $objResponse;
}

$xajax->registerFunction("doJob");
?>