<?php
/* 	
	OpenDb Media Collector Database
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

include_once("./functions/TitleMask.class.php");
include_once("./functions/HTML_Listing.class.inc");
include_once("./functions/item.php");

function get_child_items_table($item_r, $child_item_r, $HTTP_VARS)
{
	$buffer = '';

	$results = fetch_child_item_rs($item_r['item_id']);
	if($results)
	{
		$listingObject =& new HTML_Listing($PHP_SELF, $HTTP_VARS);
		$listingObject->setBufferOutput(TRUE);
		$listingObject->setNoRowsMessage(get_opendb_lang_var('no_items_found'));
		$listingObject->setShowItemImages(TRUE);
		$listingObject->setIncludeFooter(FALSE);
		
		$listingObject->addHeaderColumn(get_opendb_lang_var('type'), 'type', FALSE);
		$listingObject->addHeaderColumn(get_opendb_lang_var('title'), 'title', FALSE);
				
		$include_action_column = FALSE;
		if(get_opendb_session_var('user_id') === $item_r['owner_id'] || is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
		{
			$listingObject->addHeaderColumn(get_opendb_lang_var('action'), 'action', FALSE);
			$include_action_column = TRUE;
		}

		$listingObject->addHeaderColumn(get_opendb_lang_var('category'), 'category', FALSE);
		$listingObject->startListing(NULL);
	
		while($child_item_r = db_fetch_assoc($results))
		{
			$child_item_r['parent_instance_no'] = $item_r['instance_no'];
			
			$listingObject->startRow();
			
			$listingObject->addItemTypeImageColumn($child_item_r['s_item_type'], TRUE);
			
			$listingObject->addTitleColumn($child_item_r);
			
			$action_links_rs = NULL;
			if($include_action_column)
			{
				$action_links_rs[] = array(url=>'item_input.php?op=edit&item_id='.$child_item_r['item_id'].'&parent_id='.$item_r['item_id'].'&parent_instance_no='.$item_r['instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),img=>'edit.gif',text=>get_opendb_lang_var('edit'));
				if(get_opendb_config_var('listings', 'show_refresh_actions') && is_item_legal_site_type($child_item_r['s_item_type']))
				{
					$action_links_rs[] = array(url=>'item_input.php?op=site-refresh&item_id='.$child_item_r['item_id'].'&parent_id='.$item_r['item_id'].'&parent_instance_no='.$item_r['instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),img=>'refresh.gif',text=>get_opendb_lang_var('refresh'));
				}
				$action_links_rs[] = array(url=>'item_input.php?op=delete&item_id='.$child_item_r['item_id'].'&parent_id='.$item_r['item_id'].'&parent_instance_no='.$item_r['instance_no'].(strlen($HTTP_VARS['listing_link'])>0?'&listing_link='.$HTTP_VARS['listing_link']:''),img=>'delete.gif',text=>get_opendb_lang_var('delete'));
			
				$listingObject->addActionColumn($action_links_rs);
			}
			
			$attribute_type_r = fetch_sfieldtype_item_attribute_type_r($child_item_r['s_item_type'], 'CATEGORY');
			if(is_array($attribute_type_r))
			{
				if($attribute_type_r['lookup_attribute_ind']==='Y')
					$attribute_val = fetch_attribute_val_r($child_item_r['item_id'], NULL, $attribute_type_r['s_attribute_type'], $attribute_type_r['order_no']);
				else
					$attribute_val = fetch_attribute_val($child_item_r['item_id'], NULL, $attribute_type_r['s_attribute_type'], $attribute_type_r['order_no']);

				$listingObject->addAttrDisplayColumn(
					$child_item_r,
					$attribute_type_r,
					$attribute_val);
			}
			$listingObject->endRow();
		}
		
		$listingObject->endListing();
		
		$buffer = $listingObject->getContents();
		
		unset($listingObject);
	}
	else
	{
		$buffer .= get_opendb_lang_var('no_linked_items');
	}
	
	$action_links = NULL;
	if(get_opendb_session_var('user_id') === $item_r['owner_id'] || is_user_admin(get_opendb_session_var('user_id'), get_opendb_session_var('user_type')))
	{
		if(get_opendb_config_var('item_input', 'linked_item_support') !== FALSE && 
					is_numeric($item_r['item_id']) && is_numeric($item_r['instance_no']))
		{
			$action_links[] = array(
									url=>"item_input.php?op=site-add&".(get_opendb_config_var('item_input', 'link_same_type_only')===TRUE?"s_item_type=".$item_r['s_item_type']."&":"")."parent_id=".$item_r['item_id']."&parent_instance_no=".$item_r['instance_no']."&listing_link=".$HTTP_VARS['listing_link'],
									text=>get_opendb_lang_var('add_linked'));
									
			$buffer .= format_footer_links($action_links);
		}
	}
		
	return $buffer;
}
?>