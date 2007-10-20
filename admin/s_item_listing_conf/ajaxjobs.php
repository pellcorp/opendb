<?php
require_once("./lib/xajax/xajax_core/xajax.inc.php");

$requestOnSelect = $xajax->register(XAJAX_FUNCTION, 'onColumnTypeChange');

/**
 * 'column_no', 'column_type', 's_field_type', 's_attribute_type' , 'override_prompt', 'printable_support_ind', 
		'orderby_support_ind', 'orderby_datatype', 'orderby_default_ind', 'orderby_sort_order'
 *
 * @param unknown_type $rowNum
 * @param unknown_type $row
 * @return unknown
 */
function onColumnTypeChange($rowNum, $row)
{
	$objResponse = new xajaxResponse();
	
	//$objResponse->assign("debug","innerHTML", $row['is_new_row']['value']);
	
	return $objResponse;
}
?>