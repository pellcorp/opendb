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
include_once("./functions/fileutils.php");
include_once("./functions/logging.php");
include_once("./functions/widgets.php");
include_once("./functions/DocTypeNameSpaceXMLParser.class.php");
include_once("./functions/XMLImportPluginHandler.class.php");
include_once("./functions/RowImportPluginHandler.class.php");

function is_import_plugin(&$plugin)
{
	$found = FALSE;
	if ($handle = opendir('./import/'))
	{
    	while (($file = readdir($handle))) 
		{
			if(strcasecmp($file, $plugin.'.php') === 0)
        	{
        		$found = TRUE;
				if (preg_match("/(.*).php$/",$file,$regs))
				{
					$plugin = $regs[1];
				}
				break;	
        	}
	    }
    	closedir($handle);
	}
	
	return $found;
}

/*
* Return the name of the plugin minus './import/' prefix and '.php' extension,
* or FALSE if no plugin with doctype is found.  This will only ever return
* the first plugin encountered which supports the doctype.
*/
function &get_xml_import_plugin($doctype, $namespace)
{
	$handle=opendir('./import');
	while ($file = readdir($handle))
    {
		// Ensure valid plugin name.
		if ( !preg_match("/^\./",$file) && preg_match("/(.*).php$/",$file,$regs))
		{
			include('./import/'.$regs[1].'.php');
			$importPlugin = new $regs[1];
			
			if(strcasecmp(get_class($importPlugin), $regs[1])===0)
			{			
				if($importPlugin->get_plugin_type() == 'xml')
				{
					if($importPlugin->is_doctype_supported($doctype))
					{
						if(!method_exists($importPlugin, 'is_namespace_supported') || 
								$importPlugin->is_namespace_supported($namespace))
						{
							return $importPlugin;
						}
					}
				}
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Import class name \''.$regs[1].'\' is different to filename.', array($doctype));
			}
		}
	}
	closedir($handle);
	
	//else
	return NULL;
}

/**
*/
function &get_extension_import_plugin($extension)
{
	$handle=opendir('./import');
	while ($file = readdir($handle))
    {
		// Ensure valid plugin name.
		if ( !preg_match("/^\./",$file) && preg_match("/(.*).php$/",$file,$regs))
		{
			include('./import/'.$regs[1].'.php');
			$importPlugin = new $regs[1];
			
			if(strcasecmp(get_class($importPlugin), $regs[1])===0)
			{
				if($importPlugin->get_plugin_type() == 'row')
				{
					if($importPlugin->is_extension_supported($extension))
					{
						return $importPlugin;
					}
				}
			}
			else
			{
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Import class name is different to filename.', array($extension, $regs[1], get_class($importPlugin)));
			}
		}
	}
	closedir($handle);
	
	//else
	return NULL;
}

function &get_import_plugin($pluginName) {
	if(is_import_plugin($pluginName)) {
		include_once("./import/".$pluginName.".php");
		$importPlugin = new $pluginName();
		return $importPlugin;
	} else {
		return NULL;
	}
}

/**
	Will return the plugin to use, based on the $http_post_uploadfile_r['name']
	and/or the start of the file itself (available via: $http_post_uploadfile_r['tmp_name'])
*/
function &get_import_plugin_from_uploadfile($http_post_uploadfile_r, &$error)
{
	$extension = strtolower(get_file_ext($http_post_uploadfile_r['name']));
	if(strlen($extension)>0)
	{
		if($extension == 'xml') // We need to find the DOCTYPE for the XML file, so we can assign an XML plugin.
		{
			if(function_exists('xml_parser_create'))
			{
				if(file_exists($http_post_uploadfile_r['tmp_name']))
				{
					$xmlParser = new DocTypeNameSpaceXMLParser();
					if($xmlParser->parseFile($http_post_uploadfile_r['tmp_name']))
					{
						$docType = $xmlParser->getDocType();
						$nameSpace = $xmlParser->getNameSpace();
						
						unset($xmlParser);
						
						if(strlen($docType)>0)
						{
							$importPlugin =& get_xml_import_plugin($docType, $nameSpace);
							if($importPlugin !== NULL)
							{
								return $importPlugin;
							}
							else
							{
								$error = get_opendb_lang_var('doctype_not_supported', 'doctype', $startTag);
								return NULL;
							}
						}
						else
						{
							$error = get_opendb_lang_var('no_doctype_found');
							return NULL;
						}
					}
					else
					{
						$error = get_opendb_lang_var('file_upload_error');
						return NULL;
					}
				}
				else
				{
					$error = get_opendb_lang_var('file_upload_error');
					return NULL;
				}
			}
			else
			{
				$error = get_opendb_lang_var('xml_import_plugins_not_supported');
				return NULL;
			}
		}//if($extension == 'xml')
		else
		{
			$importPlugin =& get_extension_import_plugin($extension);
			if($importPlugin !== NULL)
				return $importPlugin;
			else
			{
				$error = get_opendb_lang_var('extension_not_supported', 'extension', strtoupper($extension));
				return NULL;
			}
		}
	}
	else
	{
		$error = get_opendb_lang_var('no_extension_found');
		return NULL;
	}
}

class PreviewImportPlugin
{
	var $classname = NULL;

	function get_display_name()
	{
		return get_opendb_lang_var('preview');
	}

	function get_plugin_type()
	{
		return 'row';
	}

	function is_header_row()
	{
		return TRUE;
	}

	function read_header($file_handle, &$error)
	{
		return NULL;
	}
}

class WrapperFileHandler
{
	var $_fileHandle;

	function WrapperFileHandler($fileHandle)
	{
		$this->_fileHandle = $fileHandle;
	}

	function isEof()
	{
		return feof($this->_fileHandle);
	}

	function readLine()
	{
		if(!$this->isEof())
		{
			return fgets($this->_fileHandle, 4096);
		}
		else
		{
			return FALSE;
		}
	}
}
?>