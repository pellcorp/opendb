<?php
/*
	File: xajaxControl.inc.php

	Contains the base class for all controls.

	Title: xajaxControl class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxControl.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxControl

	The base class for all xajax enabled controls.  Derived classes will generate the
	HTML and javascript code that will be sent to the browser via <xajaxControl->printHTML>
	or sent to the browser in a <xajaxResponse> via <xajaxControl->getHTML>.
*/
class xajaxControl
{
	/*
		String: sTag
	*/
	var $sTag;
	
	/*
		Array: aAttributes
		
		An associative array of attributes that will be used in the generation
		of the HMTL code for this control.
	*/
	var $aAttributes;
	
	/*
		Array: aEvents
		
		An associative array of events that will be assigned to this control.  Each
		event declaration will include a reference to a <xajaxRequest> object; it's
		script will be extracted using <xajaxRequest->printScript> or 
		<xajaxRequest->getScript>.
	*/
	var $aEvents;

	/*
		Function: xajaxControl
		
		$aConfiguration - (array):  An associative array that contains a variety
			of configuration options for this <xajaxControl> object.
			
		This array may contain the following entries:
		
		'attributes' - (array):  An associative array containing attributes
			that will be passed to the <xajaxControl->setAttribute> function.
		
		'children' - (array):  An array of <xajaxControl> derived objects that
			will be the children of this control.
	*/
	function xajaxControl($sTag, $aConfiguration=array())
	{
		$this->sTag = $sTag;

		$this->clearAttributes();
				
		if (isset($aConfiguration['attributes']))
			if (is_array($aConfiguration['attributes']))
				foreach ($aConfiguration['attributes'] as $sKey => $sValue)
					$this->setAttribute($sKey, $sValue);

		$this->clearEvents();
		
		if (isset($aConfiguration['event']))
			call_user_func_array(array(&$this, 'setEvent'), $aConfiguration['event']);
		
		else if (isset($aConfiguration['events']))
			if (is_array($aConfiguration['events']))
				foreach ($aConfiguration['events'] as $aEvent)
					call_user_func_array(array(&$this, 'setEvent'), $aEvent);
	}
	
	/*
		Function: clearAttributes
		
		Removes all attributes assigned to this control.
	*/
	function clearAttributes()
	{
		$this->aAttributes = array();
	}

	/*
		Function: setAttribute
		
		Call to set various control specific attributes to be included in the HTML
		script that is returned when <xajaxControl->printHTML> or <xajaxControl->getHTML>
		is called.
	*/
	function setAttribute($sName, $sValue)
	{
		$this->aAttributes[$sName] = $sValue;
	}
	
	/*
		Function: getAttribute
		
		Call to obtain the value currently associated with the specified attribute
		if set.
		
		sName - (string): The name of the attribute to be returned.
		
		Returns:
		
		mixed - The value associated with the attribute, or null.
	*/
	function getAttribute($sName)
	{
		if (false == isset($this->aAttributes[$sName]))
			return null;
		
		return $this->aAttributes[$sName];
	}
	
	/*
		Function: clearEvents
		
		Clear the events that have been associated with this object.
	*/
	function clearEvents()
	{
		$this->aEvents = array();
	}

	/*
		Function: setEvent
		
		Call this function to assign a <xajaxRequest> object as the handler for
		the specific DOM event.  The <xajaxRequest->printScript> function will 
		be called to generate the javascript for this request.
		
		sEvent - (string):  A string containing the name of the event to be assigned.
		objRequest - (xajaxRequest object):  The <xajaxRequest> object to be associated
			with the specified event.
		aParameters - (array, optional):  An array containing parameter declarations
			that will be passed to this <xajaxRequest> object just before the javascript
			is generated.
		sBeforeRequest - (string, optional):  a string containing a snippet of javascript code
			to execute prior to calling the xajaxRequest function
		sAfterRequest - (string, optional):  a string containing a snippet of javascript code
			to execute after calling the xajaxRequest function
	*/
	function setEvent($sEvent, &$objRequest, $aParameters=array(), $sBeforeRequest='', $sAfterRequest='; return false;')
	{
		if (false == is_a($objRequest, 'xajaxRequest'))
			trigger_error('Invalid request object passed to xajaxControl::setEvent'
				. $this->backtrace(), 
				E_USER_ERROR
				);

		$this->aEvents[$sEvent] = array(&$objRequest, $aParameters, $sBeforeRequest, $sAfterRequest);
	}

	/*
		Function: verifyAttributeExists
		
		Verify that the (required) attribute has been defined for this control.
		
		sName - (string):  The name of the attribute to verify
	*/
	function verifyAttributeExists($sName)
	{
		if (false == isset($this->aAttributes[$sName]))
			trigger_error('Missing required attribute ' 
				. $sName
				. $this->backtrace(), 
				E_USER_ERROR
				);
	}

	/*
		Function: verifyAttributesExist
		
		Verify that a list of (required) attributes have been defined
		for this control.
		
		aNames - (array):  An array of attribute names to be verified; see
			<xajaxControl->verifyAttributeExists> for more information.
	*/
	function verifyAttributesExist($aNames)
	{
		if (false == is_array($aNames))
			trigger_error('Invalid array of attribute names passed to xajaxControl::verifyAttributesExist.'
				. $this->backtrace(),
				E_USER_ERROR
				);

		foreach ($aNames as $sName)
			$this->verifyAttributeExists($sName);
	}

	/*
		Function: getHTML
		
		Generates and returns the HTML representation of this control and 
		it's children.
		
		Returns:
		
		string - The HTML representation of this control.
	*/
	function getHTML()
	{
		ob_start();
		$this->printHTML();
		return ob_get_clean();
	}
	
	/*
		Function: printHTML
		
		Generates and prints the HTML representation of this control and 
		it's children.
		
		Returns:
		
		string - The HTML representation of this control.
	*/
	function printHTML($sIndent='')
	{
		echo $sIndent;
		echo '<';
		echo $this->sTag;
		echo ' ';
		$this->_printAttributes();
		$this->_printEvents();
		echo "/>\n";
	}

	function _printAttributes()
	{
		// NOTE: Special case here: disabled='false' does not work in HTML; does work in javascript
		foreach ($this->aAttributes as $sKey => $sValue)
			if ('disabled' != $sKey || 'false' != $sValue)
				echo "{$sKey}='{$sValue}' ";
	}

	function _printEvents()
	{
		foreach (array_keys($this->aEvents) as $sKey)
		{
			$aEvent =& $this->aEvents[$sKey];
			$objRequest =& $aEvent[0];
			$aParameters = $aEvent[1];
			$sBeforeRequest = $aEvent[2];
			$sAfterRequest = $aEvent[3];

			foreach ($aParameters as $aParameter)
			{
				$nParameter = $aParameter[0];
				$sType = $aParameter[1];
				$sValue = $aParameter[2];
				$objRequest->setParameter($nParameter, $sType, $sValue);
			}

			$objRequest->useDoubleQuote();

			echo "{$sKey}='{$sBeforeRequest}";

			$objRequest->printScript();

			echo "{$sAfterRequest}' ";
		}
	}

	function backtrace()
	{
		// debug_backtrace was added to php in version 4.3.0
		// version_compare was added to php in version 4.0.7
		if (0 <= version_compare(PHP_VERSION, '4.3.0'))
			return '<div><div>Backtrace:</div><pre>' . print_r(debug_backtrace(), true) . '</pre></div>';
		return '';
	}
}

/*
	Class: xajaxControlContainer
	
	This class is used as the base class for controls that will contain
	other child controls.
*/
class xajaxControlContainer extends xajaxControl
{
	/*
		Array: aChildren
		
		An array of child controls.
	*/
	var $aChildren;
	
	/*
		Array: aChildrenAllowed
		
		If set, this contains the list of control classes that are allowed
		for this container.
	*/
	var $aChildrenAllowed;
	
	/*
		Boolean: bOnlyLiteral
		
		this will be true if the only children of this control are derived
		from clsLiteral.  literal text does not need to be formatted.
	*/
	var $bOnlyLiteral;
	
	/*
		Boolean: sEndTag
		
		'required' - (default) Indicates the control must have a full end tag
		'optional' - The control may have an abbr. begin tag or a full end tag
		'forbidden' - The control must have an abbr. begin tag and no end tag
	*/
	var $sEndTag;

	/*
		Function: xajaxControlContainer
		
		Called to construct and configure this control.
		
		aConfiguration - (array):  See <xajaxControl->xajaxControl> for more
			information.
	*/
	function xajaxControlContainer($sTag, $aConfiguration=array())
	{
		xajaxControl::xajaxControl($sTag, $aConfiguration);
		
		$this->sEndTag = 'required';

		$this->aChildrenAllowed = array();
		
		if (isset($aConfiguration['allowed']))
			if (is_array($aConfiguration['allowed']))
				$this->aChildrenAllowed = $aConfiguration['allowed'];

		$this->clearChildren();
		
		if (isset($aConfiguration['child']))
			$this->addChild($aConfiguration['child']);

		else if (isset($aConfiguration['children']))
			$this->addChildren($aConfiguration['children']);
	}
	
	/*
		Function: clearChildren
		
		Clears the list of child controls associated with this control.
	*/
	function clearChildren()
	{
		$this->bOnlyLiteral = true;
		$this->aChildren = array();
	}

	/*
		Function: addChild
		
		Adds a control to the array of child controls.  Child controls
		must be derived from <xajaxControl>.
	*/
	function addChild(&$objControl)
	{
		if (false == is_a($objControl, 'xajaxControl'))
			trigger_error('Invalid control passed to addChild; should be derived from xajaxControl.'
				. $this->backtrace(), 
				E_USER_ERROR);

		if (false == (is_a($objControl, 'clsLiteral') || is_a($objControl, 'clsBreak')))
			$this->bOnlyLiteral = false;
			
		if (0 < count($this->aChildrenAllowed))
		{
			$bAllowed = false;
			foreach($this->aChildrenAllowed as $sAllowed)
				if (is_a($objControl, $sAllowed))
					$bAllowed = true;
			
			if (false == $bAllowed)
				trigger_error('Invalid control passed to addChild; should be one of the following:'
					. print_r($this->aChildrenAllowed, true)
					. $this->backtrace(),
					E_USER_ERROR
					);
		}

		$this->aChildren[] =& $objControl;
	}
	
	function addChildren(&$aChildren)
	{
		if (false == is_array($aChildren))
			trigger_error('Invalid parameter passed to xajaxControl::addChildren; should be array of xajaxControl objects'
				. $this->backtrace(),
				E_USER_ERROR
				);
				
		foreach (array_keys($aChildren) as $sKey)
			$this->addChild($aChildren[$sKey]);
	}

	function printHTML($sIndent='')
	{
		echo $sIndent;
		echo '<';
		echo $this->sTag;
		echo ' ';
		$this->_printAttributes();
		$this->_printEvents();
		
		if ('forbidden' == $this->sEndTag)
		{
			echo "/>\n";
			return;
		}
		
		if ('optional' == $this->sEndTag && 0 == count($this->aChildren))
		{
			echo "/>\n";
			return;
		}
		
		echo '>';
		if (false == $this->bOnlyLiteral)
			echo "\n";
		$this->_printChildren($sIndent);
		if (false == $this->bOnlyLiteral)
			echo $sIndent;
		echo '</';
		echo $this->sTag;
		echo ">\n";
	}

	function _printChildren($sIndent='')
	{
		if (false == is_a($this, 'clsDocument'))
			$sIndent .= "\t";

		// children
		foreach (array_keys($this->aChildren) as $sKey)
		{
			$objChild =& $this->aChildren[$sKey];
			$objChild->printHTML($sIndent);
		}
	}
}
