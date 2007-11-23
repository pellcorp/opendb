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
		$snoopy =& new OpenDbSnoopy();
		
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
			http_redirect($url);
		}
		unset($snoopy);
	}
	else if( ($file = get_item_input_file_upload_url($url))!==FALSE )  // file upload - that is not cached
	{
		http_redirect($file);
	}
	else
	{
		http_redirect($url);
	}
}

if(is_site_enabled())
{
	if(is_opendb_valid_session())
	{
		// The most basic required parameter for this script is the 'url' parameter
		if(strlen($HTTP_VARS['url'])>0)
		{
			$HTTP_VARS['cache_type'] = ifempty($HTTP_VARS['cache_type'], 'ITEM');
			if($HTTP_VARS['cache_type'] == 'ITEM' || $HTTP_VARS['cache_type'] == 'HTTP')
			{
				// for simplicity sake, we do not ignore expired cached files when displaying, its assumed that
				// some automated process will refresh them as required, and of course if the item is ever refreshed
				// any cached items will be reviewed and recached as required.
				$file_cache_r = fetch_url_file_cache_r($HTTP_VARS['url'], $HTTP_VARS['cache_type'], INCLUDE_EXPIRED);
				if($file_cache_r!==FALSE)
				{
					if($HTTP_VARS['cache_type'] == 'ITEM')
					{
						if(ifempty($HTTP_VARS['op'], 'fullsize') == 'thumbnail')
						{
							$file = file_cache_open_thumbnail_file($file_cache_r);
						}
						
						// fallback on big image
						if(!$file)
						{
							if($file_cache_r['upload_file_ind'] != 'Y')
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
						header("Content-disposition: inline; filename=".$file_cache_r['cache_file']);
						header("Content-type: ".$file_cache_r['content_type']);
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
				http_redirect($HTTP_VARS['url']);
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