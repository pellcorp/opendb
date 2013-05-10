<?php
/*
 OpenDb Media Collector Database
 Copyright (C) 2001-2012 by Jason Pell

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
include_once("lib/fileutils.php");
include_once("lib/logging.php");
include_once("lib/widgets.php");
include_once("lib/utils.php");
include_once("lib/DocTypeNameSpaceXMLParser.class.php");
include_once("lib/XMLImportPluginHandler.class.php");
include_once("lib/RowImportPluginHandler.class.php");
include_once("lib/PreviewImportPlugin.class.php");
include_once("lib/WrapperFileHandler.class.php");

function get_item_id_range($item_id_r) {
	$item_id_range = '';
	$start_item_id = NULL;
	$last_item_id = NULL;
	if (is_array($item_id_r)) {
		for ($i = 0; $i < count($item_id_r); $i++) {
			$new_id = $item_id_r[$i];

			if ($last_item_id !== NULL) {
				// If the new_id, has jumped a number, we need to close range, and start again
				if (($last_item_id + 1) < $new_id) {
					// If we actually have a range, of at least one.
					if ($start_item_id < $last_item_id) {
						$item_id_range .= $start_item_id . '-' . $last_item_id . ',';
					} else if (is_numeric($start_item_id)) {
						$item_id_range .= $start_item_id . ',';
					}
					$start_item_id = $new_id;
				}
				$last_item_id = $new_id;
			} else {
				$start_item_id = $new_id;
				$last_item_id = $new_id;
			}
		}
	}

	// Do final 
	if ($start_item_id < $last_item_id) {
		$item_id_range .= $start_item_id . '-' . $last_item_id;
	} else if (is_numeric($start_item_id)) {
		$item_id_range .= $start_item_id;
	}

	return $item_id_range;
}

function isXpathMatch($xpath, $match) {
	if (strcmp($xpath, $match) === 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function isXpathStartsWith($xpath, $startsWith) {
	if (starts_with(strtolower($xpath), strtolower($startsWith))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/*
 * Return the name of the plugin minus 'lib/import/' prefix and '.php' extension,
 * or FALSE if no plugin with doctype is found.  This will only ever return
 * the first plugin encountered which supports the doctype.
 */
function &get_import_plugin_for_extension($extension, $doctype = NULL, $namespace = NULL) {
	$handle = opendir('lib/import');
	while ($file = readdir($handle)) {
		if (!preg_match("/^\./", $file) && preg_match("/(.*).class.php$/", $file, $regs)) {
			include('lib/import/' . $regs[1] . '.class.php');
			$importPlugin = new $regs[1];

			if (strcasecmp(get_class($importPlugin), $regs[1]) === 0) {
				if ($extension == 'xml' && $importPlugin->get_plugin_type() == 'xml') {
					if ($importPlugin->is_doctype_supported($doctype)) {
						if (!method_exists($importPlugin, 'is_namespace_supported') || $importPlugin->is_namespace_supported($namespace)) {
							return $importPlugin;
						}
					}
				} else if ($importPlugin->get_plugin_type() == 'row') {
					if ($importPlugin->is_extension_supported($extension)) {
						return $importPlugin;
					}
				}
			} else {
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Import class name \'' . $regs[1] . '\' is different to filename.', array($extension, $doctype, $namespace));
			}
		}
	}
	closedir($handle);

	return NULL;
}

function &get_import_plugin($pluginName) {
	$handle = opendir('./import');
	while ($file = readdir($handle)) {
		// Ensure valid plugin name.
		if (!preg_match("/^\./", $file) && preg_match("/(.*).class.php$/", $file, $regs)) {
			include('./import/' . $regs[1] . '.class.php');
			$importPlugin = new $regs[1];

			if (strcasecmp(get_class($importPlugin), $pluginName) === 0) {
				return $importPlugin;
			}
		}
	}
	return NULL;
}

/**
 Will return the plugin to use, based on the $http_post_uploadfile_r['name']
 and/or the start of the file itself (available via: $http_post_uploadfile_r['tmp_name'])
 */
function &get_import_plugin_from_uploadfile($http_post_uploadfile_r, &$error) {
	$extension = strtolower(get_file_ext($http_post_uploadfile_r['name']));
	if (strlen($extension) > 0) {
		if ($extension == 'xml') { // We need to find the DOCTYPE for the XML file, so we can assign an XML plugin.
			if (function_exists('xml_parser_create')) {
				// FIXME - is this absolute directory???
				if (file_exists($http_post_uploadfile_r['tmp_name'])) {
					$xmlParser = new DocTypeNameSpaceXMLParser();
					if ($xmlParser->parseFile($http_post_uploadfile_r['tmp_name'])) {
						$docType = $xmlParser->getDocType();
						$nameSpace = $xmlParser->getNameSpace();

						unset($xmlParser);

						if (strlen($docType) > 0) {
							$importPlugin = &get_import_plugin_for_extension($extension, $docType, $nameSpace);
							if ($importPlugin !== NULL) {
								return $importPlugin;
							} else {
								$error = get_opendb_lang_var('doctype_not_supported', 'doctype', $docType);
								return NULL;
							}
						} else {
							$error = get_opendb_lang_var('no_doctype_found');
							return NULL;
						}
					} else {
						$error = get_opendb_lang_var('file_upload_error');
						return NULL;
					}
				} else {
					$error = get_opendb_lang_var('file_upload_error');
					return NULL;
				}
			} else {
				$error = get_opendb_lang_var('xml_import_plugins_not_supported');
				return NULL;
			}
		} else {
			$importPlugin = &get_import_plugin_for_extension($extension);
			if ($importPlugin !== NULL) {
				return $importPlugin;
			} else {
				$error = get_opendb_lang_var('extension_not_supported', 'extension', strtoupper($extension));
				return NULL;
			}
		}
	} else {
		$error = get_opendb_lang_var('no_extension_found');
		return NULL;
	}
}
?>