<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

include_once("./admin/item_cache/ItemCacheAjaxJobs.class.php");

$jobObj = new ItemCacheAjaxJobs($HTTP_VARS['job']);

$xajax->register(XAJAX_CALLABLE_OBJECT, $jobObj);
?>