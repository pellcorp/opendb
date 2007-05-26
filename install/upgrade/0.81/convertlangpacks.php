<?php
function preprocess(&$LANG_VARS)
{
	// removed
	unset($LANG_VARS['colors']);
	unset($LANG_VARS['stats_help']);
	unset($LANG_VARS['usertype_updated']);
	unset($LANG_VARS['user_address_for_user_name']);
	unset($LANG_VARS['usertype_not_updated']);	 
	unset($LANG_VARS['usertype_not_updated_for_user_with_items']);
	unset($LANG_VARS['usertype_not_updated_for_user_with_borrow_or_reserve_items']);
	unset($LANG_VARS['view_log_file']);
	unset($LANG_VARS['log_file_options']); 	 
	unset($LANG_VARS['list_usagelog']); 	 
	unset($LANG_VARS['highlight_usagelog']);
	unset($LANG_VARS['highlight_options']); 	 
	unset($LANG_VARS['foreground']); 	 
	unset($LANG_VARS['background']); 	 
	unset($LANG_VARS['highlight_type']); 	 
	unset($LANG_VARS['keyword']); 	 
	unset($LANG_VARS['line']); 	 
	unset($LANG_VARS['word']); 	 
	unset($LANG_VARS['less_highlight_fields']); 	 
	unset($LANG_VARS['more_highlight_fields']);
	unset($LANG_VARS['clear_log_warning']);
	unset($LANG_VARS['are_you_sure']);
	unset($LANG_VARS['external_noframes_link_text']);
	
	// manual steps required to migrate site plugin input fields accross to db
	unset($LANG_VARS['site']);
	
	// pre version 8.0 lang vars removed.
	unset($LANG_VARS['import_csv_column_mapping_help']);
	unset($LANG_VARS['import_csv_help']);
	 
	if(is_array($LANG_VARS['days_full']))
	{
		$LANG_VARS['sunday'] = $LANG_VARS['days_full'][0];
		$LANG_VARS['monday'] = $LANG_VARS['days_full'][1];
		$LANG_VARS['tuesday'] = $LANG_VARS['days_full'][2];
		$LANG_VARS['wednesday'] = $LANG_VARS['days_full'][3];
		$LANG_VARS['thursday'] = $LANG_VARS['days_full'][4];
		$LANG_VARS['friday'] = $LANG_VARS['days_full'][5];
		$LANG_VARS['saturday'] = $LANG_VARS['days_full'][6];
		
		unset($LANG_VARS['days_full']);
	}
	
	if(is_array($LANG_VARS['days_abbrev']))
	{
		$LANG_VARS['sunday_abbrev'] = $LANG_VARS['days_abbrev'][0];
		$LANG_VARS['monday_abbrev'] = $LANG_VARS['days_abbrev'][1];
		$LANG_VARS['tuesday_abbrev'] = $LANG_VARS['days_abbrev'][2];
		$LANG_VARS['wednesday_abbrev'] = $LANG_VARS['days_abbrev'][3];
		$LANG_VARS['thursday_abbrev'] = $LANG_VARS['days_abbrev'][4];
		$LANG_VARS['friday_abbrev'] = $LANG_VARS['days_abbrev'][5];
		$LANG_VARS['saturday_abbrev'] = $LANG_VARS['days_abbrev'][6];
		
		unset($LANG_VARS['days_abbrev']);
	}

	if(is_array($LANG_VARS['months_full']))
	{
		$LANG_VARS['january'] = $LANG_VARS['months_full'][0];
		$LANG_VARS['february'] = $LANG_VARS['months_full'][1];
		$LANG_VARS['march'] = $LANG_VARS['months_full'][2];
		$LANG_VARS['april'] = $LANG_VARS['months_full'][3];
		$LANG_VARS['may'] = $LANG_VARS['months_full'][4];
		$LANG_VARS['june'] = $LANG_VARS['months_full'][5];
		$LANG_VARS['july'] = $LANG_VARS['months_full'][6];
		$LANG_VARS['august'] = $LANG_VARS['months_full'][7];
		$LANG_VARS['september'] = $LANG_VARS['months_full'][8];
		$LANG_VARS['october'] = $LANG_VARS['months_full'][9];
		$LANG_VARS['november'] = $LANG_VARS['months_full'][10];
		$LANG_VARS['december'] = $LANG_VARS['months_full'][11];
		
		unset($LANG_VARS['months_full']);
	}
	
	if(is_array($LANG_VARS['months_abbrev']))
	{
		$LANG_VARS['january_abbrev'] = $LANG_VARS['months_abbrev'][0];
		$LANG_VARS['february_abbrev'] = $LANG_VARS['months_abbrev'][1];
		$LANG_VARS['march_abbrev'] = $LANG_VARS['months_abbrev'][2];
		$LANG_VARS['april_abbrev'] = $LANG_VARS['months_abbrev'][3];
		$LANG_VARS['may_abbrev'] = $LANG_VARS['months_abbrev'][4];
		$LANG_VARS['june_abbrev'] = $LANG_VARS['months_abbrev'][5];
		$LANG_VARS['july_abbrev'] = $LANG_VARS['months_abbrev'][6];
		$LANG_VARS['august_abbrev'] = $LANG_VARS['months_abbrev'][7];
		$LANG_VARS['september_abbrev'] = $LANG_VARS['months_abbrev'][8];
		$LANG_VARS['october_abbrev'] = $LANG_VARS['months_abbrev'][9];
		$LANG_VARS['november_abbrev'] = $LANG_VARS['months_abbrev'][10];
		$LANG_VARS['december_abbrev'] = $LANG_VARS['months_abbrev'][11];
		
		unset($LANG_VARS['months_abbrev']);
	}

	$LANG_VARS['normal_usertype_description'] = $LANG_VARS['new_account_usertype_intro']['N'];
	$LANG_VARS['borrower_usertype_description'] = $LANG_VARS['new_account_usertype_intro']['B'];
	unset($LANG_VARS['new_account_usertype_intro']);
	
	$LANG_VARS['user_listing_column_header_sort_help'] = $LANG_VARS['user_listing_help'][0];
	unset($LANG_VARS['user_listing_help']);
	
	$LANG_VARS['user_with_inactive_borrows_not_deleted'] = $LANG_VARS['user_with_inactive_borrowed_items_not_deleted'];
	unset($LANG_VARS['user_with_inactive_borrowed_items_not_deleted']);
	
	$LANG_VARS['user_with_owner_inactive_borrows_not_deleted'] = $LANG_VARS['user_with_owner_inactive_borrowed_items_not_deleted'];
	unset($LANG_VARS['user_with_owner_inactive_borrowed_items_not_deleted']);

	if(is_array($LANG_VARS['listings_help']) && 
			!isset($LANG_VARS['listing_column_header_sort_help']) && 
			!isset($LANG_VARS['linked_items_cannot_be_reserved']))
	{
		$LANG_VARS['listing_column_header_sort_help'] = $LANG_VARS['listings_help'][0];
		$LANG_VARS['linked_items_cannot_be_reserved'] = $LANG_VARS['listings_help'][1];
	}
	
	unset($LANG_VARS['listings_help']);
	
	if(is_array($LANG_VARS['user_add_help']) && !isset($LANG_VARS['new_passwd_will_be_autogenerated_if_not_specified']))
	{
		$LANG_VARS['new_passwd_will_be_autogenerated_if_not_specified'] = $LANG_VARS['user_add_help'][0];
	}
	
	unset($LANG_VARS['user_add_help']);
}

function build_list($langvars)
{
	$buffer = '';
	for($i=0; $i<count($langvars); $i++)
	{
		if($i>0)
			$buffer .= "\n";
			
		if(is_array($langvars[$i]))
		{
			$buffer .= "<li>\n<ul>".build_list($langvars[$i])."</ul>\n</li>";	
		}
		else
		{
			$buffer .= "<li>".$langvars[$i]."</li>";
		}
	}
	return $buffer;
}

function build_import_help_file(&$LANG_VARS)
{
	$page = '
<h3>Item Import Help</h3>

<h4>Import Help</h4>

<ul>
'.build_list($LANG_VARS['import_help']).'
</ul>

<h4>Import Row Help</h4>

<ul>
'.build_list($LANG_VARS['import_row_help']).'
</ul>

<h4>Import XML Help</h4>

<ul>
'.build_list($LANG_VARS['import_xml_help']).'
</ul>';

	unset($LANG_VARS['import_help']);
	unset($LANG_VARS['import_row_help']);
	unset($LANG_VARS['import_xml_help']);
	
	return $page;
}

function migrate_borrow_help_file(&$LANG_VARS)
{
	$page = '
<h3>Item Borrowing Help</h3>

<h4>Borrow Help</h3>
<ul>
'.build_list($LANG_VARS['borrow_help']).'
</ul>

<h4>Check Out Borrow Duration More Information</h3>
<ul>
'.build_list($LANG_VARS['check_out_borrow_duration_moreinfo_help']).'
</ul>

<h4>Borrow Item History Help</h3>
<ul>
'.build_list($LANG_VARS['borrow_item_history_help']).'
</ul>

<h4>Reserve Basket Help</h3>
<ul>
'.build_list($LANG_VARS['reserve_basket_help']).'
</ul>';

	unset($LANG_VARS['check_out_borrow_duration_moreinfo_help']);
	unset($LANG_VARS['borrow_help']);
	unset($LANG_VARS['borrow_item_history_help']);
	unset($LANG_VARS['reserve_basket_help']);
	
	return $page;
}

function migrate_search_help_file(&$LANG_VARS)
{
	$page = '
<h3>Item Search</h3>

'.build_list($LANG_VARS['search_help']);

	unset($LANG_VARS['search_help']);
	
	return $page;
}

function get_lang_vars_from_file($filename)
{
	$LANG_VARS = NULL;
	$lang_var = NULL;

	include($filename);
	
	if(is_array($LANG_VARS))
		return $LANG_VARS;
	else if(is_array($lang_var))
		return $lang_var;
	else
		return NULL;
}

if(is_uploaded_file($_FILES['lang_file']['tmp_name']))
{
	$LANG_VARS = get_lang_vars_from_file($_FILES['lang_file']['tmp_name']);
	
	$filename = basename($_FILES['lang_file']['name']);
	if(preg_match("/([^\.]+)\.inc\.php/", $filename, $matches))
	{
		$_GET['language'] = $matches[1];
	}
	
	header('content-type: text/plain');

	if(is_array($lang_var))
		$LANG_VARS =& $lang_var;
	
	preprocess($LANG_VARS);
	
	$import_help_file = build_import_help_file(&$LANG_VARS);
	$borrow_help_file = migrate_borrow_help_file(&$LANG_VARS);
	$search_help_file = migrate_search_help_file(&$LANG_VARS);
	
	$errors = NULL;
	reset($LANG_VARS);
	while(list($key, $langvar) = each($LANG_VARS))
	{
		if(is_array($langvar))
		{
			$errors[] = $key;
		}
	}
	
	if(is_array($errors))
	{
		die("The following language vars are still arrays: \n* ".implode("\n* ", $errors));
	}
	
	$vlanguage = strtoupper($_GET['language']);
	
	echo "----------------------- admin/s_language/sql/${_GET['language']}.sql -------------------------------\n";
	reset($LANG_VARS);
	while(list($key, $value) = each($LANG_VARS))
	{
		echo("INSERT INTO s_language_var(language, varname, value) VALUES('".$vlanguage."', '$key', '".addslashes($value)."');\n");
	}
	
	echo "\n\n----------------------- help/${_GET['language']}/import.html -------------------------------";
	echo $import_help_file;
	echo "\n\n";
	
	echo "----------------------- help/${_GET['language']}/borrow.html -------------------------------";
	echo $borrow_help_file;
	echo "\n\n";
	
	echo "----------------------- help/${_GET['language']}/search.html -------------------------------";
	echo $search_help_file;
	echo "\n\n";
}
else
{
?>
	<h1>Convert Pre Version 1.0 Language Packs</h1>
	
	<p><b>This tool will process the lang/$language.inc.php file only and produce a SQL block, in addition to 2 or more
	help file blocks which must be manually copied to the help/$language/ directory.</b></p>
	
	<table border=0 frameborder=0 cellspacing=1>
	<form name="main" action="<?php echo $PHP_SELF; ?>" method="post" enctype="multipart/form-data">
	<input type="file" name="lang_file">
	<tr><td colspan=2><input type=submit value="Convert">
	</td></tr>
	</form>
	</table>
<?php
}
?>