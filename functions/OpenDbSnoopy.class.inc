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
include_once("./functions/Snoopy.class.php");
include_once("./functions/filecache.php");
include_once("./functions/logging.php");
include_once("./functions/utils.php");
include_once("./functions/fileutils.php");
include_once("./functions/widgets.php");

/**
* This class does assume that the $HTTP_VARS array has been defined before this
* class is instantiated.
*/
class OpenDbSnoopy extends Snoopy
{
	var $_debugMessages;
	var $_debug;
	var $_file_cache_r;
	var $_file_cache_enabled;

	function OpenDbSnoopy($debug = FALSE)
	{
		// if file cache table is not installed, we cannot use file cache.
		$this->_file_cache_enabled = get_opendb_config_var('http.cache', 'enable');

		//override user agent.
		$this->agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4';
		
		// in how many cases is this going to work?
		$this->passcookies = FALSE;

		$this->_debug = $debug;
		
        $proxy_server_config_r = get_opendb_config_var('http.proxy_server');
		if ( $proxy_server_config_r['enable']==TRUE )
		{
			$this->proxy_host = $proxy_server_config_r['host'];
			$this->proxy_port = $proxy_server_config_r['port'];
			$this->proxy_user = $proxy_server_config_r['userid'];
			$this->proxy_pass = $proxy_server_config_r['password'];
		}
	}

	function getDebugMessagesAsHtml()
	{
		if(is_array($this->_debugMessages))
			return format_error_block($this->_debugMessages, 'information');
		else
			return NULL;
	}
	
	function __debug($method, $message, $detail = NULL)
	{
		if($this->_debug)
		{
			$this->_debugMessages[] = array(error=>"OpenDbSnoopy::$method - $message", detail=>$detail);	
		}
	}
	
	/*
	* @param $url
	* @param $http_cache	If FALSE, will not cache the resource.  Useful for
	* 						images, where we have no interest in caching them,
	* 						as we rely on browser to do this.
	*/
	function &fetchURI($URI, $http_cache=TRUE)
	{
		@set_time_limit(600);
		
		$URI = trim($URI);
		
		$this->__debug('fetchURI', "URI: $URI");
		
		$this->_file_cache_r = NULL;
		
		$overwrite_cache_entry = FALSE;
		
		if($http_cache!==FALSE && $this->_file_cache_enabled)
		{
			// see if we can find the cache file.
			$this->_file_cache_r = fetch_url_file_cache_r($URI, 'HTTP');
			if($this->_file_cache_r!==FALSE)
			{
				$file_location = file_cache_get_cache_file($this->_file_cache_r);
				if($file_location!==FALSE)
				{
					$this->_file_cache_r['content'] = file_get_contents($file_location);
					if(strlen($this->_file_cache_r['content'])==0)
					{
						$this->__debug('fetchURI', 'URL cache invalid');
						
						$overwrite_cache_entry = TRUE;
						unset($this->_file_cache_r);	
					}
				}
				else
				{
					unset($this->_file_cache_r);
				}
			}
		}
			
		if(is_not_empty_array($this->_file_cache_r))
		{
			$this->__debug('fetchURI', 'URL cached');
			return $this->_file_cache_r['content'];
		}
		else
		{
			$this->__debug('fetchURI', 'URL NOT cached');
		
			if($this->fetch($URI) && $this->status >= 200 && $this->status < 300)
			{
				opendb_logger(OPENDB_LOG_INFO, __FILE__, __FUNCTION__, NULL, array($URI));
				
				$this->_file_cache_r['url'] = $URI;
				$this->_file_cache_r['content'] = $this->results;
				
				$this->results = NULL;
		
				if(strlen($this->_file_cache_r['content'])>0)
				{
					$this->__debug('fetchURI', 'URL fetched (Size='.strlen($this->_file_cache_r['content']).')');
			
					// assume a default.
					$this->_file_cache_r['content_type'] = 'text/html';

					if(is_array($this->headers) && count($this->headers)>0)
					{
						for($i=0; $i<count($this->headers); $i++)
						{
							if(preg_match("/^([^:]*):([^$]*)$/i", $this->headers[$i], $matches))
							{
								if(strcasecmp(trim($matches[1]), 'content-type')===0)
								{
									$this->_file_cache_r['content_type'] = trim($matches[2]);
									break;
								}
							}
						}
					}
						
					$this->_file_cache_r['location'] = $this->lastredirectaddr;
					
					if($http_cache!==FALSE && $this->_file_cache_enabled)
					{	
						if(file_cache_insert_file($this->_file_cache_r['url'], $this->_file_cache_r['location'], $this->_file_cache_r['content_type'], $this->_file_cache_r['content'], 'HTTP', $overwrite_cache_entry)!==FALSE)
						{
							$this->__debug('fetchURI', "Added $URI to file cache");
						}
						else
						{
							$this->__debug('fetchURI', "Failed to add $URI to file cache");
						}
					}//if($http_cache!==FALSE && $this->_file_cache_enabled)
				}//if(strlen($_file_cache_r['content'])>0)
				
				return $this->_file_cache_r['content'];
			}
			else
			{
				$this->__debug('fetchURI', "Failed to fetch $URI", ifempty($this->error, 'Status '.$this->status));
				
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, ifempty($this->error, 'Status '.$this->status), array($URI));
				
				return FALSE;
			}
		}
	}
	
	/**
	*/
	function getLocation()
	{
		if(is_not_empty_array($this->_file_cache_r))
		{
			return ifempty(
				$this->_file_cache_r['location'],
				$this->_file_cache_r['url']);
		}
		else
		{
			// no location because no URL has been fetched.
			return NULL;
		}
	}
	
	/**
	Get content of last URL retrieved
	*/
	function &getContent()
	{
		if(is_not_empty_array($this->_file_cache_r))
		    return $this->_file_cache_r['content'];
		else
		    return FALSE;
	}
	
	function getContentType()
	{
		if(is_not_empty_array($this->_file_cache_r))
		    return $this->_file_cache_r['content_type'];
		else
		    return FALSE;
	}
}
?>
