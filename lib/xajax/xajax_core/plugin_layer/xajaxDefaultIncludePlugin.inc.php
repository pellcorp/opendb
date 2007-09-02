<?php
/*
	File: xajaxDefaultIncludePlugin.inc.php

	Contains the default script include plugin class.

	Title: xajax default script include plugin class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxDefaultIncludePlugin.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxIncludeClientScript

	Generates the SCRIPT tags necessary to 'include' the xajax javascript
	library on the browser.

	This is called when the page is first loaded.
*/
class xajaxIncludeClientScriptPlugin extends xajaxRequestPlugin
{
	var $sJsURI;
	var $aJsFiles;
	var $sDefer;
	var $sRequestURI;
	var $sStatusMessages;
	var $sWaitCursor;
	var $sVersion;
	var $sDefaultMode;
	var $sDefaultMethod;
	var $bDebug;
	var $bVerboseDebug;
	var $nScriptLoadTimeout;
	var $bUseUncompressedScripts;
	var $bDeferScriptGeneration;

	function xajaxIncludeClientScriptPlugin()
	{
		$this->sJsURI = '';
		$this->aJsFiles = array();
		$this->sDefer = '';
		$this->sRequestURI = '';
		$this->sStatusMessages = 'false';
		$this->sWaitCursor = 'true';
		$this->sVersion = 'unknown';
		$this->sDefaultMode = 'asynchronous';
		$this->sDefaultMethod = 'POST';	// W3C: Method is case sensitive
		$this->bDebug = false;
		$this->bVerboseDebug = false;
		$this->nScriptLoadTimeout = 2000;
		$this->bUseUncompressedScripts = false;
		$this->bDeferScriptGeneration = false;
	}

	/*
		Function: configure
	*/
	function configure($sName, $mValue)
	{
		if ('javascript URI' == $sName) {
			$this->sJsURI = $mValue;
		} else if ("javascript files" == $sName) {
			$this->aJsFiles = $mValue;
		} else if ("scriptDefferal" == $sName) {
			if (true === $mValue) $this->sDefer = "defer ";
			else $this->sDefer = "";
		} else if ("requestURI" == $sName) {
			$this->sRequestURI = $mValue;
		} else if ("statusMessages" == $sName) {
			if (true === $mValue) $this->sStatusMessages = "true";
			else $this->sStatusMessages = "false";
		} else if ("waitCursor" == $sName) {
			if (true === $mValue) $this->sWaitCursor = "true";
			else $this->sWaitCursor = "false";
		} else if ("version" == $sName) {
			$this->sVersion = $mValue;
		} else if ("defaultMode" == $sName) {
			if ("asynchronous" == $mValue || "synchronous" == $mValue)
				$this->sDefaultMode = $mValue;
		} else if ("defaultMethod" == $sName) {
			if ("POST" == $mValue || "GET" == $mValue)	// W3C: Method is case sensitive
				$this->sDefaultMethod = $mValue;
		} else if ("debug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDebug = $mValue;
		} else if ("verboseDebug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bVerboseDebug = $mValue;
		} else if ("scriptLoadTimeout" == $sName) {
			$this->nScriptLoadTimeout = $mValue;
		} else if ("useUncompressedScripts" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bUseUncompressedScripts = $mValue;
		} else if ('deferScriptGeneration' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDeferScriptGeneration = $mValue;
			else if ('deferred' == $mValue)
				$this->bDeferScriptGeneration = $mValue;
		}
	}

	/*
		Function: generateClientScript
	*/
	function generateClientScript()
	{
		if (false === $this->bDeferScriptGeneration)
		{
			echo $this->getJavascriptConfig();
			echo $this->getJavascriptInclude();
		}
		else if (true === $this->bDeferScriptGeneration)
		{
			echo $this->getJavascriptInclude();
		}
		else if ('deferred' == $this->bDeferScriptGeneration)
		{
			echo $this->getJavascriptConfig();
		}
	}

	/*
		Function: getJavascriptConfig

		Generates the xajax settings that will be used by the xajax javascript
		library when making requests back to the server.

		Returns:

		string - The javascript code necessary to configure the settings on
			the browser.
	*/
	function getJavascriptConfig()
	{
		$html  = "\n<script type='text/javascript' " . $this->sDefer . "charset='UTF-8'>\n";
		$html .= "/* <![CDATA[ */\n";
		$html .= "try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; };\n";
		$html .= "xajax.config.requestURI = '" . $this->sRequestURI . "';\n";
		$html .= "xajax.config.statusMessages = " . $this->sStatusMessages . ";\n";
		$html .= "xajax.config.waitCursor = " . $this->sWaitCursor . ";\n";
		$html .= "xajax.config.version = '" . $this->sVersion . "';\n";
		$html .= "xajax.config.legacy = false;\n";
		$html .= "xajax.config.defaultMode = '" . $this->sDefaultMode . "';\n";
		$html .= "xajax.config.defaultMethod = '" . $this->sDefaultMethod . "';\n";
		$html .= "/* ]]> */\n";
		$html .= "</script>\n";
		return $html;
	}

	/*
		Function: getJavascriptInclude

		Generates SCRIPT tags necessary to load the javascript libraries on
		the browser.

		sJsURI - (string):  The relative or fully qualified PATH that will be
			used to compose the URI to the specified javascript files.
		aJsFiles - (array):  List of javascript files to include.

		Returns:

		string - The SCRIPT tags that will cause the browser to load the
			specified files.
	*/
	function getJavascriptInclude()
	{
		$aJsFiles = $this->aJsFiles;
		$sJsURI = $this->sJsURI;

		if (0 == count($aJsFiles)) {
			$aJsFiles[] = array($this->_getScriptFilename('xajax_js/xajax_core.js'), 'xajax');
			
			if (true === $this->bDebug)
				$aJsFiles[] = array($this->_getScriptFilename('xajax_js/xajax_debug.js'), 'xajax.debug');
			
			if (true === $this->bVerboseDebug)
				$aJsFiles[] = array($this->_getScriptFilename('xajax_js/xajax_verbose.js'), 'xajax.debug.verbose');
		}
		
		if ($sJsURI != '' && substr($sJsURI, -1) != '/') 
			$sJsURI .= '/';
		
		$html = '';
		foreach ($aJsFiles as $aJsFile) {
			$html .= "<script type='text/javascript' src='" . $sJsURI . $aJsFile[0] . "' " . $this->sDefer . "charset='UTF-8'></script>\n";
			if (0 < $this->nScriptLoadTimeout)
			{
				$html .= "<script type='text/javascript' " . $this->sDefer . "charset='UTF-8'>\n";
				$html .= "/* <![CDATA[ */\n";
				$html .= "window.setTimeout(\n";
				$html .= " function () {\n";
				$html .= "  var scriptExists = false;\n";
				$html .= "  try { if (".$aJsFile[1].".isLoaded) scriptExists = true; }\n";
				$html .= "  catch (e) {}\n";
				$html .= "  if (!scriptExists) {\n";
				$html .= "   alert('Error: the ".$aJsFile[1]." Javascript component could not be included. Perhaps the URL is incorrect?\\nURL: {$sJsURI}{$aJsFile[0]}');\n";
				$html .= "  }\n";
				$html .= " },\n";
				$html .= $this->nScriptLoadTimeout;
				$html .= "\n);\n";
				$html .= "/* ]]> */\n";
				$html .= "</script>\n";
			}
		}
		return $html;
	}
	
	/*
		Function: _getScriptFilename
		
		Returns the name of the script file, based on the current settings.
		
		sFilename - (string):  The base filename.
		
		Returns:
		
		string - The filename as it should be specified in the script tags
		on the browser.
	*/
	function _getScriptFilename($sFilename)
	{
		if ($this->bUseUncompressedScripts) {
			return str_replace('.js', '_uncompressed.js', $sFilename);  
		}
		return $sFilename;
	}
}

$objPluginManager =& xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new xajaxIncludeClientScriptPlugin(), 99);
