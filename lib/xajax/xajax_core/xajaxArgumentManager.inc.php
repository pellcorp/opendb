<?php
/*
	File: xajaxArgumentManager.inc.php

	Contains the xajaxArgumentManager class

	Title: xajaxArgumentManager class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxArgumentManager.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

if (!defined('XAJAX_METHOD_UNKNOWN')) define('XAJAX_METHOD_UNKNOWN', 0);
if (!defined('XAJAX_METHOD_GET')) define('XAJAX_METHOD_GET', 1);
if (!defined('XAJAX_METHOD_POST')) define('XAJAX_METHOD_POST', 2);

/*
	Class: xajaxArgumentManager
	
	This class processes the input arguments from the GET or POST data of 
	the request.  If this is a request for the initial page load, no arguments
	will be processed.  During a xajax request, any arguments found in the
	GET or POST will be converted to a PHP array.
*/
class xajaxArgumentManager
{
	/*
		Array: aArgs
		
		An array of arguments received via the GET or POST parameter
		xjxargs.
	*/
	var $aArgs;
	
	/*
		Boolean: bDecodeUTF8Input
		
		A configuration option used to indicate whether input data should be
		UTF8 decoded automatically.
	*/
	var $bDecodeUTF8Input;
	
	/*
		Integer: iPos
		
		The current array position used during the parsing of the args.
	*/
	var $iPos;
	
	/*
		Array: aObjArray
		
		An array that will contain the argument data as it is being processed.
	*/
	var $aObjArray;
	
	/*
		String: sCharacterEncoding
		
		The character encoding in which the input data will be received.
	*/
	var $sCharacterEncoding;
	
	/*
		Integer: nMethod
		
		Stores the method that was used to send the arguments from the client.  Will
		be one of: XAJAX_METHOD_UNKNOWN, XAJAX_METHOD_GET, XAJAX_METHOD_POST
	*/
	var $nMethod;
	
	/*
		Constructor: xajaxArgumentManager
		
		Initializes configuration settings to their default values and reads
		the argument data from the GET or POST data.
	*/
	function xajaxArgumentManager()
	{
		$this->aArgs = array();
		$this->bDecodeUTF8Input = false;
		$this->iPos = 0;
		$this->aObjArray = array();
		$this->sCharacterEncoding = 'UTF-8';
		$this->nMethod = XAJAX_METHOD_UNKNOWN;
		
		$aArgs = NULL;
		
		if (isset($_POST['xjxargs'])) {
			$this->nMethod = XAJAX_METHOD_POST;
			$aArgs = $_POST['xjxargs'];
		} else if (isset($_GET['xjxargs'])) {
			$this->nMethod = XAJAX_METHOD_GET;
			$aArgs = $_GET['xjxargs'];
		}
		
		if (NULL != $aArgs)
			$this->aArgs = $this->_process($aArgs);
	}
	
	/*
		Function: getInstance
		
		Returns:
		
		object - A reference to an instance of this class.  This function is
			used to implement the singleton pattern.
	*/
	function &getInstance()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxArgumentManager();
		}
		return $obj;
	}
	
	/*
		Function: configure
		
		Accepts configuration settings from the main <xajax> object.
		
		The <xajaxArgumentManager> tracks the following configuration settings:
			<decodeUTF8Input> - (boolean): See <xajaxArgumentManager->bDecodeUTF8Input>
			<characterEncoding> - (string): See <xajaxArgumentManager->sCharacterEncoding>
	*/
	function configure($sName, $mValue)
	{
		if ('decodeUTF8Input' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDecodeUTF8Input = $mValue;
		} else if ('characterEncoding' == $sName) {
			$this->sCharacterEncoding = $mValue;
		}
	}
	
	/*
		Function: getRequestMethod
		
		Returns the method that was used to send the arguments from the client.
	*/
	function getRequestMethod()
	{
		return $this->nMethod;
	}
	
	/*
		Function: process
		
		Returns the array of arguments that were extracted and parsed from 
		the GET or POST data.
	*/
	function process()
	{
		return $this->aArgs;
	}
	
	/*
		Function: _process
		
		Converts the raw input arguments into proper xajax arguments.
		
		aArgs - (array):  The arguments to process.
		
		Returns:
		
		array - The processed arguments.
	*/
	function _process($aArgs)
	{
		for ($i = 0; $i < sizeof($aArgs); $i++)
		{
			// If magic quotes is on, then we need to strip the slashes from the args
			if (get_magic_quotes_gpc() == 1 && is_string($aArgs[$i])) {
			
				$aArgs[$i] = stripslashes($aArgs[$i]);
			}
			if (false != strstr($aArgs[$i],"<xjxobj>")) {
				$aArgs[$i] = $this->_xmlToArray("xjxobj",$aArgs[$i]);	
			}
			else if (false != strstr($aArgs[$i],"<xjxquery>")) {
				$aArgs[$i] = $this->_xmlToArray("xjxquery",$aArgs[$i]);	
			}
			else {
				if ($this->bDecodeUTF8Input) {
					$aArgs[$i] = $this->_decodeUTF8Data($aArgs[$i]);	
				}
				$aArgs[$i] = str_replace(array('<![CDATA[', ']]>'), '', $aArgs[$i]);
			}
		}
		return $aArgs;
	}
	
	/*
		Function: _xmlToArray
		
		Takes a string containing xajax xjxobj or xjxquery XML tags and builds
		an array representation of it to pass as an argument to the requested
		PHP function.
		
		rootTag - (string):  The tag to be converted; one of:
			- xjxobj: A javascript array passed as a parameter
			- xjxquery: An HTML form, passed using <xajax.getFormValues>
		sXml - (string):  The xml to be parsed.
		
		Returns:
		
		array - An array representation of the values extracted from the
			xml.
	*/
	function _xmlToArray($rootTag, $sXml)
	{
		$aArray = array();
		$sXml = str_replace("<$rootTag>","<$rootTag>|~|",$sXml);
		$sXml = str_replace("</$rootTag>","</$rootTag>|~|",$sXml);
		$sXml = str_replace("<e>","<e>|~|",$sXml);
		$sXml = str_replace("</e>","</e>|~|",$sXml);
		$sXml = str_replace("<k>","<k>|~|",$sXml);
		$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
		$sXml = str_replace("<v>","<v>|~|",$sXml);
		$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
		$sXml = str_replace("<q>","<q>|~|",$sXml);
		$sXml = str_replace("</q>","|~|</q>|~|",$sXml);
		
		$this->aObjArray = explode("|~|",$sXml);
		
		$this->iPos = 0;
		$aArray = $this->_parseObjXml($rootTag);
		
		return $aArray;
	}
	
	/*
		Function: _parseObjXml
		
		A recursive function that generates an array from the contents
		of <xajaxRequestProcessorPlugin->aObjArray>.
		
		rootTag - (string):  The tag to be converted; one of:
			- xjxobj:  A javascript array that was sent as a parameter
			- xjxquery:  Form values extracted with <xajax.getFormValues>
		
		Returns:
		
		array - A PHP array reprensentation of the object.
	*/
	function _parseObjXml($rootTag)
	{
		$aArray = array();
		
		if ($rootTag == "xjxobj")
		{
			while (!strstr($this->aObjArray[$this->iPos],"</xjxobj>")) {
				$this->iPos++;
				if (strstr($this->aObjArray[$this->iPos],"<e>")) {
					$key = "";
					$value = null;
						
					$this->iPos++;
					while (!strstr($this->aObjArray[$this->iPos],"</e>")) {
						if (strstr($this->aObjArray[$this->iPos],"<k>")) {
							$this->iPos++;
							while (!strstr($this->aObjArray[$this->iPos],"</k>")) {
								$key .= $this->aObjArray[$this->iPos];
								$this->iPos++;
							}
							if ($this->bDecodeUTF8Input) {
								$key = $this->_decodeUTF8Data($key);
							}
							$key = str_replace(array('<![CDATA[', ']]>'), '', $key);
						}
						if (strstr($this->aObjArray[$this->iPos],"<v>")) {
							$this->iPos++;
							while (!strstr($this->aObjArray[$this->iPos],"</v>")) {
								if (strstr($this->aObjArray[$this->iPos],"<xjxobj>")) {
									$value = $this->_parseObjXml("xjxobj");
									$this->iPos++;
								}
								else {
									$value .= $this->aObjArray[$this->iPos];
									if ($this->bDecodeUTF8Input)
									{
										$value = $this->_decodeUTF8Data($value);
									}
									$value = str_replace(array('<![CDATA[', ']]>'), '', $value);
								}
								$this->iPos++;
							}
						}
						$this->iPos++;
					}
					$aArray[$key]=$value;
				}
			}
		}
		
		if ($rootTag == "xjxquery")
		{
			$sQuery = "";
			$this->iPos++;
			while (!strstr($this->aObjArray[$this->iPos],"</xjxquery>")) {
				if (strstr($this->aObjArray[$this->iPos],"<q>") || strstr($this->aObjArray[$this->iPos],"</q>")) {
					$this->iPos++;
					continue;
				}
				$sQuery	.= $this->aObjArray[$this->iPos];
				$this->iPos++;
			}
			
			parse_str($sQuery, $aArray);

			if ($this->bDecodeUTF8Input)
			{
				foreach($aArray as $key => $value)
				{
					$aArray[$key] = $this->_decodeUTF8Data($value);
				}
			}
			
			// If magic quotes is on, then we need to strip the slashes from the
			// array values because of the parse_str pass which adds slashes
			if (get_magic_quotes_gpc() == 1) {
				$newArray = array();
				foreach ($aArray as $sKey => $sValue) {
					if (is_string($sValue))
						$newArray[$sKey] = stripslashes($sValue);
					else
						$newArray[$sKey] = $sValue;
				}
				$aArray = $newArray;
			}
			
			foreach ($aArray as $key => $value) {
				$aArray[$key] = str_replace(array('<![CDATA[', ']]>'), '', $value);
			}
		}
		
		return $aArray;
	}
	
	/*
		Function: _decodeUTF8Data
		
		Decodes string data from UTF-8 encoding to the current xajax
		encoding; this can be set using <xajax->setEncoding>
		
		sData - (string):  The data to be converted.
		
		Returns:
		
		string - The decoded data.
	*/
	function _decodeUTF8Data($sData)
	{
		$sValue = $sData;

		// correction by Vinicius Zani
		if (is_array($sValue)) {
			foreach ($sValue as $key => $value)
				$sValue[$key] = $this->_decodeUTF8Data($value);
			return $sValue;
		}

		if ($this->bDecodeUTF8Input)
		{
			$sFuncToUse = NULL;
			
			if (function_exists('iconv'))
			{
				$sFuncToUse = "iconv";
			}
			else if (function_exists('mb_convert_encoding'))
			{
				$sFuncToUse = "mb_convert_encoding";
			}
			else if ($this->sCharacterEncoding == "ISO-8859-1")
			{
				$sFuncToUse = "utf8_decode";
			}
			else
			{
				trigger_error("The incoming xajax data could not be converted from UTF-8", E_USER_NOTICE);
			}
			
			if ($sFuncToUse)
			{
				if (is_string($sValue))
				{
					if ($sFuncToUse == "iconv")
					{
						$sValue = iconv("UTF-8", $this->sCharacterEncoding.'//TRANSLIT', $sValue);
					}
					else if ($sFuncToUse == "mb_convert_encoding")
					{
						$sValue = mb_convert_encoding($sValue, $this->sCharacterEncoding, "UTF-8");
					}
					else
					{
						$sValue = utf8_decode($sValue);
					}
				}
			}
		}
		return $sValue;	
	}
}