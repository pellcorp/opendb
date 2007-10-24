<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");

include_once("./functions/item_type.php");
include_once("./functions/item_attribute.php");
include_once("./functions/widgets.php");
include_once("./functions/item.php");
include_once("./functions/parseutils.php");
include_once("./functions/user.php");
include_once("./functions/status_type.php");

function encode_search_javascript_arrays(&$item_type_rs, &$arrayOfUniqueCategories, &$item_attribute_type_rs)
{
	$buffer = "";
	
	$arrayOfCategoryTypes = array();
	$arrayOfUniqueAttributes = array();
	
	$itemTypeBlock = "";
	$attrTypeSelect = "";
	
	$jsArrayOfAttributes = "";
	$jsArrayOfAttributesCount=0;
	
	$itemresults = fetch_item_type_rs();
	while($item_type_r = db_fetch_assoc($itemresults))
	{
		$results = fetch_item_attribute_type_rs($item_type_r['s_item_type'], NULL, 'prompt');
		// For the case where we have a s_item_type with no attributes associated with it!
		if($results)
		{
			while($item_attribute_type_r = db_fetch_assoc($results))
			{
				// Do not include any attributes which do not exist as item attributes
				if($item_attribute_type_r['s_field_type'] != 'TITLE' &&
							$item_attribute_type_r['s_field_type'] != 'DURATION' &&
							$item_attribute_type_r['s_field_type'] != 'STATUSTYPE' &&
							$item_attribute_type_r['s_field_type'] != 'STATUSCMNT' &&
							$item_attribute_type_r['s_field_type'] != 'ITEM_ID')
				{
					// Only unique attributes should be added to attribute options object.
					if(!is_array($arrayOfUniqueAttributes) || !in_array($item_attribute_type_r['s_attribute_type'], $arrayOfUniqueAttributes))
					{
						// This array, is only so we can make sure that we have unique attributes only!
						$arrayOfUniqueAttributes[] = $item_attribute_type_r['s_attribute_type'];
						
						$jsArrayOfUniqueAttributes .= "\narrayOfAttributes[$jsArrayOfAttributesCount] = new LookupAttribute(\"\",\"".$item_attribute_type_r['s_attribute_type']."\",\"".$item_attribute_type_r['s_attribute_type']." - ".$item_attribute_type_r['description']."\");";

						$item_attribute_type_rs[] = $item_attribute_type_r;
					}
					
					$jsArrayOfAttributes .= "\narrayOfAttributes[$jsArrayOfAttributesCount] = new LookupAttribute(\"".$item_type_r['s_item_type']."\",\"".$item_attribute_type_r['s_attribute_type']."\",\"".$item_attribute_type_r['s_attribute_type']." - ".$item_attribute_type_r['description']."\");";
					$jsArrayOfAttributesCount++;
				}
			}
		}

		// Get the category_attribute_type for this item_type
		$category_attribute_type = fetch_sfieldtype_item_attribute_type($item_type_r['s_item_type'], 'CATEGORY');

		// We need this list further down, to work out which of the lookup items are actually categories.
		$arrayOfCategoryTypes[] = $category_attribute_type;
		
		$item_type_rs[] = $item_type_r;
	}

	// Give us the whole s_attribute_type_lookup table, whoo baby...
	$jsArrayOfLookupValues = "";
	$jsArrayOfLookupValuesCount=0;

	$attresults = fetch_attribute_type_lookup_rs(NULL, 's_attribute_type ASC');
	while($attribute_type_r = db_fetch_assoc($attresults))
	{
		if($attribute_type_r['s_field_type'] != 'ADDRESS' && $attribute_type_r['s_field_type'] != 'S_RATING')
		{
			if(in_array($attribute_type_r['s_attribute_type'], $arrayOfCategoryTypes) && 
					(!is_array($arrayOfUniqueCategories) || !in_array($attribute_type_r['value'], $arrayOfUniqueCategories)))
			{
				$arrayOfUniqueCategories[$attribute_type_r['value']] = $attribute_type_r['display'];
			}
		
			$jsArrayOfLookupValues .= "\narrayOfLookupValues[$jsArrayOfLookupValuesCount] = new LookupAttribute(\"".$attribute_type_r['s_attribute_type']."\",\"".$attribute_type_r['value']."\",\"".$attribute_type_r['display']."\");";
			$jsArrayOfLookupValuesCount++;
		}
	}

	$buffer .= "\n\narrayOfLookupValues = new Array($jsArrayOfLookupValuesCount);";
	$buffer .= $jsArrayOfLookupValues;

	$buffer .= "\n\narrayOfAttributes = new Array($jsArrayOfAttributesCount);";
	$buffer .= $jsArrayOfAttributes;

	$arrayOfUniqueCategoryValues="";
	$arrayOfUniqueCatValuesCount=0;

	// Now sort all values into alphabetical order!
	if(is_array($arrayOfUniqueCategories))
	{
		asort($arrayOfUniqueCategories);
		reset($arrayOfUniqueCategories);
		while( list($value,$display) = each($arrayOfUniqueCategories))
		{
			$arrayOfUniqueCatValues .= "\narrayOfUniqueCatValues[$arrayOfUniqueCatValuesCount] = new LookupAttribute('',\"$value\",\"$display\");";
			$arrayOfUniqueCatValuesCount++;
		}
	}
	
	// Now wrap and return
	return "\n<script language=\"JavaScript\">\n<!-- // hide from stupid browsers\n".
				$buffer.
				"\n// -->\n</script>\n";
}

//
// Note the "----------- ALL -----------" display values is a
// kludge to support dynamic lov's in netscape 4.  We need to
// ensure the size of the lov does not need to get any larger,
// so we set to the largest value it will ever get using the
// ----------- ALL -----------.  All the other lov's include
// this as well for uniformity.
//
if(is_site_enabled())
{
	if (is_opendb_valid_session())
	{
		$page_title = get_opendb_lang_var('advanced_search');
		echo _theme_header($page_title);

		echo(encode_search_javascript_arrays($item_type_rs, $category_type_rs, $item_attribute_type_rs));

		echo("<h2>".$page_title."</h2>");
		
		echo("\n<form name=\"search\" method=\"GET\" action=\"listings.php\">");

		// global declaration of the datetimemask, to be used by all Date fields in search page.
		echo("\n<input type=hidden name=\"datetimemask\" value=\"".get_opendb_config_var('search', 'datetime_mask')."\">");

		// Indicate to listings.php that search.php initiated it.
		echo("\n<input type=hidden name=\"search_list\" value=\"y\">");
		
		echo("<table class=\"searchForm\">");

		// ------------------------
		// TITLE FIELD
		// ------------------------
		echo format_field(
			get_opendb_lang_var('title'),
			NULL,
				"\n<input type=text size=50 name=\"title\">".
				"\n<ul class=\"searchInputOptions\">".
				"\n<li><input type=radio name=\"title_match\" value=\"word\">".get_opendb_lang_var('word_match')."</li>".
				"\n<li><input type=radio name=\"title_match\" value=\"partial\" CHECKED>".get_opendb_lang_var('partial_match')."</li>".
				"\n<li><input type=radio name=\"title_match\" value=\"exact\">".get_opendb_lang_var('exact_match')."</li>".
				"\n<li><input type=checkbox name=\"title_case\" value=\"case_sensitive\">".get_opendb_lang_var('case_sensitive')."</li>".
				"\n</ul>"
			);
		
		if(@count($category_type_rs)>1)
		{
			// ------------------------
			// CATEGORY FIELD
			// ------------------------
			$catTypeSelect = "<select name=\"category\">".
				"\n<option value=\"\">-------------- ".get_opendb_lang_var('all')." --------------";
			
			reset($category_type_rs);	
			while(list($value,$display) = each($category_type_rs))
			{
				$catTypeSelect .= "\n<option value=\"$value\">$display";
			}
			$itemTypeBlock .= "</select>";
			
			echo format_field(
				get_opendb_lang_var('category'), 
				NULL,
				$catTypeSelect);
		}
		
		// Lets display the field just like item_review.php
		$attribute_type_r = fetch_attribute_type_r("S_RATING");
		$attribute_type_r['compulsory_ind'] = 'N';
		echo get_item_input_field("rating",
					$attribute_type_r,
					NULL, // $item_r
   	  	        	NULL,//value
					TRUE);
			
		// ------------------------
		// S_ITEM_TYPE FIELD
		// ------------------------
		if(@count($item_type_rs)>1)
		{
			$itemTypeSelect = "<select name=\"s_item_type\" 
						onChange=\"populateList(this.options[this.options.selectedIndex].value, this.form.attribute_type, arrayOfAttributes, true, '------------- ".get_opendb_lang_var('all')." -------------', false);\">".
					"\n<option value=\"\">-------------- ".get_opendb_lang_var('all')." --------------";
			
			reset($item_type_rs);		
			while(list(,$item_type_r) = each($item_type_rs))
			{
				$itemTypeSelect .= "\n<option value=\"".$item_type_r['s_item_type']."\" >".$item_type_r['s_item_type']." - ".$item_type_r['description'];
			}
			$itemTypeBlock .= "</select>";
			
			echo format_field(
				get_opendb_lang_var('item_type'), 
				NULL,
				$itemTypeSelect);
		}
		
		// ------------------------
		// ATTRIBUTE_TYPE FIELD
		// ------------------------
		$attrTypeSelect = "<select name=\"attribute_type\" onChange=\"populateList(this.options[this.options.selectedIndex].value, this.form['lookup_attribute_val'], arrayOfLookupValues, false, '".get_opendb_lang_var('use_the_value_field')." ---->', true);\">".
				"\n<option value=\"\">-------------- ".get_opendb_lang_var('all')." --------------";
		
		@reset($item_attribute_type_rs);		
		while(list(,$item_attribute_type_r) = @each($item_attribute_type_rs))
		{
			$attrTypeSelect .= "\n<option value=\"".$item_attribute_type_r['s_attribute_type']."\">".$item_attribute_type_r['s_attribute_type']." - ".$item_attribute_type_r['description'];
		}
		$itemTypeBlock .= "</select>";
		
		echo format_field(
			get_opendb_lang_var('s_attribute_type'), 
			NULL,
			$attrTypeSelect);
		
		 echo format_field(
			get_opendb_lang_var('s_attribute_type_lookup'),
			NULL,
			"\n<select name=\"lookup_attribute_val\" onChange=\"if(this.options[this.options.selectedIndex].value.length>0){this.form['attribute_val'].disabled=true;}else{this.form['attribute_val'].disabled=false;}\">".
				"\n<option value=\"\">".get_opendb_lang_var('use_the_value_field')." ---->".
				"\n</select>");
				
		 echo format_field(
			get_opendb_lang_var('attribute_val'), 
			NULL,
				"<input type=\"text\" name=\"attribute_val\" size=50 value=\"\">".
				"\n<ul class=\"searchInputOptions\">".
				"\n<li><input type=radio name=\"attr_match\" value=\"word\">".get_opendb_lang_var('word_match')."</li>".
				"\n<li><input type=radio name=\"attr_match\" value=\"partial\" CHECKED>".get_opendb_lang_var('partial_match')."</li>".
				"\n<li><input type=radio name=\"attr_match\" value=\"exact\">".get_opendb_lang_var('exact_match')."</li>".
				"\n<li><input type=checkbox name=\"attr_case\" value=\"case_sensitive\">".get_opendb_lang_var('case_sensitive')."</li>".
				"\n</ul>"
			);
				
		// ------------------------
		// UPDATE_ON FIELD
		// ------------------------
		echo format_field(
		get_opendb_lang_var('updated'), 
		NULL,
		"\n<select name=\"attr_update_on_days\" onChange=\"if(this.options[this.options.selectedIndex].value.length>0){this.form['attr_update_on'].disabled=true;}else{this.form['attr_update_on'].disabled=false;}\">".
			"\n<option value=\"\">".get_opendb_lang_var('specify_datetime')." ---->".
			"\n<option value=\"1\">".get_opendb_lang_var('one_day_ago').
			"\n<option value=\"7\">".get_opendb_lang_var('one_week_ago').
			"\n<option value=\"28\">".get_opendb_lang_var('one_month_ago').
			"\n<option value=\"365\">".get_opendb_lang_var('one_year_ago').
			"\n</select>".
			get_input_field("attr_update_on", NULL, NULL,"datetime(".get_opendb_config_var('search', 'datetime_mask').")","N",NULL,FALSE));
		
		// ------------------------
		// OWNER FIELD
		// ------------------------
		
		// Must pass on not_owner_id if specified.
		if(strlen($HTTP_VARS['not_owner_id'])>0)
		{
			echo("\n<input type=hidden name=\"not_owner_id\" value=\"".$HTTP_VARS['not_owner_id']."\">");
		}
		
		echo(
			format_field(
				get_opendb_lang_var('owner'), 
				NULL,
				"\n<select name=\"owner_id\">".
					"\n<option value=\"\">-------------- ".get_opendb_lang_var('all')." --------------".
					custom_select(
						'owner_id', 
						fetch_user_rs(get_owner_user_types_r()), 
						'%fullname% (%user_id%)',
						'NA',
						NULL,
						'user_id'
					).
					"\n</select>"
				)
			);
		
        // ------------------------
        // Item Status
        // ------------------------
		$lookup_results = fetch_status_type_rs(TRUE,TRUE,TRUE);
		
		// Only include Status type restriction, if more than once status type.
		if($lookup_results && db_num_rows($lookup_results)>1)
		{
			echo format_field(
				get_opendb_lang_var('s_status_type'),
				NULL,
				checkbox_grid('s_status_type',
							$lookup_results, 
							'%img%', // mask
							'VERTICAL',
							array())); // value
		}
		
		// ------------------------
		// Status Comment FIELD
		// ------------------------
		echo format_field(
			get_opendb_lang_var('status_comment'), 
			NULL,
				"\n<input type=text size=50 name=\"status_comment\">".
				"\n<ul class=\"searchInputOptions\">".
				"\n<li><input type=radio name=\"status_comment_match\" value=\"word\">".get_opendb_lang_var('word_match')."</li>".
				"\n<li><input type=radio name=\"status_comment_match\" value=\"partial\" CHECKED>".get_opendb_lang_var('partial_match')."</li>".
				"\n<li><input type=radio name=\"status_comment_match\" value=\"exact\">".get_opendb_lang_var('exact_match')."</li>".
				"\n<li><input type=checkbox name=\"status_comment_case\" value=\"case_sensitive\">".get_opendb_lang_var('case_sensitive')."</li>".
				"\n</ul>"
			);

		// ------------------------
		// UPDATE_ON FIELD
		// ------------------------
		echo format_field(
			get_opendb_lang_var('updated'), 
			NULL,
			"\n<select name=\"update_on_days\" onChange=\"if(this.options[this.options.selectedIndex].value.length>0){this.form['update_on'].disabled=true;}else{this.form['update_on'].disabled=false;}\">".
				"\n<option value=\"\">".get_opendb_lang_var('specify_datetime')." ---->".
				"\n<option value=\"1\">".get_opendb_lang_var('one_day_ago').
				"\n<option value=\"7\">".get_opendb_lang_var('one_week_ago').
				"\n<option value=\"28\">".get_opendb_lang_var('one_month_ago').
				"\n<option value=\"365\">".get_opendb_lang_var('one_year_ago').
				"\n</select>".
					get_input_field("update_on", NULL, NULL,"datetime(".get_opendb_config_var('search', 'datetime_mask').")","N",NULL,FALSE));
	
		echo format_field(
			get_opendb_lang_var('order_by'), 
			NULL,
			"\n<select name=\"order_by\">".
				"\n<option value=\"title\" SELECTED>".get_opendb_lang_var('title').
				"\n<option value=\"owner_id\">".get_opendb_lang_var('owner').
				"\n<option value=\"category\">".get_opendb_lang_var('category').
				"\n<option value=\"s_item_type\">".get_opendb_lang_var('item_type').
				"\n<option value=\"s_status_type\">".get_opendb_lang_var('s_status_type').
				"\n<option value=\"update_on\">".get_opendb_lang_var('update_date').
				"\n</select>".
			
				"\n<input type=radio name=\"sortorder\" value=\"ASC\" CHECKED>".get_opendb_lang_var('asc').
				"\n<input type=radio name=\"sortorder\" value=\"DESC\">".get_opendb_lang_var('desc'));
		
		echo("</table>");
		
		echo("\n<input type=submit value=\"".get_opendb_lang_var('search')."\">");
		echo("</form>");

		echo _theme_footer();
	}
	else
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())
else
{
	echo _theme_header(get_opendb_lang_var('site_is_disabled'), FALSE);
	echo("<p class=\"error\">".get_opendb_lang_var('site_is_disabled')."</p>");
	echo _theme_footer();
}

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>
