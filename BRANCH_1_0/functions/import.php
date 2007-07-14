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

function get_display_import_type($type)
{
	return str_replace('_', ' ', $type);
}

/*
* Return the name of the plugin minus './import/' prefix and '.php' extension,
* or FALSE if no plugin with doctype is found.  This will only ever return
* the first plugin encountered which supports the doctype.
*/
function &get_doctype_import_plugin($doctype)
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
						return $importPlugin;
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

/**
	Will return the plugin to use, based on the $http_post_uploadfile_r['name']
	and/or the start of the file itself (available via: $http_post_uploadfile_r['tmp_name'])
*/
function &get_import_plugin($http_post_uploadfile_r, &$error)
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
					if(($fp = @fopen($http_post_uploadfile_r['tmp_name'], 'r')))
					{
						$xml_parser = new StartElementXMLParser();
						$startTag = $xml_parser->getStartElement($fp, $error);
						if(strlen($startTag)>0)
						{
							$importPlugin =& get_doctype_import_plugin($startTag);
							if($importPlugin !== NULL)
							{
								@fclose($fp);
								return $importPlugin;
							}
							else
							{
								@fclose($fp);
								$error = get_opendb_lang_var('doctype_not_supported', 'doctype', $startTag);
								return NULL;
							}
						}
						else
						{
							@fclose($fp);
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

/*
* Work out the DocType of the Document,
*/
class StartElementXMLParser
{
	var $_startElement;

	function getStartElement($fp, &$error)
	{
		// reset it.
		$this->_startElement = NULL;

		$parser = xml_parser_create();
	    xml_set_object($parser, $this);
	    xml_set_element_handler($parser, "_startElement", "_endElement");

		while ($data = fread($fp, 1024))
		{
			if(strlen($this->_startElement) > 0)
			{
				break;
			}

			if(!xml_parse($parser, $data, feof($fp)))
			{
				$error = xml_error_string(xml_get_error_code($parser));
				break;
			}
		}
		xml_parser_free($parser);

		return $this->_startElement;
	}

	function _startElement($parser,$name,$attributes)
	{
		if(strlen($this->_startElement) == 0)
			$this->_startElement = $name;
	}

	function _endElement($parser,$name)
	{
    	// not used
	}
}

class XMLImportPluginHandler
{
	var $itemImportHandler;
	var $importPlugin;
	var $fileHandler;

	// We want to send startElement name, attribs and any PCDATA as a
	// single unit.
	var $_startElementName;
	var $_startElementAttribs;
	var $_characterData;

	// stores the first error encountered.
	var $_error;

	function XMLImportPluginHandler(&$itemImportHandler, &$importPlugin, &$fileHandler)
	{
		$this->itemImportHandler =& $itemImportHandler;
		$this->importPlugin =& $importPlugin;
		$this->fileHandler =& $fileHandler;
	}

	function handleImport()
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, FALSE);
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, "_start_element", "_end_element");
		xml_set_character_data_handler($parser, "_characters");

		while (($data = $this->fileHandler->readLine())!==FALSE)
		{
			if(!xml_parse($parser, $data, $this->fileHandler->isEof()))
			{
				$this->_error = get_opendb_lang_var('xml_error', array('xml_error_string'=>xml_error_string(xml_get_error_code($parser)), 'xml_error_line'=>xml_get_current_line_number($parser)));
				return FALSE;
			}
		}

		xml_parser_free($parser);

		return TRUE;
	}

	function _start_element($parser, $name, $attribs)
	{
		// if any character data waiting to be sent, send it now.
		if(strlen($this->_startElementName)>0)
		{
			$this->importPlugin->start_element(
								$this->_startElementName,
								$this->_startElementAttribs,
								$this->_characterData);
		}

		$this->_startElementName = $name;
		$this->_startElementAttribs = $attribs;
		$this->_characterData = NULL;
	}

	function _end_element($parser, $name)
	{
		// if any character data waiting to be sent, send it now.
		if(strlen($this->_startElementName)>0)
		{
			$this->importPlugin->start_element(
								$this->_startElementName,
								$this->_startElementAttribs,
								trim($this->_characterData));

			$this->_startElementName = NULL;
			$this->_startElementAttribs = NULL;
			$this->_characterData = NULL;
		}

		$this->importPlugin->end_element($name);
	}

	function _characters($parser, $data)
	{
		$this->_characterData .= $data;
	}

	function getError()
	{
		return $this->_error;
	}
}

class RowImportPluginHandler
{
	var $itemImportHandler;
	var $importPlugin;
	var $fileHandler;
	var $field_column_r;
	var $field_default_r;
	var $field_initcap_r;

	// stores the first error encountered.
	var $_error;

	function RowImportPluginHandler(&$itemImportHandler, &$importPlugin, &$fileHandler, $field_column_r, $field_default_r, $field_initcap_r)
	{
		$this->itemImportHandler =& $itemImportHandler;
		$this->importPlugin =& $importPlugin;
		$this->fileHandler =& $fileHandler;
		$this->field_column_r = $field_column_r;
		$this->field_default_r = $field_default_r;
		$this->field_initcap_r = $field_initcap_r;
	}

	/**
	Will attempt to get the value of the fieldname, via the
	$tokens array and any $fieldname_default.
	*/
	function get_field_value($fieldname, $s_attribute_type, $tokens)
	{
		if(isset($this->field_column_r[$fieldname]) &&
					is_numeric($this->field_column_r[$fieldname]) &&
					strlen($tokens[$this->field_column_r[$fieldname]])>0)
		{
			// Only support INITCAP of actual tokens imported from CSV/DIF file!!!
			if($this->field_initcap_r[$fieldname] == 'true' && !is_array($tokens[$this->field_column_r[$fieldname]]))
				return initcap($tokens[$this->field_column_r[$fieldname]]);
			else
				return $tokens[$this->field_column_r[$fieldname]];
		}
		else if(isset($this->field_default_r[$fieldname]))
		{
			return $this->field_default_r[$fieldname];
		}
		else // no $value to return
			return FALSE;
	}

	/*
	* Will call read_header() and ignore it, if is_header_row() == FALSE.  Otherwise will call
	* read_row() and ignore it, if $include_header_row == FALSE
	*/
	function handleImport($include_header_row, $s_item_type)
	{
		// skip the header row if appropriate.
		if($this->importPlugin->is_header_row() !== TRUE || $include_header_row !== TRUE)
		{
			$this->importPlugin->read_header($this->fileHandler, $this->_error);
		}

		while( !$this->fileHandler->isEof() &&
				$this->itemImportHandler->isError()!=TRUE &&
				($read_row_r = $this->importPlugin->read_row($this->fileHandler, $this->_error)) !== FALSE )
		{
			// ensure we have a array that is not empty, or empty except for first element, which is empty.
			// Either no s_item_type restriction applies, or the s_item_type column is the same as
			// the current s_item_type we are processing.
			if((is_not_empty_array($read_row_r) && (count($read_row_r)>1 || strlen($read_row_r[0])>0)) &&
						(!is_numeric($this->field_column_r['s_item_type']) ||
						strlen($read_row_r[$this->field_column_r['s_item_type']])==0 ||
						strcasecmp($read_row_r[$this->field_column_r['s_item_type']], $s_item_type)===0))
			{
				$this->itemImportHandler->startItem($s_item_type);

				// Now do the title.
				$title_attr_type_r = fetch_sfieldtype_item_attribute_type_r($s_item_type, 'TITLE');
				$title = $this->get_field_value(get_field_name($title_attr_type_r['s_attribute_type'], $title_attr_type_r['order_no']), NULL, $read_row_r);
				$this->itemImportHandler->setTitle($title);

				$results = fetch_item_attribute_type_rs($s_item_type, NULL, FALSE);
				if($results)
				{
					while($item_attribute_type_r = db_fetch_assoc($results))
					{
						// these field types are references to item_instance values, and not true attribute types.
						if($item_attribute_type_r['s_field_type'] != 'TITLE' &&
									$item_attribute_type_r['s_field_type'] != 'STATUSTYPE' &&
									$item_attribute_type_r['s_field_type'] != 'STATUSCMNT' &&
									$item_attribute_type_r['s_field_type'] != 'DURATION' &&
									$item_attribute_type_r['s_field_type'] != 'ITEM_ID')
						{
							$value = $this->get_field_value(get_field_name($item_attribute_type_r['s_attribute_type'], $item_attribute_type_r['order_no']), $item_attribute_type_r['s_attribute_type'], $read_row_r);

							if(strlen($value)>0)
							{
								if($item_attribute_type_r['lookup_attribute_ind'] == 'Y' || 
													$item_attribute_type_r['multi_attribute_ind'] == 'Y')
								{
									// row based are comma delimited.
									$values_r = trim_explode(',', $value);
								}
								else
								{
                                    $values_r = $value;
								}

								$this->itemImportHandler->itemAttribute(
												$item_attribute_type_r['s_attribute_type'],
												$item_attribute_type_r['order_no'],
												$values_r);
												
							}//if(strlen($value)>0)
						}
					}
					db_free_result($results);
				}//if($results)

				$status_attr_type_r = fetch_sfieldtype_item_attribute_type_r($s_item_type, 'STATUSTYPE');
				$s_status_type = $this->get_field_value(get_field_name($status_attr_type_r['s_attribute_type'], $status_attr_type_r['order_no']), $status_attr_type_r['s_attribute_type'], $read_row_r);

				$status_cmnt_attr_type_r = fetch_sfieldtype_item_attribute_type_r($s_item_type, 'STATUSCMNT');
				$status_comment = $this->get_field_value(get_field_name($status_cmnt_attr_type_r['s_attribute_type'], $status_cmnt_attr_type_r['order_no']), $status_cmnt_attr_type_r['s_attribute_type'], $read_row_r);

				$duration_attr_type_r = fetch_sfieldtype_item_attribute_type_r($s_item_type, 'DURATION');
				$borrow_duration = $this->get_field_value(get_field_name($duration_attr_type_r['s_attribute_type'], $duration_attr_type_r['order_no']), $duration_attr_type_r['s_attribute_type'], $read_row_r);

				// We are only supporting instances, at this point - at some time in the future this
				// functionality may be augmented to supported linked items as well - as long as they
				// are the same s_item_type as the parent.
				$this->itemImportHandler->startItemInstance($s_status_type, $status_comment, $borrow_duration);

				// Now end the item, which also ends the instance
				$this->itemImportHandler->endItem();
			}
		}

		if($this->itemImportHandler->isError())
		{
			// copy the first error in
			$itemImportHandlerErrors =& $this->itemImportHandler->getRawErrors();
			if(is_array($itemImportHandlerErrors))
			{
				$this->_error = $itemImportHandlerErrors[0]['error'];
			}

			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	function getError()
	{
		return $this->_error;
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
