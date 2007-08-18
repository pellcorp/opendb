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

define('EXPIRED_ONLY', '3131');
define('EXCLUDE_EXPIRED', '3123');
define('INCLUDE_EXPIRED', '4442');

include_once("./functions/database.php");
include_once("./functions/logging.php");
include_once("./functions/utils.php");
include_once("./functions/file_type.php");
include_once("./functions/fileutils.php");
include_once("./functions/OpenDbSnoopy.class.inc");
include_once('./functions/phpthumb/phpthumb.class.php');
include_once("./functions/item_attribute.php");

function file_cache_get_config($cache_type='HTTP')
{
	if($cache_type == 'HTTP')
	{
		return get_opendb_config_var('http.cache');
	}
	else if($cache_type == 'ITEM')
	{
		return get_opendb_config_var('http.item.cache');
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid cache type specified', array($cache_type));
		return FALSE;
	}
}

/**
	Returns an array which includes fullsize and cached image versions, complete
	with dimensions.  Its quite possible that the dimensions for the fullsize image
	will not be available in which case it should be set in the calling function
	for the popup window size.

	array(
	'thumbnail'=>
	array('url'=>urlOfThumbnail,
	'width'=>widthOfThumbnail,
	'height'=>heightOfThumbnail),
	'fullsize'=>
	array('url'=>urlOfFullsize,
	'width'=>widthOfFullsize,
	'height'=>heightOfFullsize));

	NOTE: There is an assumption that this function is only ever used for IMAGE
	group entries.
	*/
function file_cache_get_image_r($url, $type)
{
	if($type == 'display')
		$thumbnail_size_r = get_opendb_config_var('item_display', 'item_image_size');
	else if($type == 'listing')
		$thumbnail_size_r = get_opendb_config_var('listings', 'item_image_size');
	else if($type == 'site-add')
		$thumbnail_size_r = get_opendb_config_var('item_input.site', 'item_image_size');

	$file_r = array();

	if(strlen($url)>0 && (is_url_absolute($url) || file_exists($url)))
	{
		$file_r['thumbnail']['url'] = 'url.php?url='.urlencode($url).'&op=thumbnail';

		// TODO - decide whether to define this, if the fullsize image is the size of the thumbnail anyway.
		$file_r['fullsize']['url'] = 'url.php?url='.urlencode($url);

		// kludge so that the item display can display the originating URL instead.
		$file_r['url'] = $url;

		$file_cache_r = fetch_url_file_cache_r($url, 'ITEM', INCLUDE_EXPIRED);
		if(is_array($file_cache_r))
		{
			$file = file_cache_get_cache_file($file_cache_r);
			$thumbnail_file = file_cache_get_cache_file_thumbnail($file_cache_r);
		}
		else if(!is_url_absolute($url)) // only pre 1.0 cached files should be matched
		{
			$file = $url;
			$thumbnail_file = $url;
		}

		if($file!==FALSE)
		{
			$size = @getimagesize($file);
			if(is_array($size))
			{
				$file_r['fullsize']['width'] = $size[0];
				$file_r['fullsize']['height'] = $size[1];
			}
		}

		if($thumbnail_file!==FALSE)
		{
			$size = @getimagesize($thumbnail_file);
			if(is_array($size))
			{
				$file_r['thumbnail']['width'] = $size[0];
				$file_r['thumbnail']['height'] = $size[1];
					
				if(is_numeric($thumbnail_size_r['width']) && $thumbnail_size_r['width'] < $file_r['thumbnail']['width'])
				{
					$file_r['thumbnail']['width'] = $thumbnail_size_r['width'];

					// let browser auto dither image
					$file_r['thumbnail']['height'] = NULL;
				}
				else if(is_numeric($thumbnail_size_r['height']) && $thumbnail_size_r['height'] < $file_r['thumbnail']['height'])
				{
					$file_r['thumbnail']['height'] = $thumbnail_size_r['height'];

					// let browser auto dither image
					$file_r['thumbnail']['width'] = NULL;
				}
			}
		}

		if(!is_numeric($file_r['thumbnail']['width']) && !is_numeric($file_r['thumbnail']['height']))
		{
			$file_r['thumbnail']['width'] = $thumbnail_size_r['width'];
			$file_r['thumbnail']['height'] = $thumbnail_size_r['height'];
		}
	}
	else
	{
		$file_r['thumbnail'] = file_cache_get_noimage_r($type);
	}
	return $file_r;
}

/**
	Returns an array describing the image, which must then be generated, the keys will be:
	url
	width - most likely one of these only will be provided, in this case derive the other.
	height
	*/
function file_cache_get_noimage_r($type)
{
	if($type == 'display')
	{
		$src = _theme_image_src(get_opendb_config_var('item_display', 'no_image'));
	}
	else //if($type == 'listing')
	{
		$src = _theme_image_src(get_opendb_config_var('listings', 'no_image'));
	}

	if(is_file($src))
	{
		$size = @getimagesize($src);
		return array('url'=>$src, 'width'=>$size[0], 'height'=>$size[1]);
	}
	else
	{
		return NULL;
	}
}

/**
TODO: If saved image has smaller dimensions than requested thumbnail, there is no need to
save the thumb, both dimensions must be smaller, otherwise the dimension that is bigger, should
be resized to match.
*/
function file_cache_save_thumbnail_file($file_cache_r, &$errors)
{
	$file_type_r = fetch_file_type_r($file_cache_r['content_type']);
	if($file_type_r['thumbnail_support_ind'] == 'Y')
	{
		$directory = file_cache_get_cache_type_directory($file_cache_r['cache_type']);
		if($directory!==FALSE)
		{
			if(is_file($directory.$file_cache_r['cache_file']))
			{
				$phpThumb = new phpThumb();
				
				// prevent issues with safe mode and /tmp directory
				//$phpThumb->config_temp_directory = './itemcache';
				
				$phpThumb->setParameter('config_error_die_on_error', FALSE);
				//$phpThumb->setParameter('config_prefer_imagemagick', FALSE);
				$phpThumb->setParameter('config_allow_src_above_docroot', TRUE);

				// configure the size of the thumbnail.
				if(is_array(get_opendb_config_var('item_display', 'item_image_size')))
				{
					if(is_numeric(get_opendb_config_var('item_display', 'item_image_size', 'width')))
					{
						$phpThumb->setParameter('w', get_opendb_config_var('item_display', 'item_image_size', 'width'));
					}
					else if(is_numeric(get_opendb_config_var('item_display', 'item_image_size', 'height')))
					{
						$phpThumb->setParameter('h', get_opendb_config_var('item_display', 'item_image_size', 'height'));
					}
				}
				else
				{
					$phpThumb->setParameter('h', 100);
				}

				// input and output format should match
				$phpThumb->setParameter('f', $file_type_r['extension']);
				$phpThumb->setParameter('config_output_format', $file_type_r['extension']);

				$phpThumb->setSourceFilename($directory.$file_cache_r['cache_file']);

				// generate & output thumbnail
				if ($phpThumb->GenerateThumbnail() && $phpThumb->RenderToFile($directory.$file_cache_r['cache_file_thumb']))
				{
					opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Thumbnail image saved', array($sequence_number, $cache_type, $file_type_r, $directory.$file_cache_r['cache_file_thumb']));
					return TRUE;
				}
				else
				{
					// do something with debug/error messages
					if(is_not_empty_array($phpThumb->debugmessages))
					$errors = $phpThumb->debugmessages;
					else if(strlen($phpThumb->debugmessages)>0) // single array element
					$errors[] = $phpThumb->debugmessages;

					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, implode(";",$errors), array($file_cache_r, $file_type_r, $directory.$file_cache_r['cache_file_thumb']));
					return FALSE;
				}
			}//if(is_file
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Source image not found', array($file_cache_r, $file_type_r, $directory.$file_cache_r['cache_file']));
				return FALSE;
			}
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Cache directory not found', array($file_cache_r, $file_type_r, $directory));
			return FALSE;
		}
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Thumbnails not supported by image file type', array($file_cache_r, $file_type_r));
		return FALSE;
	}
}

function file_cache_get_cache_type_directory($cache_type='HTTP')
{
	$cache_config_r = file_cache_get_config($cache_type);
	if(is_array($cache_config_r))
	{
		$dir = $cache_config_r['directory'];

		$dir = trim($dir);
		if($dir!='.' && $dir!='..' && is_dir($dir))
		{
			if(!ends_with($dir, '/'))
				$dir .= '/';
				
			return $dir;
		}
	}

	return FALSE;
}

/**
 * This function should only be used where the $file_cache_r record for the
 * sequence number is not already available.
 */
function file_cache_get_cache_file($file_cache_r)
{
	$directory = file_cache_get_cache_type_directory($file_cache_r['cache_type']);

	if(strlen($file_cache_r['cache_file'])>0 && 
			file_exists($directory.$file_cache_r['cache_file']))
	{
		return $directory.$file_cache_r['cache_file'];
	}

	//	else
	return FALSE;
}

function file_cache_get_cache_file_thumbnail($file_cache_r)
{
	$directory = file_cache_get_cache_type_directory($file_cache_r['cache_type']);

	if(strlen($file_cache_r['cache_file_thumb'])>0 && 
			file_exists($directory.$file_cache_r['cache_file_thumb']))
	{
		return $directory.$file_cache_r['cache_file_thumb'];
	}

	//	else
	return FALSE;
}

function file_cache_open_file($file_cache_r)
{
	$file = file_cache_get_cache_file($file_cache_r);
	if($file!==FALSE)
		return fopen($file, 'rb');
	else
		return FALSE;
}

function file_cache_open_thumbnail_file($file_cache_r)
{
	$file = file_cache_get_cache_file_thumbnail($file_cache_r);
	if($file!==FALSE)
		return fopen($file, 'rb');
	else
		return FALSE;
}

/**
 * @param $url
 * @param $cache_type
 * @param $ignore_expired
 * @return unknown
 */
function fetch_url_file_cache_r($url, $cache_type='HTTP', $mode = EXCLUDE_EXPIRED)
{
	// ensure only first 2083 chars are considered.
	$url = addslashes(trim(substr($url, 0, 2083)));

	$query = "SELECT sequence_number, cache_type, url, location, cache_file, cache_file_thumb, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date ".
	"FROM file_cache ".
	"WHERE cache_type = '$cache_type' AND (url = '".$url."' OR (location IS NOT NULL AND location = '".$url."'))";

	if($mode == EXCLUDE_EXPIRED)
	{
		$query .= " AND (expire_date IS NULL OR expire_date > NOW())";
	}

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$record_r = db_fetch_assoc($result);
		db_free_result($result);
		if ($record_r!== FALSE)
		{
			return $record_r;
		}
	}

	//else
	return FALSE;
}

/**
 * Will include expired files.
 */
function fetch_file_cache_rs($cache_type='HTTP', $order_by = NULL, $sortorder = NULL, $start_index=NULL, $items_per_page=NULL)
{
	$query = "SELECT sequence_number, cache_type, url, location, upload_file_ind, cache_file, cache_file_thumb, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date, IF(expire_date<=NOW(),'Y','N') as expired_ind ".
	"FROM file_cache ".
	"WHERE cache_type = '$cache_type' ";

	if(strlen($order_by)>0)
	{
		if(strcasecmp($sortorder, 'DESC')===0)
			$sortorder = 'DESC';
		else
			$sortorder = 'ASC';
			
		$query .= "ORDER BY $order_by $sortorder";
	}

	if(is_numeric($start_index) && is_numeric($items_per_page))
	{
		$query .= ' LIMIT ' .$start_index. ', ' .$items_per_page;
	}

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_cnt($cache_type = 'HTTP')
{
	$query = "SELECT count('X') AS count ".
	"FROM file_cache ".
	"WHERE cache_type = '$cache_type' ";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!==FALSE)
			return $found['count'];
	}

	//else
	return FALSE;
}

/**
	Return a list of cache records which have expired, the
	item in this list will have their cache files refreshed.
	*/
function fetch_file_cache_refresh_rs($cache_type='HTTP', $limit = NULL)
{
	$query = "SELECT sequence_number, cache_type, url, location, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date ".
	"FROM file_cache ".
	"WHERE cache_type = '$cache_type' AND ".
	"upload_file_ind <> 'Y' AND ". // do not refresh uploaded files
	" (expire_date IS NOT NULL AND expire_date <= NOW())";

	if(is_numeric($limit))
		$query .= ' LIMIT 0, '.$limit;

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_refresh_cnt($cache_type = 'HTTP')
{
	$query = "SELECT count('X') AS count ".
	"FROM file_cache ".
	"WHERE cache_type = '$cache_type' AND ".
	"upload_file_ind <> 'Y' AND ". // do not refresh uploaded files
	" (expire_date IS NOT NULL AND expire_date <= NOW())";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!==FALSE)
			return $found['count'];
	}

	//else
	return FALSE;
}

function fetch_file_cache_item_attribute_orphans_cnt()
{
	// this query is not restricted to file_attribute_ind item attributes, but in the rare occurence
	// where this occurs, its probably ok to leave them, in case for instance an attribute is misconfigured,
	// to be non-file attribute ind, when it should be!e
	$query = "SELECT count('x') AS count
	FROM file_cache fc
	LEFT JOIN item_attribute ia ON (ia.attribute_val = fc.url OR ia.attribute_val = fc.location OR 
			fc.url = CONCAT( 'file://opendb/upload/', ia.item_id, '/', ia.instance_no, '/', ia.s_attribute_type, '/', ia.order_no, '/', ia.attribute_no, '/', ia.attribute_val ) )
	WHERE fc.cache_type = 'ITEM' AND ia.s_attribute_type IS NULL";
	
	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!==FALSE)
			return $found['count'];
	}
	
	//else
	return FALSE;
}

/**
	Query the number of file cache records which no longer reference a item attribute
	*/
function fetch_file_cache_item_attribute_orphans_rs()
{
	// this query is not restricted to file_attribute_ind item attributes, but in the rare occurence
	// where this occurs, its probably ok to leave them, in case for instance an attribute is misconfigured,
	// to be non-file attribute ind, when it should be!
	$query = "SELECT fc.sequence_number
	FROM file_cache fc
	LEFT JOIN item_attribute ia ON (ia.attribute_val = fc.url OR ia.attribute_val = fc.location OR 
			fc.url = CONCAT( 'file://opendb/upload/', ia.item_id, '/', ia.instance_no, '/', ia.s_attribute_type, '/', ia.order_no, '/', ia.attribute_no, '/', ia.attribute_val ))
	WHERE fc.cache_type = 'ITEM' AND ia.s_attribute_type IS NULL";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_missing_file_cnt($cache_type='HTTP')
{
	$count = 0;
	$results = fetch_file_cache_rs($cache_type);
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			if(file_cache_get_cache_file($file_cache_r)===FALSE)
			{
				$count++;
			}
		}
		db_free_result($results);
	}

	return $count;
}

function fetch_file_cache_missing_thumbs_cnt($cache_type='HTTP')
{
	$count = 0;
	$results = fetch_file_cache_rs($cache_type);
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			// its not a case of only a thumbnail, if not even the source exists
			if(file_cache_get_cache_file($file_cache_r) !== FALSE &&
			file_cache_get_cache_file_thumbnail($file_cache_r)===FALSE)
			{
				$count++;
			}
		}
		db_free_result($results);
	}

	return $count;
}

/**
	Fetch a result of all file item attributes that do not have a file cache record
*/
function fetch_file_cache_new_item_attribute_rs($limit = NULL)
{
	$query = "SELECT ia.item_id, ia.instance_no, ia.s_attribute_type, ia.order_no, ia.attribute_no, ia.attribute_val
	FROM (s_attribute_type sat, item_attribute ia)
	LEFT JOIN file_cache fc ON (fc.url = ia.attribute_val OR fc.location = ia.attribute_val) AND fc.cache_type = 'ITEM'
	WHERE sat.s_attribute_type = ia.s_attribute_type AND
	sat.file_attribute_ind = 'Y' AND fc.sequence_number IS NULL AND ia.attribute_val LIKE '%tp://%'";

	if(is_numeric($limit))
	{
		$query .= " LIMIT 0,$limit";
	}

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_new_item_attribute_cnt()
{
	$query = "SELECT count('x') AS count
		FROM (s_attribute_type sat, item_attribute ia)
		LEFT JOIN file_cache fc ON (fc.url = ia.attribute_val OR fc.location = ia.attribute_val) AND fc.cache_type = 'ITEM'
		WHERE sat.s_attribute_type = ia.s_attribute_type AND
		sat.file_attribute_ind = 'Y' AND fc.sequence_number IS NULL AND ia.attribute_val LIKE '%tp://%'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!==FALSE)
			return $found['count'];
	}

	//else
	return FALSE;
}

/**
 * @$include_content if TRUE, will request content as well.
 */
function fetch_file_cache_r($sequence_number)
{
	if(is_numeric($sequence_number))
	{
		$query = "SELECT sequence_number, url, location, content_type, cache_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date, cache_file, cache_file_thumb ".
		"FROM file_cache ".
		"WHERE sequence_number = '$sequence_number' AND LENGTH(cache_file) > 0 "; // ignore records where cache_file reference is empty.

		$result = db_query($query);
		if($result && db_num_rows($result)>0)
		{
			$found = db_fetch_assoc($result);
			db_free_result($result);
			return $found;
		}
	}

	//else
	return FALSE;
}

/*
 * File Cache - create cache file

 @param $lock_tables - if TRUE, then the cache table will be locked before proceeding.  If FALSE, its assumed its
 been properly locked by the calling function.

 @param $overwrite_entry - if TRUE, any existing cache entry will be updated and existing cache file overwritten
 with new file / content
 */
function file_cache_insert_file($url, $location, $content_type, $content, $cache_type='HTTP', $overwrite = FALSE)
{
	$cache_config_r = file_cache_get_config($cache_type);
	if(is_array($cache_config_r) && $cache_config_r['enable']!==FALSE)
	{
		// we need to know whether we are overwriting existing URL or not
		$file_cache_r = fetch_url_file_cache_r($url, $cache_type, INCLUDE_EXPIRED);

		// where a record exists, but no cache file, need to resolve a new temporary file name.
		if(is_array($file_cache_r))
		{
			if(!file_cache_get_cache_file($file_cache_r))
			{
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Cache file record exists, but there is no cache file - will recreate', $file_cache_r);

				$file_cache_r['cache_file'] = NULL;

				// delete thumbnail if it exists
				if(!($thumbnail_file = file_cache_get_cache_file_thumbnail($file_cache_r)))
				{
					delete_file($thumbnail_file);
				}

				$file_cache_r['cache_file_thumb'] = NULL;
			}
			else if(!$overwrite)
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'File cache record exists and cannot be overwritten', $file_cache_r);
				return FALSE;
			}
		}
		else
		{
			$file_cache_r['cache_type'] = $cache_type;
		}

		if($cache_type == 'ITEM' && $content == NULL)
		{
			$httpClient =& new OpenDbSnoopy();
			$content = $httpClient->fetchURI($url, FALSE);

			if($content!==FALSE)
			{
				$location = $httpClient->getLocation();
				$content_type = $httpClient->getContentType();
				unset($httpClient);
			}
			else
			{
				unset($httpClient);

				// http client logs error
				return FALSE;
			}
		}

		// where an extension is defined, override specified content type, but only if it maps to a valid one
		if(strlen($file_cache_r['content_type'])==0)
		{
			$extension = get_file_ext($url);
			if(strlen($extension)>0 && ($ext_content_type = fetch_file_type_for_extension($extension))!==FALSE)
				$content_type = $ext_content_type;
			else
				$content_type = validate_content_type($content_type);

			$file_type_r = fetch_file_type_r($content_type);
			if(!is_array($file_type_r))
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Content type not supported', array($url, $location, $content_type, $cache_type, $overwrite));
				return FALSE;
			}

			$file_cache_r['content_type'] = $content_type;
		}

		$directory = file_cache_get_cache_type_directory($cache_type);
		if(!is_array($file_cache_r) || $file_cache_r['cache_file'] == NULL)
		{
			$file_cache_r['cache_file'] = dir_tempnam($directory, strtolower($cache_type));
			if($file_cache_r['cache_file'] === FALSE)
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Cache directory not accessible', array($url, $location, $content_type, $cache_type, $overwrite));
				return FALSE;
			}
		}

		$tmp_location = $directory.$file_cache_r['cache_file'];
		
		$is_upload_file = FALSE;

		$file_location = NULL;
		if(!is_array($content)) // normal content
		{
			$content_length = strlen($content);
			if($content_length > 0)
			{
				$file_cache_r['content_length'] = $content_length;
				if(file_put_contents($tmp_location, $content)!==FALSE)
				{
					$file_location = $tmp_location;
				}
			}
		}
		else if(is_array($content) && isset($content['tmp_name']) && is_uploaded_file($content['tmp_name'])) // upload file
		{
			$content_length = @filesize($content['tmp_name']);
			if($content_length > 0)
			{
				$file_cache_r['content_length'] = $content_length;

				if(copy($content['tmp_name'], $tmp_location)!==FALSE)
				{
					$file_location = $tmp_location;
				}
			}

			$is_upload_file = TRUE;
		}

		if($file_location!=NULL && file_exists($file_location))
		{
			if($file_type_r['thumbnail_support_ind'] == 'Y')
			{
				if(strlen($file_cache_r['cache_file_thumb']) == 0)
				{
					// todo - is it sufficient to do it this way.
					$file_cache_r['cache_file_thumb'] = $file_cache_r['cache_file'].'_T';
				}

				file_cache_save_thumbnail_file($file_cache_r, $errors);
			}

			if(!is_numeric($file_cache_r['sequence_number']))
			{
				$expire_date = (is_numeric($cache_config_r['lifetime'])?"NOW()+ INTERVAL ".$cache_config_r['lifetime']." SECOND":NULL);
				
				$sequence_number = insert_file_cache($cache_type, $url, $location, $is_upload_file, $file_cache_r['content_type'], $file_cache_r['content_length'], $expire_date, $file_cache_r['cache_file'], $file_cache_r['cache_file_thumb']);
				if($sequence_number===FALSE)
				{
					// file record was not saved, so delete file itself.
					delete_file($file_location);

					return FALSE;
				}
			}
			else
			{
				if(update_file_cache($file_cache_r['sequence_number'], $file_cache_r['url'], $file_cache_r['location'], $file_cache_r['content_type'], $file_cache_r['content_length'], $expire_date, $file_cache_r['cache_file'], $file_cache_r['cache_file_thumb'])===FALSE)
				{
					// note do not delete old file here
					return FALSE;
				}
			}
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'File not saved', array($url, $location, $content_type, $cache_type, $overwrite));
			return FALSE;
		}
	}
	else if(!is_array($cache_config_r))
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'No file cache configuration', array($url, $location, $content_type, $cache_type, $overwrite));
		return FALSE;
	}
	else
	{
		// file cache is disabled
		return TRUE;
	}
}

function insert_file_cache($cache_type, $url, $location, $is_upload_file, $content_type, $content_length, $expire_date, $cache_file, $cache_file_thumb)
{
	// do not want location to have a copy of url
	if(strcasecmp($url, $location) === 0)
		$location = NULL;

	$url = addslashes(trim(substr($url, 0, 2083)));

	if($location!=NULL)
		$location = addslashes(trim(substr($location, 0, 2083)));

	// upload files cannot expire
	if($is_upload_file===TRUE)
	{
		$expire_date = NULL;
	}
		
	$query = "INSERT INTO file_cache (cache_type, url, location, upload_file_ind, content_type, content_length, cache_date, expire_date, cache_file, cache_file_thumb)".
	" VALUES ('$cache_type', '$url', ".(strlen($location)>0?"'$location'":"NULL").", '".($is_upload_file?'Y':'N')."', '$content_type', $content_length, NOW(), ".($expire_date!=NULL?$expire_date:"NULL").", '$cache_file', ".($cache_file_thumb!=NULL?"'$cache_file_thumb'":"NULL").")";

	$insert = db_query($query);
	if ($insert && db_affected_rows() > 0)
	{
		$sequence_number = db_insert_id();
		opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($url, $location, $content_type, $content_length, $expire_date, $cache_type, $cache_file, $cache_file_thumb));
		return $sequence_number;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($url, $location, $content_type, $content_length, $expire_date, $cache_type, $cache_file, $cache_file_thumb));
		return FALSE;
	}
}

function update_file_cache($sequence_number, $url, $location, $content_type, $content_length, $expire_date, $cache_file, $cache_file_thumb)
{
	if(is_numeric($sequence_number))
	{
		// do not want location to have a copy of url
		if(strcasecmp($url, $location) === 0)
			$location = NULL;

		$url = addslashes(trim(substr($url, 0, 2083)));

		if($location!=NULL)
			$location = addslashes(trim(substr($location, 0, 2083)));

		$query = "UPDATE file_cache ".
		"SET url = '$url', ".
		"location = ".(strlen($location)>0?"'$location'":"NULL").", ".
		"content_type = '$content_type', ".
		"content_length = $content_length, ".
		"cache_date = NOW(), ".
		"expire_date = ".($expire_date!=NULL?$expire_date:"NULL").", ".
		"cache_file = '$cache_file', ".
		"cache_file_thumb = ".($cache_file_thumb!=NULL?"'$cache_file_thumb'":"NULL")." ".
		"WHERE sequence_number = $sequence_number ";

		$update = db_query($query);
		$rows_affected = db_affected_rows();
		if ($update && $rows_affected !== -1)// We should not treat updates that were not actually updated because value did not change as failures.
		{
			if($rows_affected>0)
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($sequence_number, $url, $location, $content_type, $content_length, $expire_date, $cache_file, $cache_file_thumb));
			return TRUE;
		}
		else
		{
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), array($sequence_number, $url, $location, $content_type, $content_length, $expire_date, $cache_file, $cache_file_thumb));
			return FALSE;
		}
	}
	else
	{
		return FALSE;
	}
}

function delete_file_cache($file_cache_r)
{
	if(($filename = file_cache_get_cache_file($file_cache_r))!==FALSE)
	{
		delete_file($filename);
	}

	// in case thumbnail file is orphaned, delete separately.
	if(($thumbnail_filename = file_cache_get_cache_file_thumbnail($file_cache_r))!==FALSE)
	{
		delete_file($thumbnail_filename);
	}

	$query = "DELETE FROM file_cache WHERE sequence_number = ".$file_cache_r['sequence_number'];

	$delete = db_query($query);
	if ( $delete )// Even if no attributes were deleted, because there were none, this should still return true.
	{
		if(db_affected_rows() > 0)
		{
			opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, $file_cache_r);
		}
		return TRUE;
	}
	else
	{
		opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error(), $file_cache_r);
		return FALSE;
	}
}

function delete_file_cache_rs($file_cache_rs)
{
	$count = 0;
	reset($file_cache_rs);
	while(list(,$file_cache_r) = each($file_cache_rs))
	{
		if(delete_file_cache($file_cache_r)!==FALSE)
		{
			$count++;
		}
	}
	return $count;
}

/**
 * Return number of files deleted or FALSE. Be sure to use ===FALSE, so you do not get
 * confused with successful completion, but no files deleted.
 */
function file_cache_delete_files($cache_type='HTTP', $expired_only = FALSE)
{
	$query = "SELECT sequence_number, cache_type, cache_file, cache_file_thumb FROM file_cache WHERE cache_type = '$cache_type' ";

	if($expired_only == EXPIRED_ONLY)
	{
		$query .= " AND expire_date IS NOT NULL AND expire_date <= NOW()";
	}

	$results = db_query($query);
	if($results)
	{
		$file_cache_rs = array();
		while($file_cache_r = db_fetch_assoc($results))
		{
			$file_cache_rs[] = $file_cache_r;
		}
		db_free_result($results);

		$count = delete_file_cache_rs($file_cache_rs);
		return $count;
	}

	//else
	return 0;
}

/*
 This function does not take into consideration thumbnails, so should be used for HTTP cache only.
 */
function file_cache_fetch_cache_size($cache_type='HTTP')
{
	$query = "SELECT SUM(content_length) AS total_size FROM file_cache WHERE cache_type = '$cache_type'";

	$result = db_query($query);
	if($result && db_num_rows($result)>0)
	{
		$found = db_fetch_assoc($result);
		db_free_result($result);
		if ($found!== FALSE)
		{
			if(is_numeric($found['total_size']))
				return $found['total_size'];
			else
				return 0;
		}
	}

	//else
	return FALSE;
}

function file_cache_delete_orphans($cache_type = 'HTTP')
{
	$results = fetch_file_cache_rs($cache_type);
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			// where file does not exist, delete it.
			if(!file_cache_get_cache_file($file_cache_r))
			{
				delete_file_cache($file_cache_r);
			}
		}
	}

	return TRUE;
}

function file_cache_delete_orphan_item_cache()
{
	$results = fetch_file_cache_item_attribute_orphans_rs();
	if($results)
	{
		while($file_cache_r = db_fetch_assoc($results))
		{
			delete_file_cache($file_cache_r);
		}
		db_free_result($results);
	}
}


?>