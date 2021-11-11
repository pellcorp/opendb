<?php
/* 	
	OpenDb Media Collector Database
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
define ( 'EXPIRED_ONLY', '3131' );
define ( 'EXCLUDE_EXPIRED', '3123' );
define ( 'INCLUDE_EXPIRED', '4442' );

include_once("./lib/database.php");
include_once("./lib/logging.php");
include_once("./lib/utils.php");
include_once("./lib/file_type.php");
include_once("./lib/fileutils.php");
include_once("./lib/OpenDbSnoopy.class.php");
include_once ('./lib/phpthumb/phpthumb.class.php');
include_once("./lib/item_attribute.php");

function add_url_to_temp_file_cache($url) {
	$key = md5 ( $url );
	register_opendb_session_array_var ( '_OPENDB_TEMP_FILE_CACHE_', $key, $url );
	return $key;
}

function get_url_from_temp_file_cache($key) {
	return get_opendb_session_array_var ( '_OPENDB_TEMP_FILE_CACHE_', $key );
}

function get_item_input_file_upload_directory() {
	$uploadDir = OPENDB_ITEM_UPLOAD_DIRECTORY;
	if ($uploadDir != '.' && $uploadDir != '..' && is_dir ( $uploadDir )) {
		if (ends_with ( $uploadDir, '/' ))
			$uploadDir = substr ( $uploadDir, 0, - 1 );
		
		return $uploadDir;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid Item Input File Upload Directory', array (
				$uploadDir ) );
		return FALSE;
	}
}

function file_cache_get_cache_type_directory($cache_type = 'HTTP') {
	if ($cache_type == 'HTTP')
		$cacheDir = OPENDB_HTTP_CACHE_DIRECTORY;
	else //if($cache_type == 'ITEM')
		$cacheDir = OPENDB_ITEM_CACHE_DIRECTORY;
	
	if ($cacheDir != '.' && $cacheDir != '..' && is_dir ( $cacheDir )) {
		if (ends_with ( $cacheDir, '/' ))
			$cacheDir = substr ( $cacheDir, 0, - 1 );
		
		return $cacheDir;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid Cache directory', array (
				$cache_type,
				$cacheDir ) );
		return FALSE;
	}
}

function file_cache_get_config($cache_type = 'HTTP') {
	if ($cache_type == 'HTTP') {
		$config = get_opendb_config_var ( 'http.cache' );
		$config ['directory'] = file_cache_get_cache_type_directory ( $cache_type );
		return $config;
	} else if ($cache_type == 'ITEM') {
		$config = get_opendb_config_var ( 'http.item.cache' );
		$config ['directory'] = file_cache_get_cache_type_directory ( $cache_type );
		return $config;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Invalid cache type specified', array (
				$cache_type ) );
		return FALSE;
	}
}

/**
 * For the moment this is a relative URL, but clients of this
 * function should not assume this.  In other words do not use
 * to get a file system reference to an upload file.
 *
 * @param unknown_type $filename
 * @return unknown
 */
function get_item_input_file_upload_url($filename) {
	$filename = basename ( $filename );
	if (strlen ( $filename ) > 0) {
		$uploadDir = get_item_input_file_upload_directory ();
		if ($uploadDir != FALSE) {
			$url = $uploadDir . '/' . $filename;
			if (file_exists ( $url )) {
				return $url;
			}
		}
	}
	
	//else
	return FALSE;
}

function filecache_generate_cache_filename($file_cache_r, $thumbnail = FALSE) {
	$prefix = strtolower( $file_cache_r['cache_type'] );
	if ($thumbnail)
		$prefix .= 'Thumb';
	
	$randomNum = $file_cache_r['sequence_number'] . '_' . generate_random_num ();
	$extension = $file_cache_r['extension'];
	
	return $prefix . $randomNum . '.' . $extension;
}

function save_uploaded_file($tmpFile, $name) {
	$uploadDir = get_item_input_file_upload_directory ();
	if ($uploadDir !== FALSE) {
		if (is_writable ( $uploadDir )) {
			if (is_uploaded_file ( $tmpFile )) {
				$newFile = $uploadDir . '/' . $name;
				
				if (@copy ( $tmpFile, $newFile )) {
					opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
							$tmpFile,
							$newFile ) );
					return TRUE;
				} else {
					opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Item Input Upload file not written', array (
							$tmpFile,
							$newFile ) );
					return FALSE;
				}
			} else {
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Item Input Upload file is not valid', array (
						$tmpFile ) );
				return FALSE;
			}
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Item Input Upload Directory is not writable', array (
					$uploadDir ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
 *	Returns an array which includes fullsize and cached image versions, complete
 *	with dimensions.  Its quite possible that the dimensions for the fullsize image
 *	will not be available in which case it should be set in the calling function
 *	for the popup window size.
 *
 *	array('thumbnail'=>
 *	          array('url'=>urlOfThumbnail,
 *	                'width'=>widthOfThumbnail,
 *	                'height'=>heightOfThumbnail),
 *	      'fullsize'=>
 *	          array('url'=>urlOfFullsize,
 *	                'width'=>widthOfFullsize,
 *	                'height'=>heightOfFullsize));
 *
 *	NOTE: There is an assumption that this function is only ever used for IMAGE
 *	group entries.
 */
function file_cache_get_image_r($url, $type) {
	if ($type == 'display')
		$thumbnail_size_r = get_opendb_config_var ( 'item_display', 'item_image_size' );
	else if ($type == 'listing')
		$thumbnail_size_r = get_opendb_config_var ( 'listings', 'item_image_size' );
	else if ($type == 'site-add')
		$thumbnail_size_r = get_opendb_config_var ( 'item_input.site', 'item_image_size' );
	
	$file_r = array ();
	$uploadUrl = FALSE;
	$file = FALSE;
	$thumbnail_file = FALSE;
	
	if (strlen ( $url ) > 0) {
		$file_cache_r = fetch_url_file_cache_r ( $url, 'ITEM', INCLUDE_EXPIRED );
		if ($file_cache_r !== FALSE) {
			$file_r ['fullsize'] ['url'] = 'url.php?id=' . $file_cache_r ['sequence_number'];
			$file_r ['thumbnail'] ['url'] = $file_r ['fullsize'] ['url'] . '&op=thumbnail';
			
			if ($file_cache_r ['upload_file_ind'] == 'Y') {
				$file_r ['url'] = get_item_input_file_upload_url ( $url );
			} else {
				$file_r ['url'] = $url;
			}
		} else if (is_url_absolute ( $url )) {
			$tmpId = add_url_to_temp_file_cache ( $url );
			$file_r ['fullsize'] ['url'] = 'url.php?tmpId=' . $tmpId;
			$file_r ['thumbnail'] ['url'] = $file_r ['fullsize'] ['url'];
			$file_r ['url'] = $url;
		} else if (($uploadUrl = get_item_input_file_upload_url ( $url )) !== FALSE) {
			$file_r ['fullsize'] ['url'] = $uploadUrl;
			$file_r ['thumbnail'] ['url'] = $uploadUrl;
			$file_r ['url'] = $uploadUrl;
		}

		if ($file_cache_r !== FALSE) {
			$file = file_cache_get_cache_file ( $file_cache_r );
			$thumbnail_file = file_cache_get_cache_file_thumbnail ( $file_cache_r );
		} else if ($uploadUrl) {
			$file = $uploadUrl;
			$thumbnail_file = $uploadUrl;
		}

		// defaults
		$file_r ['fullsize'] ['width'] = '400';
		$file_r ['fullsize'] ['height'] = '300';

		if ($file) {
			$size = @getimagesize ( $file );
			
			if (is_array ( $size ) && $size [0] > 0 && $size [1] > 0) {
				$file_r ['fullsize'] ['width'] = $size [0];
				$file_r ['fullsize'] ['height'] = $size [1];
			}
		}

		if ($thumbnail_file) {
			$size = @getimagesize ( $thumbnail_file );
			if (is_array( $size )) {
				$file_r['thumbnail']['width'] = $size[0];
				$file_r['thumbnail']['height'] = $size[1];
				
				if (is_numeric( $thumbnail_size_r['width'] ?? "") && $thumbnail_size_r['width'] < $file_r['thumbnail']['width']) {
					$file_r['thumbnail']['width'] = $thumbnail_size_r['width'];
					
					// let browser auto dither image
					$file_r['thumbnail']['height'] = NULL;
				} else if (is_numeric( $thumbnail_size_r['height'] ) && $thumbnail_size_r['height'] < $file_r['thumbnail']['height']) {
					$file_r['thumbnail']['height'] = $thumbnail_size_r['height'];
					
					// let browser auto dither image
					$file_r['thumbnail']['width'] = NULL;
				}
			}
		}
		
		if (is_numeric($thumbnail_size_r ['width'] ?? ''))
			$file_r ['thumbnail'] ['width'] = $thumbnail_size_r ['width'];
		if (is_numeric($thumbnail_size_r ['height'] ?? ''))
			$file_r ['thumbnail'] ['height'] = $thumbnail_size_r ['height'];
		
		return $file_r;
	}
	
	// else
	$file_r ['thumbnail'] = file_cache_get_noimage_r ( $type );
	return $file_r;
}

/**
	Returns an array describing the image, which must then be generated, the keys will be:
	url
	width - most likely one of these only will be provided, in this case derive the other.
	height
	*/
function file_cache_get_noimage_r($type) {
	if ($type == 'display') {
		$src = theme_image_src ( get_opendb_config_var ( 'item_display', 'no_image' ) );
	} else {	//if($type == 'listing')
		$src = theme_image_src ( get_opendb_config_var ( 'listings', 'no_image' ) );
	}
	
	if (is_file ( $src )) {
		$size = @getimagesize ( $src );
		return array (
				'url' => $src,
				'width' => $size [0],
				'height' => $size [1] );
	} else {
		return NULL;
	}
}

function file_cache_save_thumbnail_file($file_cache_r, &$errors) {
	$file_type_r = fetch_file_type_r ( $file_cache_r ['content_type'] );
	if ($file_type_r ['thumbnail_support_ind'] == 'Y') {
		$sourceFile = file_cache_get_cache_file ( $file_cache_r );
		if ($sourceFile !== FALSE) {
			$phpThumb = new phpThumb ();
			
			// prevent issues with safe mode and /tmp directory
			//$phpThumb->setParameter('config_cache_directory', realpath('./itemcache'));
			

			$phpThumb->setParameter ( 'config_error_die_on_error', FALSE );
			//$phpThumb->setParameter('config_prefer_imagemagick', FALSE);
			$phpThumb->setParameter ( 'config_allow_src_above_docroot', TRUE );
			
			// configure the size of the thumbnail.
			if (is_array ( get_opendb_config_var ( 'item_display', 'item_image_size' ) )) {
				if (is_numeric ( get_opendb_config_var ( 'item_display', 'item_image_size', 'width' ) )) {
					$phpThumb->setParameter ( 'w', get_opendb_config_var ( 'item_display', 'item_image_size', 'width' ) );
				} else if (is_numeric ( get_opendb_config_var ( 'item_display', 'item_image_size', 'height' ) )) {
					$phpThumb->setParameter ( 'h', get_opendb_config_var ( 'item_display', 'item_image_size', 'height' ) );
				}
			} else {
				$phpThumb->setParameter ( 'h', 100 );
			}
			
			// input and output format should match
			$phpThumb->setParameter ( 'f', $file_type_r ['extension'] );
			$phpThumb->setParameter ( 'config_output_format', $file_type_r ['extension'] );
			
			$phpThumb->setSourceFilename ( realpath ( $sourceFile ) );
			
			$directory = realpath ( file_cache_get_cache_type_directory ( $file_cache_r ['cache_type'] ) );
			
			$thumbnailFile = $directory . '/' . $file_cache_r ['cache_file_thumb'];
			if ($phpThumb->GenerateThumbnail () && $phpThumb->RenderToFile ( $thumbnailFile )) {
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Thumbnail image saved', array (
						$sequence_number,
						$cache_type,
						$file_type_r,
						$thumbnailFile ) );
				return TRUE;
			} else {
				// do something with debug/error messages
				if (is_not_empty_array ( $phpThumb->debugmessages ))
					$errors = $phpThumb->debugmessages;
				else if (strlen ( $phpThumb->debugmessages ) > 0) // single array element
					$errors [] = $phpThumb->debugmessages;
				
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, implode ( ";", $errors ), array (
						$file_cache_r,
						$file_type_r,
						$file_cache_r ['cache_file_thumb'] ) );
				return FALSE;
			}
		} else {		//if(is_file
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Source image not found', array (
					$file_cache_r,
					$file_type_r,
					$sourceFile ) );
			return FALSE;
		}
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Thumbnails not supported by image file type', array (
				$file_cache_r,
				$file_type_r ) );
		return FALSE;
	}
}

function file_cache_get_cache_file($file_cache_r) {
	if ($file_cache_r ['upload_file_ind'] == 'Y')
		$directory = get_item_input_file_upload_directory ();
	else
		$directory = file_cache_get_cache_type_directory ( $file_cache_r ['cache_type'] );
	
	if (strlen ( $file_cache_r ['cache_file'] ) > 0 && is_file ( $directory . '/' . $file_cache_r ['cache_file'] )) {
		return $directory . '/' . $file_cache_r ['cache_file'];
	}
	
	//	else
	return FALSE;
}

function file_cache_get_cache_file_thumbnail($file_cache_r) {
	$directory = file_cache_get_cache_type_directory ( $file_cache_r ['cache_type'] );
	
	if (strlen ( $file_cache_r ['cache_file_thumb'] ) > 0 && is_file ( $directory . '/' . $file_cache_r ['cache_file_thumb'] )) {
		return $directory . '/' . $file_cache_r ['cache_file_thumb'];
	}
	
	//	else
	return FALSE;
}

function file_cache_open_file($file_cache_r) {
	$file = file_cache_get_cache_file ( $file_cache_r );
	if ($file !== FALSE)
		return fopen ( $file, 'rb' );
	else
		return FALSE;
}

function file_cache_open_thumbnail_file($file_cache_r) {
	$file = file_cache_get_cache_file_thumbnail ( $file_cache_r );
	if ($file !== FALSE)
		return fopen ( $file, 'rb' );
	else
		return FALSE;
}

/**
 * @param $url
 * @param $cache_type
 * @param $ignore_expired
 * @return unknown
 */
function fetch_url_file_cache_r($url, $cache_type = 'HTTP', $mode = EXCLUDE_EXPIRED) {
	// ensure only first 2083 chars are considered.
	$url = addslashes ( trim ( substr ( $url, 0, 2083 ) ) );
	
	$query = "SELECT sequence_number, upload_file_ind, cache_type, url, location, cache_file, cache_file_thumb, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date " . "FROM file_cache " . "WHERE cache_type = '$cache_type' AND url = '$url'";
	
	if ($mode == EXCLUDE_EXPIRED) {
		$query .= " AND (expire_date IS NULL OR expire_date > NOW())";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$record_r = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($record_r !== FALSE) {
			return $record_r;
		}
	}
	
	//else
	return FALSE;
}

/**
 * Will include expired files.
 */
function fetch_file_cache_rs($cache_type = 'HTTP', $order_by = NULL, $sortorder = NULL, $start_index = NULL, $items_per_page = NULL) {
	$query = "SELECT sequence_number, cache_type, url, location, upload_file_ind, cache_file, cache_file_thumb, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date, IF(expire_date<=NOW(),'Y','N') as expired_ind " . "FROM file_cache " . "WHERE cache_type = '$cache_type' ";
	
	if (strlen ( $order_by ) > 0) {
		if (strcasecmp ( $sortorder, 'DESC' ) === 0)
			$sortorder = 'DESC';
		else
			$sortorder = 'ASC';
		
		$query .= "ORDER BY $order_by $sortorder";
	}
	
	if (is_numeric ( $start_index ) && is_numeric ( $items_per_page )) {
		$query .= ' LIMIT ' . $start_index . ', ' . $items_per_page;
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_cnt($cache_type = 'HTTP') {
	$query = "SELECT count('X') AS count " . "FROM file_cache " . "WHERE cache_type = '$cache_type' ";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
	Return a list of cache records which have expired, the
	item in this list will have their cache files refreshed.
	*/
function fetch_file_cache_refresh_rs($cache_type = 'HTTP', $limit = NULL) {
	$query = "SELECT sequence_number, cache_type, url, location, content_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date " . "FROM file_cache " . "WHERE cache_type = '$cache_type' AND " . "upload_file_ind <> 'Y' AND " . 	// do not refresh uploaded files
			" (expire_date IS NOT NULL AND expire_date <= NOW())";
	
	if (is_numeric ( $limit ))
		$query .= ' LIMIT 0, ' . $limit;
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_refresh_cnt($cache_type = 'HTTP') {
	$query = "SELECT count('X') AS count " . "FROM file_cache " . "WHERE cache_type = '$cache_type' AND " . "upload_file_ind <> 'Y' AND " . 	// do not refresh uploaded files
			" (expire_date IS NOT NULL AND expire_date <= NOW())";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

function fetch_file_cache_item_attribute_orphans_cnt() {
	$query = "SELECT COUNT(*) AS count
	FROM file_cache fc
	LEFT JOIN item_attribute ia ON ia.attribute_val = fc.url
	WHERE fc.cache_type = 'ITEM' AND ia.s_attribute_type IS NULL";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
 * Only deleting orphans for item cache 
 *
 * @return unknown
 */
function fetch_file_cache_item_attribute_orphans_rs() {
	$query = "SELECT fc.sequence_number
	FROM file_cache fc
	LEFT JOIN item_attribute ia ON ia.attribute_val = fc.url
	WHERE fc.cache_type = 'ITEM' AND ia.s_attribute_type IS NULL";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_missing_file_cnt($cache_type = 'HTTP') {
	$count = 0;
	$results = fetch_file_cache_rs ( $cache_type );
	if ($results) {
		while ( $file_cache_r = db_fetch_assoc ( $results ) ) {
			if (file_cache_get_cache_file ( $file_cache_r ) === FALSE) {
				$count ++;
			}
		}
		db_free_result ( $results );
	}
	
	return $count;
}

function fetch_file_cache_missing_thumbs_cnt($cache_type = 'HTTP') {
	$count = 0;
	$results = fetch_file_cache_rs ( $cache_type );
	if ($results) {
		while ( $file_cache_r = db_fetch_assoc ( $results ) ) {
			if (file_cache_get_cache_file ( $file_cache_r ) !== FALSE && file_cache_get_cache_file_thumbnail ( $file_cache_r ) === FALSE) {
				$count ++;
			}
		}
		db_free_result ( $results );
	}
	
	return $count;
}

/**
	Fetch a result of all file item attributes that do not have a file cache record
*/
function fetch_file_cache_new_item_attribute_rs($limit = NULL) {
	$query = "SELECT ia.item_id, ia.instance_no, ia.s_attribute_type, ia.order_no, ia.attribute_no, ia.attribute_val
	FROM (s_attribute_type sat, item_attribute ia)
	LEFT JOIN file_cache fc ON fc.url = ia.attribute_val AND fc.cache_type = 'ITEM'
	WHERE sat.s_attribute_type = ia.s_attribute_type AND
	sat.file_attribute_ind = 'Y' AND fc.sequence_number IS NULL";
	
	if (is_numeric ( $limit )) {
		$query .= " LIMIT 0,$limit";
	}
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0)
		return $result;
	else
		return FALSE;
}

function fetch_file_cache_new_item_attribute_cnt() {
	$query = "SELECT count('x') AS count
		FROM (s_attribute_type sat, item_attribute ia)
		LEFT JOIN file_cache fc ON fc.url = ia.attribute_val AND fc.cache_type = 'ITEM'
		WHERE sat.s_attribute_type = ia.s_attribute_type AND
		sat.file_attribute_ind = 'Y' AND fc.sequence_number IS NULL";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE)
			return $found ['count'];
	}
	
	//else
	return FALSE;
}

/**
 * @$include_content if TRUE, will request content as well.
 */
function fetch_file_cache_r($sequence_number) {
	if (is_numeric ( $sequence_number )) {
		$query = "SELECT sequence_number, url, upload_file_ind, location, content_type, cache_type, content_length, UNIX_TIMESTAMP(cache_date) as cache_date, IF(expire_date IS NOT NULL, UNIX_TIMESTAMP(expire_date), NULL) AS expire_date, cache_file, cache_file_thumb " . "FROM file_cache " . "WHERE sequence_number = '$sequence_number' AND LENGTH(cache_file) > 0 "; // ignore records where cache_file reference is empty.
		

		$result = db_query ( $query );
		if ($result && db_num_rows ( $result ) > 0) {
			$found = db_fetch_assoc ( $result );
			db_free_result ( $result );
			return $found;
		}
	}
	
	//else
	return FALSE;
}

/*
 * File Cache - create cache file

 @param $overwrite - if TRUE, any existing cache entry will be updated and existing cache file overwritten
 with new file / content
 */
function file_cache_insert_file($url, $location, $content_type, $content, $cache_type = 'HTTP', $overwrite = FALSE) {
	$cache_config_r = file_cache_get_config ( $cache_type );
	if (is_array ( $cache_config_r ) && $cache_config_r ['enable'] !== FALSE) {
		$directory = $cache_config_r ['directory'];
		if ($directory === FALSE) {
			return FALSE;
		}
		
		// we need to know whether we are overwriting existing URL or not
		$file_cache_r = fetch_url_file_cache_r ( $url, $cache_type, INCLUDE_EXPIRED );
		if (is_array ( $file_cache_r )) {
			if (! file_cache_get_cache_file ( $file_cache_r )) {
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, 'Cache file record exists, but there is no cache file - will recreate', $file_cache_r );
				
				$file_cache_r ['cache_file'] = NULL;
				
				// delete thumbnail if it exists
				if (! ($thumbnail_file = file_cache_get_cache_file_thumbnail ( $file_cache_r ))) {
					delete_file ( $thumbnail_file );
				}
				
				$file_cache_r ['cache_file_thumb'] = NULL;
			} else if (! $overwrite) {
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'File cache record exists and cannot be overwritten', $file_cache_r );
				return FALSE;
			}
		} else {
			$file_cache_r ['cache_type'] = $cache_type;
		}
		
		if ($content == NULL && is_url_absolute ( $url )) {
			$httpClient = new OpenDbSnoopy ();
			$content = $httpClient->fetchURI ( $url, FALSE );
			
			if ($content !== FALSE) {
				$location = $httpClient->getLocation ();
				$content_type = $httpClient->getContentType ();
				unset ( $httpClient );
			} else {
				unset ( $httpClient );
				
				// http client logs error
				return FALSE;
			}
		}
		
		$file_cache_r ['url'] = ifempty ( $url, $file_cache_r ['url'] );
		$file_cache_r ['location'] = ifempty ( $location, $file_cache_r ['location'] );
		$file_cache_r ['content_type'] = validate_content_type ( ifempty ( $content_type, $file_cache_r ['content_type'] ) );
		
		$thumbnail_support = FALSE;
		if (strlen ( $file_cache_r ['content_type'] ) > 0) {
			$file_type_r = fetch_file_type_r ( $file_cache_r ['content_type'] );
			$file_cache_r ['extension'] = $file_type_r ['extension'];
			$thumbnail_support = ($file_type_r ['thumbnail_support_ind'] == 'Y');
		} else {
			$extension = get_file_ext ( $file_cache_r ['url'] );
			
			if (strlen ( $extension ) > 0 && ($ext_content_type = fetch_file_type_for_extension ( $extension )) !== FALSE)
				$content_type = $ext_content_type;
			else
				$content_type = validate_content_type ( $content_type );
			
			$file_type_r = fetch_file_type_r ( $content_type );
			if (! is_array ( $file_type_r )) {
				opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Content type not supported', array (
						$url,
						$location,
						$content_type,
						$cache_type,
						$overwrite ) );
				return FALSE;
			}
			
			$file_cache_r ['content_type'] = $content_type;
			$file_cache_r ['extension'] = $file_type_r ['extension'];
			$thumbnail_support = ($file_type_r ['thumbnail_support_ind'] == 'Y');
		}
		
		$file_cache_r ['content_length'] = 0;
		
		if (($uploadFile = get_item_input_file_upload_url ( $url )) !== FALSE) {
			$file_cache_r ['content_length'] = @filesize ( $uploadFile );
			$file_cache_r ['cache_file'] = basename ( $uploadFile );
			$file_cache_r ['upload_file_ind'] = 'Y';
		} else if (strlen ( $content ) > 0) {
			$file_cache_r ['content_length'] = strlen ( $content );
			$file_cache_r ['upload_file_ind'] = 'N';
		}
		
		if ($file_cache_r ['content_length'] > 0) {
			if (! is_numeric ( $file_cache_r ['sequence_number'] )) {
				$file_cache_r ['sequence_number'] = insert_file_cache ( $cache_type, $file_cache_r ['upload_file_ind'], $file_cache_r ['url'], $file_cache_r ['location'], $file_cache_r ['content_type'] );
				if ($file_cache_r ['sequence_number'] === FALSE)
					return FALSE;
			}
			
			if ($file_cache_r ['cache_file'] == NULL) {
				$file_cache_r ['cache_file'] = filecache_generate_cache_filename ( $file_cache_r );
			}
			
			if ($content != NULL) {
				$directory = file_cache_get_cache_type_directory ( $file_cache_r ['cache_type'] );
				$cacheFile = $directory . '/' . $file_cache_r ['cache_file'];
				
				if (! file_put_contents ( $cacheFile, $content ) !== FALSE) {
					opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Cache file not written', array (
							$cacheFile ) );
					return FALSE;
				}
			}
			
			if ($thumbnail_support) {
				if (strlen ( $file_cache_r ['cache_file_thumb'] ) == 0) {
					$file_cache_r ['cache_file_thumb'] = filecache_generate_cache_filename ( $file_cache_r, TRUE );
				}
			}
			
			if ($file_cache_r ['upload_file_ind'] != 'Y')
				$expire_date = (is_numeric ( $cache_config_r ['lifetime'] ) ? "NOW()+ INTERVAL " . $cache_config_r ['lifetime'] . " SECOND" : NULL);
			else
				$expire_date = NULL; // do not expire uploaded file records.
			

			if (! update_file_cache ( $file_cache_r ['sequence_number'], $file_cache_r ['content_length'], $expire_date, $file_cache_r ['cache_file'], $file_cache_r ['cache_file_thumb'] )) {
				return FALSE;
			}
			
			if ($thumbnail_support) {
				file_cache_save_thumbnail_file ( $file_cache_r, $errors );
			}
			
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'File content length is zero', array (
					$url,
					$location,
					$content_type,
					$cache_type,
					$overwrite ) );
			return FALSE;
		}
	} else if (! is_array ( $cache_config_r )) {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'No file cache configuration', array (
				$url,
				$location,
				$content_type,
				$cache_type,
				$overwrite ) );
		return FALSE;
	} else {
		// file cache is disabled
		return TRUE;
	}
}

/**
 * this function is only here to get a new sequence number record for allocation of unique filename, otherwise
 * it does little useful.
 *
 * @param unknown_type $cache_type
 * @param unknown_type $file_upload_ind
 * @return unknown
 */
function insert_file_cache($cache_type, $file_upload_ind, $url, $location, $content_type) {
	$file_upload_ind = validate_ind_column ( $file_upload_ind );
	
	// do not want location to have a copy of url
	if (strcasecmp ( $url, $location ) === 0)
		$location = NULL;
	
	$url = addslashes ( trim ( substr ( $url, 0, 2083 ) ) );
	
	if ($location != NULL)
		$location = addslashes ( trim ( substr ( $location, 0, 2083 ) ) );
	
	$query = "INSERT INTO file_cache (cache_type, upload_file_ind, url, location, content_type, cache_date)" . " VALUES ('$cache_type', '$file_upload_ind', '$url', " . (strlen ( $location ) > 0 ? "'$location'" : "NULL") . ", '$content_type', NOW())";
	
	$insert = db_query ( $query );
	if ($insert && db_affected_rows () > 0) {
		$sequence_number = db_insert_id ();
		opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
				$cache_type,
				$file_upload_ind,
				$url,
				$location,
				$content_type ) );
		return $sequence_number;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
				$cache_type,
				$file_upload_ind,
				$url,
				$location,
				$content_type ) );
		return FALSE;
	}
}

function update_file_cache($sequence_number, $content_length, $expire_date, $cache_file, $cache_file_thumb) {
	if (is_numeric ( $sequence_number )) {
		$query = "UPDATE file_cache " . "SET content_length = $content_length, " . "cache_date = NOW(), " . "expire_date = " . ($expire_date != NULL ? $expire_date : "NULL") . ", " . "cache_file = '$cache_file', " . "cache_file_thumb = " . ($cache_file_thumb != NULL ? "'$cache_file_thumb'" : "NULL") . " " . "WHERE sequence_number = $sequence_number ";
		
		$update = db_query ( $query );
		$rows_affected = db_affected_rows ();
		if ($update && $rows_affected !== - 1) {// We should not treat updates that were not actually updated because value did not change as failures.
			if ($rows_affected > 0)
				opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array (
						$sequence_number,
						$content_length,
						$expire_date,
						$cache_file,
						$cache_file_thumb ) );
			return TRUE;
		} else {
			opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), array (
					$sequence_number,
					$content_length,
					$expire_date,
					$cache_file,
					$cache_file_thumb ) );
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function delete_file_cache($file_cache_r) {
	if (($filename = file_cache_get_cache_file ( $file_cache_r )) !== FALSE) {
		delete_file ( $filename );
	}
	
	// in case thumbnail file is orphaned, delete separately.
	if (($thumbnail_filename = file_cache_get_cache_file_thumbnail ( $file_cache_r )) !== FALSE) {
		delete_file ( $thumbnail_filename );
	}
	
	$query = "DELETE FROM file_cache WHERE sequence_number = " . $file_cache_r ['sequence_number'];
	
	$delete = db_query ( $query );
	if ($delete) {	// Even if no attributes were deleted, because there were none, this should still return true.
		if (db_affected_rows () > 0) {
			opendb_logger ( OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, $file_cache_r );
		}
		return TRUE;
	} else {
		opendb_logger ( OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error (), $file_cache_r );
		return FALSE;
	}
}

function delete_file_cache_rs($file_cache_rs) {
	$count = 0;
	reset ( $file_cache_rs );
	foreach ( $file_cache_rs as $file_cache_r ) {
		if (delete_file_cache ( $file_cache_r ) !== FALSE) {
			$count ++;
		}
	}
	return $count;
}

/**
 * Return number of files deleted or FALSE. Be sure to use ===FALSE, so you do not get
 * confused with successful completion, but no files deleted.
 */
function file_cache_delete_files($cache_type = 'HTTP', $expired_only = FALSE) {
	$query = "SELECT sequence_number, cache_type, cache_file, cache_file_thumb FROM file_cache WHERE cache_type = '$cache_type' ";
	
	if ($expired_only == EXPIRED_ONLY) {
		$query .= " AND expire_date IS NOT NULL AND expire_date <= NOW()";
	}
	
	$results = db_query ( $query );
	if ($results) {
		$file_cache_rs = array ();
		while ( $file_cache_r = db_fetch_assoc ( $results ) ) {
			$file_cache_rs [] = $file_cache_r;
		}
		db_free_result ( $results );
		
		$count = delete_file_cache_rs ( $file_cache_rs );
		return $count;
	}
	
	//else
	return 0;
}

/*
 This function does not take into consideration thumbnails, so should be used for HTTP cache only.
 */
function file_cache_fetch_cache_size($cache_type = 'HTTP') {
	$query = "SELECT SUM(content_length) AS total_size FROM file_cache WHERE cache_type = '$cache_type'";
	
	$result = db_query ( $query );
	if ($result && db_num_rows ( $result ) > 0) {
		$found = db_fetch_assoc ( $result );
		db_free_result ( $result );
		if ($found !== FALSE) {
			if (is_numeric ( $found ['total_size'] ))
				return $found ['total_size'];
			else
				return 0;
		}
	}
	
	//else
	return FALSE;
}

function file_cache_delete_orphans($cache_type = 'HTTP') {
	$results = fetch_file_cache_rs ( $cache_type );
	if ($results) {
		while ( $file_cache_r = db_fetch_assoc ( $results ) ) {
			// where file does not exist, delete it.
			if (! file_cache_get_cache_file ( $file_cache_r )) {
				delete_file_cache ( $file_cache_r );
			}
		}
	}
	
	return TRUE;
}

function file_cache_delete_orphan_item_cache() {
	$results = fetch_file_cache_item_attribute_orphans_rs ();
	if ($results) {
		while ( $file_cache_r = db_fetch_assoc ( $results ) ) {
			delete_file_cache ( $file_cache_r );
		}
		db_free_result ( $results );
	}
}
?>
