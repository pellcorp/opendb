<?php
/*
	File: xajaxResponseManager.inc.php

	Contains the xajaxResponseManager class

	Title: xajaxResponseManager class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxResponseManager.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxResponseManager

	This class stores and tracks the response that will be returned after
	processing a request.  The response manager represents a single point
	of contact for working with <xajaxResponse> objects as well as 
	<xajaxCustomResponse> objects.
*/
class xajaxResponseManager
{
	/*
		Object: objResponse
	
		The current response object that will be sent back to the browser
		once the request processing phase is complete.
	*/
	var $objResponse;
	
	/*
		String: sCharacterEncoding
	*/
	var $sCharacterEncoding;
	
	/*
		Boolean: bOutputEntities
	*/
	var $bOutputEntities;
	
	/*
		Function: xajaxResponseManager
	*/
	function xajaxResponseManager()
	{
		$this->objResponse = NULL;
	}
	
	/*
		Function: getInstance
	*/
	function &getInstance()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxResponseManager();
		}
		return $obj;
	}
	
	/*
		Function: configure
	*/
	function configure($sName, $mValue)
	{
		if ('characterEncoding' == $sName) {
			$this->sCharacterEncoding = $mValue;
		} else if ('outputEntities' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bOutputEntities = $mValue;
		}
	}
	
	/*
		Function: clear
		
		Clear the current response.  A new response will need to be appended
		before the request processing is complete.
	*/
	function clear()
	{
		$this->objResponse = NULL;
	}
	
	/*
		Function: append
	*/
	function append($mResponse)
	{
		if (is_a($mResponse, 'xajaxResponse')) {
			if (NULL == $this->objResponse) {
				$this->objResponse = $mResponse;
			} else if (is_a($this->objResponse, 'xajaxResponse')) {
				if ($this->objResponse != $mResponse)
					$this->objResponse->absorb($mResponse);
			} else {
				$this->debug('Error:  You cannot mix response types while processing a single request.');
			}
		} else if (is_a($mResponse, 'xajaxCustomResponse')) {
			if (NULL == $this->objResponse) {
				$this->objResponse = $mResponse;
			} else if (is_a($this->objResponse, 'xajaxCustomResponse')) {
				if ($this->objResponse != $mResponse)
					$this->objResponse->absorb($mResponse);
			} else {
				$this->debug('Error:  You cannot mix response types while processing a single request.');
			}
		} else {
			$this->debug("An invalid response was returned while processing this request.");
		}
	}
	
	/*
		Function: debug
	*/
	function debug($sMessage)
	{
		if (NULL == $this->objResponse)
			$this->objResponse = new xajaxResponse();
			
		$this->objResponse->debug($sMessage);
	}
	
	/*
		Function: send
	*/
	function send()
	{
		if (NULL != $this->objResponse) {
			$this->objResponse->printOutput();
		}
	}
	
	/*
		Function: getCharacterEncoding
		
		Used to configure new xajaxResponse objects as they are instantiated.
	*/
	function getCharacterEncoding()
	{
		return $this->sCharacterEncoding;
	}
	
	/*
		Function: getOutputEntities
		
		Used to configure new xajaxResponse objects as they are instantiated.
	*/
	function getOutputEntities()
	{
		return $this->bOutputEntities;
	}
}