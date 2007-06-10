<?php
/* 	
	Open Media Collectors Database
	Copyright (C)2001,2006 by Jason Pell

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

include_once("./functions/filecache.php");
include_once("./functions/file_type.php");

include_once("./functions/OpenDbSnoopy.class.inc");

function output_cache_file($cache_type, $url)
{
	// no point streaming a local URI
	if($cache_type == 'ITEM' && 
			is_url_absolute($url) && 
			get_opendb_config_var('http.stream_external_images', 'enable')!==FALSE &&
			is_uri_domain_in_list($url, get_opendb_config_var('http.stream_external_images', 'domain_list')))
	{
		$snoopy =& new OpenDbSnoopy(FALSE);
		
		$dataBuffer = $snoopy->fetchURI($url, FALSE);
		if($dataBuffer!==FALSE)
		{
			if(is_array($snoopy->headers))
			{
				for($i=0; $i<count($snoopy->headers); $i++)
				{
					header($snoopy->headers[$i]);
				}
			}
			echo($dataBuffer);
			flush();
		}
		else
		{
			header('Location:'.$url);
		}
		unset($snoopy);
	}
	else
	{
		header('Location:'.$url);
	}
}

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		// The most basic required parameter for this script is the 'url' parameter
		if(strlen($HTTP_VARS['url'])>0)
		{
			/*if(isset($HTTP_VARS['item_id']) && 
						isset($HTTP_VARS['instance_no']) && 
						isset($HTTP_VARS['s_attribute_type']) && 
						isset($HTTP_VARS['order_no']))
			{
				$HTTP_VARS['url'] = get_upload_file_url($HTTP_VARS['item_id'], $HTTP_VARS['instance_no'], $HTTP_VARS['s_attribute_type'], $HTTP_VARS['order_no'], 1, $HTTP_VARS['url']);
			}*/
			
			$HTTP_VARS['cache_type'] = ifempty($HTTP_VARS['cache_type'], 'ITEM');
			if($HTTP_VARS['cache_type'] == 'ITEM' || $HTTP_VARS['cache_type'] == 'HTTP')
			{
				// for simplicity sake, we do not ignore expired cached files when displaying, its assumed that
				// some automated process will refresh them as required, and of course if the item is ever refreshed
				// any cached items will be reviewed and recached as required.
				$file_cache_r = fetch_url_file_cache_r($HTTP_VARS['url'], $HTTP_VARS['cache_type'], INCLUDE_EXPIRED);
				if($file_cache_r!==FALSE)
				{
					$file_type_r = fetch_file_type_r($file_cache_r['content_type']);
					if($HTTP_VARS['cache_type'] == 'ITEM')
					{
						if(ifempty($HTTP_VARS['op'], 'fullsize') == 'fullsize')
						{
							$file = file_cache_open_file($file_cache_r);
						}
						else
						{
							$file = file_cache_open_thumbnail_file($file_cache_r);
							
							// fallback on big image
							if($file === FALSE)
							{
								$file = file_cache_open_file($file_cache_r);
							}
						}
					}
					else
					{
						$file = file_cache_open_file($file_cache_r);
					}
				
					if($file)
					{
						// TODO: derive a better filename, perhaps derived from a basename of the URL itself
						$filename = strtolower($file_type_r['content_group']).$sequence_number.'.'.$file_type_r['extension'];
						
						header("Content-disposition: inline; filename=".$filename);
						header("Content-type: ".$file_type_r['content_type']);
						fpassthru($file);
						fclose($file);
					}
					else
					{
						// final fallback
						output_cache_file($HTTP_VARS['cache_type'], $HTTP_VARS['url']);
					}
				}//if($sequence_number!==FALSE)
				else
				{
					output_cache_file($HTTP_VARS['cache_type'], $HTTP_VARS['url']);
				}
			}//if($HTTP_VARS['cache_type'] == 'ITEM' || $HTTP_VARS['cache_type'] == 'HTTP')
			else
			{
				header('Location:'.$HTTP_VARS['url']);
			}
		} //if(strlen($HTTP_VARS['url'])>0)
		else
		{
			echo _theme_header(get_opendb_lang_var('external_url_error'),FALSE);
			echo("<p class=\"error\">".get_opendb_lang_var('external_url_error')."</p>");
			echo _theme_footer();
		}
	}
	else
	{
		// invalid login, so login instead.
		redirect_login($PHP_SELF, $HTTP_VARS);
	}
}//if(is_site_enabled())

// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>