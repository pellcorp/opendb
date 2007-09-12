<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

include_once("./admin/item_review/ItemReviewAjaxJobs.class.php");

$jobObj =& new ItemReviewAjaxJobs($HTTP_VARS['job']);

$xajax->register(XAJAX_CALLABLE_OBJECT, $jobObj);
?>