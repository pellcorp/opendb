<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

include_once("./admin/s_site_plugin/InstallTableAjaxJobs.class.php");

$HTTP_VARS['import_file'] = basename($HTTP_VARS['import_file'] ?? '');
$jobObj = new InstallTableAjaxJobs($HTTP_VARS['site_type'] ?? "");
$xajax->register(XAJAX_CALLABLE_OBJECT, $jobObj);
?>
