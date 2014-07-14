<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

include_once("./lib/item_attribute.php");

// legal s_field_type values.
$_FIELD_TYPES = array('' => '', // empty option
		'TITLE' => 'Item Title', 'CATEGORY' => 'Category', 'STATUSTYPE' => 'Status Indicator', 'STATUSCMNT' => 'Status Comment', 'DURATION' => 'Borrow Duration', 'ITEM_ID' => 'Item ID', 'UPDATE_ON' => 'Last Updated', 'IMAGE' => 'Cover Image', 'RATING' => 'System rating attribute',
		'ADDRESS' => 'System address attribute');

$argument_types = array('width' => array('prompt' => 'Width', 'description' => 'Specify the width of window', 'input_type' => 'number(3)'), 'height' => array('prompt' => 'Height', 'description' => 'Specify the height of window', 'input_type' => 'number(3)'),
		'target' => array('prompt' => 'Target', 'description' => 'Specify the target window.  Options are: <ul>' . '<li>_blank - New Window</li>' . '<li>_self - Current Window</li></ul>', 'input_type' => 'text(10,10)'),
		'maxrange' => array('prompt' => 'Max Range', 'description' => 'Specify the maximum range', 'input_type' => 'number(3)'),
		'ratingmask' => array('prompt' => 'Rating Display Mask',
				'description' => 'Specify the rating display mask, with the following special variables:' . '<table>' . '<tr><th>Mask</th><th>Description</th>' . '<tr><td>%value%</td><td>The real rating value</td></tr>' . '<tr><td>%maxrange%</td><td>The maximum range of the rating</td></tr>'
						. '<tr><td>%starrating%</td><td>The star rating itself</td></tr>' . '</table>', 'input_type' => 'text(20,100)'), 'viewbutton' => array('prompt' => 'View Button', 'description' => 'Include view button. [Y|N]', 'input_type' => 'checkbox(Y,N,)'),
		'theme_img' => array('prompt' => 'Image', 'description' => 'Image (literal or if/switch mask statement).', 'input_type' => 'url(50,100,"gif,jpg,jpeg,png"'),
		'auto_datetime' => array('prompt' => 'Auto Datetime', 'description' => 'Provide current date/time as default for new field', 'input_type' => 'checkbox(Y,N,)'),
		'length' => array('prompt' => 'Length', 'description' => 'Specify the visible size of the text field', 'input_type' => 'number(3)'),
		'maxlength' => array('prompt' => 'Maxlength', 'description' => 'Specify the maximum length of the text input for the text field', 'input_type' => 'number(3)'),
		'display_mask' => array('prompt' => 'Mask',
				'description' => 'Specifies the format of each lookup value. The %value%, %display% and ' . '%img% mask variables correspond to the <i>value</i>,<i>display</i> ' . 'and <i>img</i> columns in the s_attribute_type_lookup_table.  This '
						. 'table is where the lookup values are sourced for this input field.', 'input_type' => 'text(50,100)'),
		'display_file_mask' => array('prompt' => 'Mask', 'description' => 'Specifies the format of the fileviewer link. The %value% and ' . '%img% mask variables correspond to the <i>value</i> ' . 'and file extension <i>icon</i>', 'input_type' => 'text(50,100)'),
		'input_datetime_mask' => array('prompt' => 'Datetime Mask',
				'description' => 'A mask consisting of the following mask variables:' . '<table>' . '<tr><th>Mask</th><th>Description</th>' . '<tr><td>DD</td><td>Days (01 - 31)</td></tr>' . '<tr><td>MM</td><td>Months (01 -12)</td></tr>' . '<tr><td>YYYY</td><td>Years</td></tr>'
						. '<tr><td>HH24</td><td>Hours (00 - 23)</td></tr>' . '<tr><td>HH</td><td>Hours (01 - 12)</td></tr>' . '<tr><td>MI</td><td>Minutes (00 - 59)</td></tr>' . '<tr><td>SS</td><td>Seconds (00 - 59)</td></tr>' . '</table>', 'input_type' => 'text(50,100)'),
		'display_datetime_mask' => array('prompt' => 'Datetime Mask',
				'description' => 'A mask consisting of the following mask variables:' . '<table>' . '<tr><th>Mask</th><th>Description</th>' . '<tr><td>Month</td><td>Month name</td></tr>' . '<tr><td>Mon</td><td>Abbreviated month, Initcap</td></tr>'
						. '<tr><td>MON</td><td>Abreviated month UPPERCASE</td></tr>' . '<tr><td>Day</td><td>Weekday name (display widget only!)</td></tr>' . '<tr><td>DDth</td><td>Day of the month with English suffix (1st, 2nd, 3rd)</td></tr>' . '<tr><td>DD</td><td>Days (01 - 31)</td></tr>'
						. '<tr><td>MM</td><td>Months (01 -12)</td></tr>' . '<tr><td>YYYY</td><td>Years</td></tr>' . '<tr><td>HH24</td><td>Hours (00 - 23)</td></tr>' . '<tr><td>HH</td><td>Hours (01 - 12)</td></tr>' . '<tr><td>MI</td><td>Minutes (00 - 59)</td></tr>'
						. '<tr><td>SS</td><td>Seconds (00 - 59)</td></tr>' . '<tr><td>AM</td><td>Meridian indicator. Will be \'AM\' or \'PM\', but \'AM\' is used as the mask</td></tr>' . '</table>', 'input_type' => 'text(50,100)'),
		'legalchars' => array('prompt' => 'Legal Char List',
				'description' => 'Specify a list of legal characters that can be used for this field. ' . 'You can specify this as a comma delimited list, or as a range. ' . '<br />Example: <b>a-z,1,2,3</b> This list will allow all lowercase ' . 'characters and the numbers 1 2 and 3 to be used.',
				'input_type' => 'text(50,100)'), 'checked-val' => array('prompt' => 'Checked Value', 'description' => 'Specify the value the checkbox should be when it IS checked.', 'input_type' => 'text(50,100)'),
		'unchecked-val' => array('prompt' => 'Unchecked Value', 'description' => 'Specify the value the checkbox should be when it IS NOT checked.', 'input_type' => 'text(50,100)'),
		'cols' => array('prompt' => 'Columns', 'description' => 'How many columns wide should this field be?', 'input_type' => 'number(2)'), 'rows' => array('prompt' => 'Columns', 'description' => 'How many rows high should this field be?', 'input_type' => 'number(2)'),
		'orientation' => array('prompt' => 'Orientation', 'description' => 'Which way should this field be formatted?' . '<p>Options are:' . '<ul>' . '<li>VERTICAL</li>' . '<li>HORIZONTAL</li>' . '</ul></p>', 'input_type' => 'value_select("VERTICAL,HORIZONTAL", 1)'),
		'content_group' => array('prompt' => 'Content Group', 'description' => 'Specify which content group(s) this field will accept as legal.' . '<p>Example: <b>IMAGE, VIDEO</b></p>', 'input_type' => 'text(50)'),
		'columns' => array('prompt' => 'Columns', 'description' => 'How many columns wide should this field be?', 'input_type' => 'number(2)'), 'border' => array('prompt' => 'Border', 'description' => 'What size table border to put around this field?', 'input_type' => 'number(2)'),
		'size' => array('prompt' => 'Size', 'description' => 'What size should this select field be.  A size >1 will cause the ' . 'select field to be a MULTIPLE select as well.', 'input_type' => 'number(2)'),
		'value_list' => array('prompt' => 'Value List', 'description' => 'Provide the value options for the widget, comma delimited.' . '<p>Example: <b>one,two,three</b></p>', 'input_type' => 'text(50,100)'),
		'delimiter' => array('prompt' => 'Delimiter', 'description' => 'Specify a single character delimiter to tokenise the text.', 'input_type' => 'text(1,1)'),
		'list_type' => array('prompt' => 'List Type',
				'description' => 'Specify a list format type.' . '<p>Options are: ' . '<ul>' . '<li>nl2br - Each token will be separated by a newline &lt;br&gt;</li>' . '<li>plain - Will display the tokens in a HTML &lt;UL&gt; list with a class of \'plain\'.</li>'
						. '<li>names - List type specifically for multivalue names, such as for Authors, Artists, etc.  Same as plain except for the CSS style which is \'names\'.</li>' . '<li>ordered - Will display the tokens in a HTML &lt;OL&gt; list.</li>'
						. '<li>unordered - Same as plain except for the CSS style which is \'unordered\'.</li>' . '<li>ticks - Same as plain except for the CSS style which is \'ticks\' and the default implementation will use a tick image for each entry instead of the standard html bullet.</li>'
						. '</ul></p>', 'input_type' => 'value_select("plain,nl2br,ordered,unordered,ticks", 1)'),
		'time_mask' => array('prompt' => 'Time Mask',
				'description' => 'Specify time format mask.' . '<p>The mask components supported are: ' . '<ul>' . '<li>%h - hour value only</li>' . '<li>%H - text &quot;hour&quot; or &quot;hours&quot;</li>' . '<li>%m - minute value only</li>'
						. '<li>%M - text &quot;minute&quot; or &quot;minutes&quot;</li>' . '</ul></p>', 'input_type' => 'text(50,100)'),
        'default_val' => array('prompt' => 'Default Value',
                'description' => 'The default value for this field.', 'input_type' => 'text(50,100)'),);

// input type functions
$input_type_functions = array('hidden' => array('args' => array(), 'description' => 'A hidden input field.  Hidden fields are often used for site plugin link attributes.'), 'readonly' => array('args' => array(), 'description' => 'A readonly field'),
		'text' => array('args' => array('length[Y]', 'maxlength', 'default_val'), 'description' => 'A text field'), 'textarea' => array('args' => array('cols[Y]', 'rows[Y]'), 'description' => 'A textarea field'),
		'htmlarea' => array('args' => array('cols[Y]', 'rows[Y]'), 'description' => 'A HTML textarea field'), 'email' => array('args' => array('length[Y]', 'maxlength'), 'description' => 'A text field with email format validation'),
		'filtered' => array('args' => array('length[Y]', 'maxlength', 'legalchars[Y]', 'default_val'), 'description' => 'A text field with validation controlled by legalchars parameter.'),
		'datetime' => array('args' => array('input_datetime_mask[Y]', 'auto_datetime'), 'description' => 'A datetime field, which much match the Datetime Mask exactly.' . 'Must be used with the matching \'datetime\' display type widget.'),
		'number' => array('args' => array('length[Y]', 'default_val'), 'description' => 'A text field with numeric validation'),
		'checkbox' => array('args' => array('checked-val[Y]', 'unchecked-val', 'default_val'), 'description' => 'A two state checkbox.  This differs from normal check boxes, ' . 'because this one can send a value to OpenDb whether checked ' . 'or not.'),
		'review_options' => array('args' => array('display_mask', 'orientation'), 'description' => 'Item Review / Search specific widget.'),
		'url' => array('args' => array('length[Y]', 'maxlength', 'content_group'), 'description' => 'External URL or file upload (file upload configuration permitting), with popup file viewer'),
		'radio_grid' => array('args' => array('display_mask', 'orientation'), 'description' => 'A formatted list of radio buttons, one for each matching (according to the item_attribute s_attribute_type) lookup record.'),
		'checkbox_grid' => array('args' => array('display_mask', 'orientation'), 'description' => 'A formatted list of checkboxes, one for each matching (according to the item_attribute s_attribute_type) lookup record.'),
		'value_radio_grid' => array('args' => array('value_list[Y]'), 'description' => 'A list of radio buttons, for each value in the comma delimited value_list.'),
		'single_select' => array('args' => array('display_mask', 'length'), 'description' => 'A <i>single</i> select field, with a option for each matching (according to ' . 'the item_attribute s_attribute_type) lookup record.'),
		'multi_select' => array('args' => array('display_mask', 'length', 'size'), 'description' => 'A <i>multiple / single</i> (size parameter controls this) select field, with a ' . 'option for each matching (according to the item_attribute s_attribute_type) lookup record.'),
		'value_select' => array('args' => array('value_list[Y]', 'size'), 'description' => 'A <i>multiple / single</i> (size parameter controls this) select object which formats a ' . ' select object with the specified value_list'));

// Display type functions.
$display_type_functions = array(
		'list' => array('args' => array('list_type[Y]', 'delimiter'), 'description' => 'If the attribute is multivalue, the list will be formatted according to the list type, otherwise the attribute will be delimited ' . 'using a newline and formatted as required.'),
		'fileviewer' => array('args' => array('display_file_mask', 'width', 'height', 'target'),
				'description' => 'This widget, will optionally display a file icon or url text, with a popup link to display ' . 'it in a new window.  The width & height arguments are optional, to ' . 'control the dimensions of the window.  The window will be opened '
						. 'with 640x480 dimensions by default.'), 
		'format_mins' => array('args' => array('time_mask[Y]'), 'description' => 'Format a time value.'), 
		'star_rating' => array('args' => array('maxrange[Y]', 'ratingmask'), 'description' => 'Format a star rating.'),
		'datetime' => array('args' => array('display_datetime_mask[Y]'), 'description' => 'Display a datetime populated with the matching \'datetime\' input type.'),
		'display' => array('args' => array('display_mask[Y]'), 'description' => 'Format the display value using the %display% and %img% attributes of the matching ' . 'lookup table record, or %value% for the attribute value itself.'),
		'category' => array('args' => array('display_mask[Y]'), 'description' => 'A special format function especially designed for item category'), 
		'hidden' => array('args' => array(), 'description' => 'A hidden input field.  Hidden fields are often used for site plugin link attributes.'));

function validate_s_field_type($s_field_type) {
	global $_FIELD_TYPES;

	$s_field_type = strtoupper(trim($s_field_type));

	if (strlen($s_field_type) > 0 && isset($_FIELD_TYPES[$s_field_type])) {
		return $s_field_type;
	} else {
		return NULL;
	}
}

function validate_display_type($display_type) {
	global $display_type_functions;

	$display_type = strtolower(trim($display_type));

	if (strlen($display_type) > 0 && isset($display_type_functions[$display_type])) {
		return $display_type;
	} else {
		return NULL;
	}
}

function validate_input_type($input_type) {
	global $input_type_functions;

	$input_type = strtolower(trim($input_type));

	if (strlen($input_type) > 0 && isset($input_type_functions[$input_type])) {
		return $input_type;
	} else {
		return NULL;
	}
}

/**
 * Checks to see if the s_attribute_type is linked to any s_item_type's via a
 * s_item_attribute_type table record.  The delete will not be allowed if any
 * such record is found.
 * 
 * @param $s_item_type		If specified will restrict to specific s_item_type
 * @param $s_attribute_type	If specified will restrict to specific s_item_attribute_type
 * @param $order_no			If specified will restrict to specific $s_attribute_type AND $order_no
 */
function is_exists_item_attribute_type($s_item_type, $s_attribute_type, $order_no = NULL) {
	$query = "SELECT 'x' FROM s_item_attribute_type ";

	if (strlen($s_attribute_type) > 0) {
		$where .= " s_attribute_type = '" . $s_attribute_type . "'";
		if (is_numeric($order_no))
			$where .= " AND order_no = '$order_no'";
	}

	// Support check for any instances of the s_attribute_type in the s_item_attribute_type table,
	// or a specific s_item_type instance if $s_item_type specified.	
	if (strlen($s_item_type) > 0) {
		if (strlen($s_attribute_type) > 0)
			$where .= " AND ";
		$where .= "s_item_type = '$s_item_type'";
	}

	if (strlen($where) > 0)
		$query .= " WHERE $where ";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function is_exists_addr_attribute_type_rltshp($s_address_type, $s_attribute_type, $order_no = NULL) {
	$query = "SELECT 'x' FROM s_addr_attribute_type_rltshp ";

	if (strlen($s_attribute_type) > 0) {
		$where .= " s_attribute_type = '" . $s_attribute_type . "'";
		if (is_numeric($order_no))
			$where .= " AND order_no = '$order_no'";
	}

	// Support check for any instances of the s_attribute_type in the s_addr_attribute_type_rltshp table,
	// or a specific s_address_type instance if $s_address_type specified.	
	if (strlen($s_address_type) > 0) {
		if (strlen($s_attribute_type) > 0)
			$where .= " AND ";
		$where .= "s_address_type = '$s_address_type'";
	}

	if (strlen($where) > 0)
		$query .= " WHERE $where ";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

/*
 * Fetch a list of all s_attribute_type's which do not start with the restricted
 * 'S_ prefix.
 */
function fetch_user_attribute_type_rs($orderby = "s_attribute_type", $order = "asc") {
	$query = "SELECT s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type, lookup_attribute_ind, multi_attribute_ind FROM s_attribute_type " . "WHERE s_attribute_type NOT LIKE 'S\_%'";

	$query .= " ORDER BY $orderby $order";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

/*
 * Fetch a list of ALL s_attribute_type's
 */
function fetch_attribute_type_rs($orderby = "s_attribute_type", $order = "asc") {
	$query = "SELECT s_attribute_type, description, prompt, s_field_type, site_type, lookup_attribute_ind, multi_attribute_ind 
			FROM s_attribute_type
			ORDER BY $orderby $order";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0)
		return $result;
	else
		return FALSE;
}

/**
    This one selects FULL records from the table.
 */
function fetch_s_attribute_type_r($s_attribute_type) {
	$query = "SELECT s_attribute_type, 
					description, 
					prompt, 
					display_type,
					display_type_arg1,
					display_type_arg2,
					display_type_arg3,
					display_type_arg4,
					display_type_arg5,
					input_type,
					input_type_arg1,
					input_type_arg2,
					input_type_arg3,
					input_type_arg4,
					input_type_arg5,
					listing_link_ind,
					s_field_type,
					site_type, 
					file_attribute_ind, 
					lookup_attribute_ind, 
					multi_attribute_ind,
					view_perm
			FROM s_attribute_type 
			WHERE s_attribute_type = '$s_attribute_type'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found;
	}

	//else
	return FALSE;
}

/**
 */
function is_reserved_s_attribute_type($s_attribute_type) {
	if (strtoupper(substr(trim($s_attribute_type), 0, 2)) == "S_")
		return TRUE;
	else
		return FALSE;
}

function fetch_attribute_type_s_field_type($s_attribute_type) {
	$query = "SELECT s_field_type FROM s_attribute_type WHERE s_attribute_type = '$s_attribute_type'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$found = db_fetch_assoc($result);
		db_free_result($result);
		return $found['s_field_type'];
	}

	//else
	return FALSE;
}

function fetch_s_attribute_type_lookup_cnt($s_attribute_type) {
	$query = "SELECT count('x') as count FROM s_attribute_type_lookup WHERE s_attribute_type = '" . $s_attribute_type . "'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found !== FALSE)
			return $found['count'];
	}
}

/**
    Check if record exists.
 */
function is_exists_s_atribute_type_lookup($s_attribute_type, $value) {
	$query = "SELECT 'x' FROM s_attribute_type_lookup WHERE s_attribute_type = '" . $s_attribute_type . "' AND value = '$value'";

	$result = db_query($query);
	if ($result && db_num_rows($result) > 0) {
		db_free_result($result);
		return TRUE;
	}

	//else
	return FALSE;
}

function validate_s_attribute_type($s_attribute_type, &$description, &$prompt, &$input_type, &$input_type_arg1, &$input_type_arg2, &$input_type_arg3, &$input_type_arg4, &$input_type_arg5, &$display_type, &$display_type_arg1, &$display_type_arg2, &$display_type_arg3, &$display_type_arg4,
		&$display_type_arg5, &$s_field_type, &$site_type, &$listing_link_ind, &$file_attribute_ind, &$lookup_attribute_ind, &$multi_attribute_ind, &$view_perm) {
	$description = addslashes(trim(strip_tags($description)));
	$prompt = addslashes(trim(strip_tags($prompt)));

	if ($display_type !== FALSE) {
		$display_type = validate_display_type($display_type);
		if (strlen($display_type) > 0) {
			$display_type_arg1 = addslashes(trim(strip_tags($display_type_arg1)));
			$display_type_arg2 = addslashes(trim(strip_tags($display_type_arg2)));
			$display_type_arg3 = addslashes(trim(strip_tags($display_type_arg3)));
			$display_type_arg4 = addslashes(trim(strip_tags($display_type_arg4)));
			$display_type_arg5 = addslashes(trim(strip_tags($display_type_arg5)));
		} else {
			$display_type_arg1 = NULL;
			$display_type_arg2 = NULL;
			$display_type_arg3 = NULL;
			$display_type_arg4 = NULL;
			$display_type_arg5 = NULL;
		}
	}

	if ($input_type !== FALSE) {
		$input_type = validate_input_type($input_type);
		if (strlen($input_type) > 0) {
			$input_type_arg1 = addslashes(trim(strip_tags($input_type_arg1)));
			$input_type_arg2 = addslashes(trim(strip_tags($input_type_arg2)));
			$input_type_arg3 = addslashes(trim(strip_tags($input_type_arg3)));
			$input_type_arg4 = addslashes(trim(strip_tags($input_type_arg4)));
			$input_type_arg5 = addslashes(trim(strip_tags($input_type_arg5)));
		} else {
			$input_type_arg1 = NULL;
			$input_type_arg2 = NULL;
			$input_type_arg3 = NULL;
			$input_type_arg4 = NULL;
			$input_type_arg5 = NULL;
		}
	}

	if ($s_field_type !== FALSE)
		$s_field_type = validate_s_field_type($s_field_type);

	if ($site_type !== FALSE)
		$site_type = strtolower(trim($site_type));

	if ($multi_attribute_ind !== FALSE)
		$multi_attribute_ind = validate_ind_column($multi_attribute_ind);

	if ($lookup_attribute_ind !== FALSE)
		$lookup_attribute_ind = validate_ind_column($lookup_attribute_ind);

	if ($file_attribute_ind !== FALSE)
		$file_attribute_ind = validate_ind_column($file_attribute_ind);

	if ($listing_link_ind !== FALSE)
		$listing_link_ind = validate_ind_column($listing_link_ind);

	// only one of these indicators can be Y
	if ($lookup_attribute_ind == 'Y') {
		$multi_attribute_ind = 'N';
		$file_attribute_ind = 'N'; // cannot have a lookup type that is also a file_resources
	} else if ($file_attribute_ind == 'Y') {
		$multi_attribute_ind = 'N';
		$lookup_attribute_ind = 'N';
	} else if ($multi_attribute_ind == 'Y') {
		$file_attribute_ind = 'N'; // cannot have a lookup type that is also a file_resources
		$lookup_attribute_ind = 'N';
	}

    if ($view_perm !== FALSE)
        $view_perm = strtoupper(trim($view_perm));
}

function insert_s_attribute_type($s_attribute_type, $description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5,
		$s_field_type, $site_type, $listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm) {
	validate_s_attribute_type($s_attribute_type, $description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5,
			$s_field_type, $site_type, $listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm);

	$query = "INSERT INTO s_attribute_type (s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, s_field_type, site_type, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, view_perm) "
			. "VALUES ('$s_attribute_type', '$description', '$prompt', '$input_type', '$input_type_arg1', '$input_type_arg2', '$input_type_arg3', '$input_type_arg4', '$input_type_arg5', '$display_type', '$display_type_arg1', '$display_type_arg2', '$display_type_arg3', '$display_type_arg4', '$display_type_arg5', '$s_field_type', '$site_type', '$listing_link_ind', '$file_attribute_ind', '$lookup_attribute_ind', '$multi_attribute_ind', '$view_perm')";
	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL,
					array($description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5, $s_field_type, $site_type,
							$listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(),
				array($description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5, $s_field_type, $site_type,
						$listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm));
		return FALSE;
	}
}

/*
 */
function update_s_attribute_type($s_attribute_type, $description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5,
		$s_field_type, $site_type, $listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm) {
	validate_s_attribute_type($s_attribute_type, $description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5,
			$s_field_type, $site_type, $listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm);

	$query = "UPDATE s_attribute_type " . "SET description = '" . $description . "'" . ", prompt = '" . $prompt . "'" . ($input_type !== FALSE ? ", input_type = '" . $input_type . "'" : "") . ($input_type_arg1 !== FALSE ? ", input_type_arg1 = '" . $input_type_arg1 . "'" : "")
			. ($input_type_arg2 !== FALSE ? ", input_type_arg2 = '" . $input_type_arg2 . "'" : "") . ($input_type_arg3 !== FALSE ? ", input_type_arg3 = '" . $input_type_arg3 . "'" : "") . ($input_type_arg4 !== FALSE ? ", input_type_arg4 = '" . $input_type_arg4 . "'" : "")
			. ($input_type_arg5 !== FALSE ? ", input_type_arg5 = '" . $input_type_arg5 . "'" : "") . ($display_type !== FALSE ? ", display_type = '" . $display_type . "'" : "") . ($display_type_arg1 !== FALSE ? ", display_type_arg1 = '" . $display_type_arg1 . "'" : "")
			. ($display_type_arg2 !== FALSE ? ", display_type_arg2 = '" . $display_type_arg2 . "'" : "") . ($display_type_arg3 !== FALSE ? ", display_type_arg3 = '" . $display_type_arg3 . "'" : "") . ($display_type_arg4 !== FALSE ? ", display_type_arg4 = '" . $display_type_arg4 . "'" : "")
			. ($display_type_arg5 !== FALSE ? ", display_type_arg5 = '" . $display_type_arg5 . "'" : "") . ($s_field_type !== FALSE ? ", s_field_type = '" . $s_field_type . "'" : "") . ($site_type !== FALSE ? ", site_type = '" . $site_type . "'" : "")
			. ($listing_link_ind !== FALSE ? ", listing_link_ind = '" . $listing_link_ind . "'" : "") . ($file_attribute_ind !== FALSE ? ", file_attribute_ind = '" . $file_attribute_ind . "'" : "") . ($lookup_attribute_ind !== FALSE ? ", lookup_attribute_ind = '" . $lookup_attribute_ind . "'" : "")
			. ($multi_attribute_ind !== FALSE ? ", multi_attribute_ind = '" . $multi_attribute_ind . "'" : "") . ($view_perm !== FALSE ? ", view_perm = '" . $view_perm . "'" : "") . " WHERE s_attribute_type = '$s_attribute_type'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL,
					array($description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5, $s_field_type, $site_type,
							$listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(),
				array($description, $prompt, $input_type, $input_type_arg1, $input_type_arg2, $input_type_arg3, $input_type_arg4, $input_type_arg5, $display_type, $display_type_arg1, $display_type_arg2, $display_type_arg3, $display_type_arg4, $display_type_arg5, $s_field_type, $site_type,
						$listing_link_ind, $file_attribute_ind, $lookup_attribute_ind, $multi_attribute_ind, $view_perm));
		return FALSE;
	}
}

function delete_s_attribute_type($s_attribute_type) {
	$query = "DELETE FROM s_attribute_type " . "WHERE s_attribute_type = '$s_attribute_type'";

	$delete = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($delete && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_attribute_type));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_attribute_type));
		return FALSE;
	}
}

/**
    Assumes that is_exists_s_atribute_type_lookup($s_attribute_type, $value) has been called
    before this function to ensure we are not inserting a duplicate.  Mysql will fall over any
    way.
 */
function insert_s_attribute_type_lookup($s_attribute_type, $value, $display, $img, $checked_ind, $order_no) {
	$display = addslashes(trim(strip_tags($display)));

	// Validate checked_ind
	if (strcasecmp($checked_ind, "Y") === 0 || strcasecmp($checked_ind, "N") === 0)
		$checked_ind = strtoupper($checked_ind);
	else
		$checked_ind = "";

	$query = "INSERT INTO s_attribute_type_lookup (s_attribute_type, value, display, img, checked_ind, order_no) " . "VALUES ('$s_attribute_type', '" . $value . "', '" . $display . "', '$img', '$checked_ind', " . (is_numeric($order_no) ? "'$order_no'" : "NULL") . ")";
	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_attribute_type, $value, $display, $img, $checked_ind, $order_no));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_attribute_type, $value, $display, $img, $checked_ind, $order_no));
		return FALSE;
	}
}

/**
 */
function update_s_attribute_type_lookup($s_attribute_type, $value, $display, $img, $checked_ind, $order_no) {
	$display = addslashes(trim(strip_tags($display)));

	// Validate checked_ind
	if (strcasecmp($checked_ind, 'Y') === 0 || strcasecmp($checked_ind, 'N') === 0)
		$checked_ind = strtoupper($checked_ind);
	else
		$checked_ind = '';

	$query = "UPDATE s_attribute_type_lookup " . "SET display = '" . $display . "', " . "img = '$img', " . "checked_ind = '$checked_ind', " . "order_no = " . (is_numeric($order_no) ? "'$order_no'" : "NULL") . " " . "WHERE s_attribute_type = '$s_attribute_type' AND " . "value = '$value'";

	$update = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($update && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_attribute_type, $value, $display, $img, $checked_ind, $order_no));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_attribute_type, $value, $display, $img, $checked_ind, $order_no));
		return FALSE;
	}
}

/**
    Assumes record exists!
 */
function delete_s_attribute_type_lookup($s_attribute_type, $value) {
	$query = "DELETE FROM s_attribute_type_lookup " . "WHERE s_attribute_type = '$s_attribute_type' AND " . "value = '$value'";

	$delete = db_query($query);

	// We should not treat updates that were not actually updated because value did not change as failures.
	$rows_affected = db_affected_rows();
	if ($delete && $rows_affected !== -1) {
		if ($rows_affected > 0) {
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($s_attribute_type, $value));
		}
		return TRUE;
	} else {
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($s_attribute_type, $value));
		return FALSE;
	}
}
?>
