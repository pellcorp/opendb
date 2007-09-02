<?php
/*
	File: xajaxResponse.inc.php

	Contains the response class.
	
	Title: xajax response class
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxResponse.inc.php 361 2007-05-24 12:48:14Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: xajaxResponse
	
	Collect commands to be sent back to the browser in response to a xajax
	request.  Commands are encoded and packaged in a format that is acceptable
	to the response handler from the javascript library running on the client
	side.
	
	Common commands include:
		- <xajaxResponse->assign>: Assign a value to an elements property.
		- <xajaxResponse->append>: Append a value on to an elements property.
		- <xajaxResponse->script>: Execute a portion of javascript code.
		- <xajaxResponse->call>: Execute an existing javascript function.
		- <xajaxResponse->alert>: Display an alert dialog to the user.
		
	Elements are identified by the value of the HTML id attribute.  If you do 
	not see your updates occuring on the browser side, ensure that you are 
	using the correct id in your response.
*/
class xajaxResponse
{
	/**#@+
	 * @access protected
	 */
	 
	/*
		Array: aCommands
		
		Stores the commands that will be sent to the browser in the response.
	*/
	var $aCommands;
	
	/*
		String: sEncoding
		
		The name of the encoding method you wish to use when dealing with 
		special characters.  See <xajax->setEncoding> for more information.
	*/
	var $sCharacterEncoding;
	
	/*
		Boolean: bOutputEntities
		
		Convert special characters to the HTML equivellent.  See also
		<xajax->bOutputEntities> and <xajax->setFlag>.
	*/
	var $bOutputEntities;

	/*
		Mixed: returnValue
		
		A string, array or integer value to be returned to the caller when
		using 'synchronous' mode requests.  See <xajax->setMode> for details.
	*/
	var $returnValue;

	/**#@-*/

	/*
		Constructor: xajaxResponse
		
		Create and initialize a xajaxResponse object.
	*/
	function xajaxResponse()
	{
		$objResponseManager =& xajaxResponseManager::getInstance();
		
		$this->sCharacterEncoding = $objResponseManager->getCharacterEncoding();
		$this->bOutputEntities = $objResponseManager->getOutputEntities();

		$this->aCommands = array();
	}

	/*
		Function: setCharacterEncoding
		
		Overrides the default character encoding (or the one specified in the
		constructor) to the specified character encoding.
		
		sEncoding - (string):  The encoding method to use for this response.
		
		See also, <xajaxResponse->xajaxResponse>()
		
		Returns:
		
		object - The xajaxResponse object.
	*/
	function setCharacterEncoding($sCharacterEncoding)
	{
		$this->sCharacterEncoding = $sCharacterEncoding;
		return $this;
	}

	/*
		Function: setOutputEntities
		
		Convert special characters to their HTML equivellent automatically
		(only works if the mb_string extension is available).
		
		bOption - (boolean):  Convert special characters
		
		Returns:
		
		object - The xajaxResponse object.
	*/
	function setOutputEntities($bOutputEntities)
	{
		$this->bOutputEntities = (boolean)$bOutputEntities;
		return $this;
	}
	
	/*
		Function: plugin
		
		Provides access to registered response plugins.  If you are using PHP
		4 or 5, pass the plugin name as the first argument, the plugin method
		name as the second argument and subsequent arguments (if any) to be 
		passed along to the plugin.
		
		Optionally, if you use PHP 5, you can pass just the plugin name as the
		first argument and the plugin object will be returned.  You can then
		access the methods of the plugin directly.
		
		sName - (string):  Name of the plugin.
		sFunction - (string, optional):  The name of the method to call.
		arg1...argn - (mixed, optional):  Additional arguments to pass on to
			the plugin function.
			
		Returns:
		
		object - The plugin specified by sName.
	*/
	function &plugin()
	{
		$aArgs = func_get_args();
		
		if (0 < count($aArgs))
		{
			$sName = array_shift($aArgs);

			$objPluginManager =& xajaxPluginManager::getInstance();
			$objPlugin =& $objPluginManager->getPlugin($sName);
			
			if (false !== $objPlugin)
			{
				$objPlugin->setResponse($this);
				
				if (0 < count($aArgs))
				{
					$sMethod = array_shift($aArgs);
					
					$aFunction = array(&$objPlugin, $sMethod);
					call_user_func_array($aFunction, $aArgs);
				}
				
				return $objPlugin;
			}
			else
			{
				$bReturn = false;
				return $bReturn;
			}
		}
		else trigger_error("Error: Plugin name is missing or invalid.", E_USER_ERROR);

		$bReturn = false;
		return $bReturn;
	}

	/*
		Function: __get
		
		Magic function for PHP 5.  Used to permit plugins to be called as if they
		where native members of the xajaxResponse instance.
		
		sPluginName - (string):  The name of the plugin.
		
		Returns:
		
		object - The plugin specified by sPluginName.
	*/
	function __get($sPluginName)
	{
		return $this->plugin($sPluginName);
	}

	/*
		Function: confirmCommands
		
		Response command that prompts user with [ok] [cancel] style
		message box.  If the user clicks cancel, the specified 
		number of response commands following this one, will be
		skipped.
		
		iCmdNumber - (integer):  The number of commands to skip upon cancel.
		sMessage - (string):  The message to display to the user.
	
		Returns:
		
		object - The xajaxResponse object.
	*/
	function confirmCommands($iCmdNumber, $sMessage)
	{
		return $this->addCommand(array('n'=>'cc','t'=>$iCmdNumber),$sMessage);
	}
	
	/*
		Function: assign
		
		Response command indicating that the specified value should be 
		assigned to the given element's attribute.
		
		sTarget - (string):  The id of the html element on the browser.
		sAttribute - (string):  The property to be assigned.
		sData - (string):  The value to be assigned to the property.
		
		Returns:
		
		object - The <xajaxResponse> object.
		
		Example:
		
		$objResponse->assign("contentDiv", "innerHTML", "Some Text");
	*/
	function assign($sTarget,$sAttribute,$sData)
	{
		return $this->addCommand(array('n'=>'as','t'=>$sTarget,'p'=>$sAttribute),$sData);
	}
	
	/*
		Function: append
		
		Response command that indicates the specified data should be appended
		to the given element's property.
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The name of the property to be appended to.
		sData - (string):  The data to be appended to the property.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function append($sTarget,$sAttribute,$sData)
	{	
		return $this->addCommand(array('n'=>'ap','t'=>$sTarget,'p'=>$sAttribute),$sData);
	}

	/*
		Function: prepend
		
		Response command to prepend the specified value onto the given
		element's property.
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The property to be updated.
		sData - (string):  The value to be prepended.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function prepend($sTarget,$sAttribute,$sData)
	{
		return $this->addCommand(array('n'=>'pp','t'=>$sTarget,'p'=>$sAttribute),$sData);
	}
	
	/*
		Function: replace
		
		Replace a specified value with another value within the given
		element's property.
		
		sTarget - (string):  The id of the element to update.
		sAttribute - (string):  The property to be updated.
		sSearch - (string):  The needle to search for.
		sData - (string):  The data to use in place of the needle.
	*/
	function replace($sTarget,$sAttribute,$sSearch,$sData)
	{
		$aData[] = array('k'=>'s','v'=>$sSearch);
		$aData[] = array('k'=>'r','v'=>$sData);
		return $this->addCommand(array('n'=>'rp','t'=>$sTarget,'p'=>$sAttribute),$aData);
	}
	
	/*
		Function: clear
		
		Response command used to clear the specified property of the 
		given element.
		
		sTarget - (string):  The id of the element to be updated.
		sAttribute - (string):  The property to be clared.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function clear($sTarget,$sAttribute)
	{
		return $this->assign($sTarget,$sAttribute,'');
	}

	/*
		Function: contextAssign
		
		Response command used to assign a value to a member of a
		javascript object (or element) that is specified by the context
		member of the request.  The object is referenced using the 'this' keyword
		in the sAttribute parameter.
		
		sAttribute - (string):  The property to be updated.
		sData - (string):  The value to assign.
	
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function contextAssign($sAttribute, $sData)
	{
		return $this->addCommand(array('n'=>'c:as', 'p'=>$sAttribute), $sData);
	}

	/*
		Function: contextAppend
		
		Response command used to append a value onto the specified member
		of the javascript context object (or element) specified by the context
		member of the request.  The object is referenced using the 'this' keyword
		in the sAttribute parameter.
		
		sAttribute - (string):  The member to be appended to.
		sData - (string):  The value to append.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function contextAppend($sAttribute, $sData)
	{
		return $this->addCommand(array('n'=>'c:ap', 'p'=>$sAttribute), $sData);
	}	
	
	/*
		Function: contextPrepend
		
		Response command used to prepend the speicified data to the given
		member of the current javascript object specified by context in the
		current request.  The object is access via the 'this' keyword in the
		sAttribute parameter.
		
		sAttribute - (string):  The member to be updated.
		sData - (string):  The value to be prepended.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function contextPrepend($sAttribute, $sData)
	{
		return $this->addCommand(array('n'=>'c:pp', 'p'=>$sAttribute), $sData);
	}

	/*
		Function: contextClear
		
		Response command used to clear the value of the property specified
		in the sAttribute parameter.  The member is access via the 'this'
		keyword and can be used to update a javascript object specified
		by context in the request parameters.
		
		sAttribute - (string):  The member to be cleared.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function contextClear($sAttribute)
	{
		return $this->contextAssign($sAttribute, '');
	}

	/*
		Function: alert
		
		Response command that is used to display an alert message to the user.
		
		sMsg - (string):  The message to be displayed.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function alert($sMsg)
	{
		return $this->addCommand(array('n'=>'al'),$sMsg);
	}
	
	function debug($sMessage)
	{
		return $this->addCommand(array('n'=>'dbg'),$sMessage);
	}
	
	/*
		Function: redirect
		
		Response command that causes the browser to navigate to the specified
		URL.
		
		sURL - (string):  The relative or fully qualified URL.
		iDelay - (integer, optional):  Number of seconds to delay before
			the redirect occurs.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function redirect($sURL, $iDelay=0)
	{
		//we need to parse the query part so that the values are rawurlencode()'ed
		//can't just use parse_url() cos we could be dealing with a relative URL which
		//  parse_url() can't deal with.
		$queryStart = strpos($sURL, '?', strrpos($sURL, '/'));
		if ($queryStart !== FALSE)
		{
			$queryStart++;
			$queryEnd = strpos($sURL, '#', $queryStart);
			if ($queryEnd === FALSE)
				$queryEnd = strlen($sURL);
			$queryPart = substr($sURL, $queryStart, $queryEnd-$queryStart);
			parse_str($queryPart, $queryParts);
			$newQueryPart = "";
			if ($queryParts)
			{
				$first = true;
				foreach($queryParts as $key => $value)
				{
					if ($first)
						$first = false;
					else
						$newQueryPart .= ini_get('arg_separator.output');
					$newQueryPart .= rawurlencode($key).'='.rawurlencode($value);
				}
			} else if ($_SERVER['QUERY_STRING']) {
				//couldn't break up the query, but there's one there
				//possibly "http://url/page.html?query1234" type of query?
				//just encode it and hope it works
				$newQueryPart = rawurlencode($_SERVER['QUERY_STRING']);
			}
			$sURL = str_replace($queryPart, $newQueryPart, $sURL);
		}
		if ($iDelay)
			$this->script('window.setTimeout("window.location = \''.$sURL.'\';",'.($iDelay*1000).');');
		else
			$this->script('window.location = "'.$sURL.'";');
		return $this;
	}

	/*
		Function: script
		
		Response command that is used to execute a portion of javascript on
		the browser.  The script runs in it's own context, so variables declared
		locally, using the 'var' keyword, will no longer be available after the
		call.  To construct a variable that will be accessable globally, even
		after the script has executed, leave off the 'var' keyword.
		
		sJS - (string):  The script to execute.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function script($sJS)
	{
		return $this->addCommand(array('n'=>'js'),$sJS);
	}
	
	/*
		Function: call
		
		Response command that indicates that the specified javascript
		function should be called with the given (optional) parameters.
		
		arg1 - (string):  The name of the function to call.
		arg2 .. argn - arguments to be passed to the function.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function call() {
		$aArgs = func_get_args();
		$sFunc = array_shift($aArgs);
		$aData = $this->_buildObj($aArgs);
		
		return $this->addCommand(array('n'=>'jc','f'=>$sFunc),$aData);
	}
	
	/*
		Function: remove
		
		Response command used to remove an element from the document.
		
		sTarget - (string):  The id of the element to be removed.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function remove($sTarget)
	{
		return $this->addCommand(array('n'=>'rm','t'=>$sTarget),'');
	}
	
	/*
		Function: create
		
		Response command used to create a new element on the browser.
		
		sParent - (string):  The id of the parent element.
		sTag - (string):  The tag name to be used for the new element.
		sId - (string):  The id to assign to the new element.
		sType - (string, optional):  The type of tag, deprecated, use
			<xajaxResponse->createInput> instead.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function create($sParent, $sTag, $sId, $sType='')
	{
		if ($sType)
		{
			trigger_error("The \$sType parameter of addCreate has been deprecated.  Use the addCreateInput() method instead.", E_USER_WARNING);
			return;
		}
		return $this->addCommand(array('n'=>'ce','t'=>$sParent,'p'=>$sId),$sTag);
	}
	
	/*
		Function: insert
		
		Response command used to insert a new element just prior to the specified
		element.
		
		sBefore - (string):  The element used as a reference point for the 
			insertion.
		sTag - (string):  The tag to be used for the new element.
		sId - (string):  The id to be used for the new element.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function insert($sBefore, $sTag, $sId)
	{
		return $this->addCommand(array('n'=>'ie','t'=>$sBefore,'p'=>$sId),$sTag);
	}

	/*
		Function: insertAfter
		
		Response command used to insert a new element after the specified
		one.
		
		sAfter - (string):  The id of the element that will be used as a reference
			for the insertion.
		sTag - (string):  The tag name to be used for the new element.
		sId - (string):  The id to be used for the new element.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function insertAfter($sAfter, $sTag, $sId)
	{
		return $this->addCommand(array('n'=>'ia','t'=>$sAfter,'p'=>$sId),$sTag);
	}
	
	/*
		Function: createInput
		
		Response command used to create an input element on the browser.
		
		sParent - (string):  The id of the parent element.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new element.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function createInput($sParent, $sType, $sName, $sId)
	{
		return $this->addCommand(array('n'=>'ci','t'=>$sParent,'p'=>$sId,'c'=>$sType),$sName);
	}
	
	/*
		Function: insertInput
		
		Response command used to insert a new input element preceeding the
		specified element.
		
		sBefore - (string):  The id of the element to be used as the reference
			point for the insertion.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new input element.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function insertInput($sBefore, $sType, $sName, $sId)
	{
		return $this->addCommand(array('n'=>'ii','t'=>$sBefore,'p'=>$sId,'c'=>$sType),$sName);
	}
	
	/*
		Function: insertInputAfter
		
		Response command used to insert a new input element after the 
		specified element.
		
		sAfter - (string):  The id of the element that is to be used
			as the insertion point for the new element.
		sType - (string):  The type of the new input element.
		sName - (string):  The name of the new input element.
		sId - (string):  The id of the new input element.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function insertInputAfter($sAfter, $sType, $sName, $sId)
	{
		return $this->addCommand(array('n'=>'iia','t'=>$sAfter,'p'=>$sId,'c'=>$sType),$sName);
	}
	
	/*
		Function: setEvent
		
		Response command used to set an event handler on the browser.
		
		sTarget - (string):  The id of the element that contains the event.
		sEvent - (string):  The name of the event.
		sScript - (string):  The javascript to execute when the event is fired.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function setEvent($sTarget,$sEvent,$sScript)
	{
		return $this->addCommand(array('n'=>'ev','t'=>$sTarget,'p'=>$sEvent),$sScript);
	}
	
	function addEvent($sTarget,$sEvent,$sScript)
	{
		return $this->setEvent($sTarget,$sEvent,$sScript);
	}

	/*
		Function: addHandler
		
		Response command used to install an event handler on the specified element.
		
		sTarget - (string):  The id of the element.
		sEvent - (string):  The name of the event to add the handler to.
		sHandler - (string):  The javascript function to call when the event is fired.
		
		You can add more than one event handler to an element's event using this method.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function addHandler($sTarget,$sEvent,$sHandler)
	{	
		return $this->addCommand(array('n'=>'ah','t'=>$sTarget,'p'=>$sEvent),$sHandler);
	}
	
	/*
		Function: removeHandler
		
		Response command used to remove an event handler from an element.
		
		sTarget - (string):  The id of the element.
		sEvent - (string):  The name of the event.
		sHandler - (string):  The javascript function that is called when the 
			event is fired.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function removeHandler($sTarget,$sEvent,$sHandler)
	{
		return $this->addCommand(array('n'=>'rh','t'=>$sTarget,'p'=>$sEvent),$sHandler);
	}

	/*
		Function: setFunction
		
		Response command used to construct a javascript function on the browser.
		
		sFunction - (string):  The name of the function to construct.
		sArgs - (string):  Comma separated list of parameter names.
		sScript - (string):  The javascript code that will become the body of the
			function.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function setFunction($sFunction, $sArgs, $sScript)
	{
		return $this->addCommand(array('n'=>'sf','f'=>$sFunction,'p'=>$sArgs),$sScript);
	}
	
	/*
		Function: wrapFunction
		
		Response command used to construct a wrapper function around
		and existing javascript function on the browser.
		
		sFunction - (string):  The name of the existing function to wrap.
		sArgs - (string):  The comma separated list of parameters for the function.
		aScripts - (array):  An array of javascript code snippets that will
			be used to build the body of the function.  The first piece of code
			specified in the array will occur before the call to the original
			function, the second will occur after the original function is called.
		sReturnValueVariable - (string):  The name of the variable that will
			retain the return value from the call to the original function.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function wrapFunction($sFunction, $sArgs, $aScripts, $sReturnValueVariable)
	{
		return $this->addCommand(array('n'=>'wpf','f'=>$sFunction,'p'=>$sArgs,'c'=>$sReturnValueVariable),$aScripts);
	}
	
	/*
		Function: includeScript
		
		Response command used to load a javascript file on the browser.
		
		sFileName - (string):  The relative or fully qualified URI of the 
			javascript file.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function includeScript($sFileName)
	{
		return $this->addCommand(array('n'=>'in'),$sFileName);
	}

	/*
		Function: includeScriptOnce
		
		Response command used to include a javascript file on the browser
		if it has not already been loaded.
		
		sFileName - (string):  The relative for fully qualified URI of the
			javascript file.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function includeScriptOnce($sFileName)
	{
		return $this->addCommand(array('n'=>'ino'),$sFileName);
	}
	
	/*
		Function: removeScript
		
		Response command used to remove a SCRIPT reference to a javascript
		file on the browser.  Optionally, you can call a javascript function
		just prior to the file being unloaded (for cleanup).
		
		sFileName - (string):  The relative or fully qualified URI of the
			javascript file.
		sUnload - (string):  Name of a javascript function to call prior
			to unlaoding the file.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function removeScript($sFileName, $sUnload = '') {
		$this->addCommand(array('n'=>'rjs'), ('' != $sUnload) ? array($sFileName, $sUnload) : $sFileName);
		return $this;
	}
	
	/*
		Function: includeCSS
		
		Response command used to include a LINK reference to 
		the specified CSS file on the browser.  This will cause the
		browser to load and apply the style sheet.
		
		sFileName - (string):  The relative or fully qualified URI of
			the css file.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function includeCSS($sFileName)
	{
		return $this->addCommand(array('n'=>'css'),$sFileName);
	}
	
	/*
		Function: removeCSS
		
		Response command used to remove a LINK reference to 
		a CSS file on the browser.  This causes the browser to
		unload the style sheet, effectively removing the style
		changes it caused.
		
		sFileName - (string):  The relative or fully qualified URI
			of the css file.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function removeCSS($sFileName)
	{
		return $this->addCommand(array('n'=>'rcss'),$sFileName);
	}
	
	/*
		Function: waitForCSS
		
		Response command instructing xajax to pause while the CSS
		files are loaded.  The browser is not typically a multi-threading
		application, with regards to javascript code.  Therefore, the
		CSS files included or removed with <xajaxResponse->includeCSS> and 
		<xajaxResponse->removeCSS> respectively, will not be loaded or 
		removed until the browser regains control from the script.  This
		command returns control back to the browser and pauses the execution
		of the response until the CSS files, included previously, are
		loaded.
		
		iTimeout - (integer):  The number of 1/10ths of a second to pause
			before timing out and continuing with the execution of the
			response commands.
			
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function waitForCSS($iTimeout = 600) {
		$sData = "";
		$this->addCommand(array('n'=>'wcss', 'p'=>$iTimeout),$sData);
		return $this;
	}
	
	/*
		Function: waitFor
		
		Response command instructing xajax to delay execution of the response
		commands until a specified condition is met.  Note, this returns control
		to the browser, so that other script operations can execute.  xajax
		will continue to monitor the specified condition and, when it evaulates
		to true, will continue processing response commands.
		
		script - (string):  A piece of javascript code that evaulates to true 
			or false.
		tenths - (integer):  The number of 1/10ths of a second to wait before
			timing out and continuing with the execution of the response
			commands.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function waitFor($script, $tenths) {
		return $this->addCommand(array('n'=>'wf','p'=>$tenths), $script);
	}
	
	/*
		Function: sleep
		
		Response command which instructs xajax to pause execution
		of the response commands, returning control to the browser
		so it can perform other commands asynchronously.  After
		the specified delay, xajax will continue execution of the 
		response commands.
		
		tenths - (integer):  The number of 1/10ths of a second to
			sleep.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function sleep($tenths) {
		$this->addCommand(array('n'=>'s','p'=>$tenths), '');
		return $this;
	}
	
	/*
		Function: setReturnValue
		
		Stores a value that will be passed back as part of the response.
		When making synchronous requests, the calling javascript can
		obtain this value immediately as the return value of the
		<xajax.call> function.
		
		value - (mixed):  Any value.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function setReturnValue($value) {
		$this->returnValue = $value;
		return $this;
	}
	
	/*
		Function: getContentType
		
		Returns the current content type that will be used for the
		response packet.  (typically: "text/xml")
		
		Returns:
		
		string - The content type.
	*/
	function getContentType()
	{
		return 'text/xml';
	}
	
	/*
		Function: getOutput
	*/
	function getOutput()
	{
		ob_start();
		$this->_printXML();
		$this->_printResponse();
		return ob_get_clean();
	}
	
	/*
		Function: printOutput
		
		Prints the output, generated from the commands added to the response,
		that will be sent to the browser.
		
		Returns:
		
		string - The textual representation of the response commands.
	*/
	function printOutput()
	{
		$this->_printHeader();
		$this->_printXML();
		$this->_printResponse();
	}
	
	/*
		Function: _printHeader
		
		Used internally to generate the response headers.
	*/
	function _printHeader()
	{
		$objArgumentManager =& xajaxArgumentManager::getInstance();
		if (XAJAX_METHOD_GET == $objArgumentManager->getRequestMethod())
		{
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
		}

		$sCharacterSet = '';
		if ($this->sEncoding && 0 < strlen(trim($this->sEncoding))) {
			$sCharacterSet = '; charset="' . trim($this->sEncoding) . '"';
		}
		
		$sContentType = $this->getContentType();

		header('content-type: ' . $sContentType . ' ' . $sCharacterSet);
	}
	
	/*
		Function: _printXML
		
		Used internally to print XML start tag.
	*/
	function _printXML()
	{
		$encoding = '';
		
		if ($this->sEncoding && 0 < strlen(trim($this->sEncoding))) {
			$encoding = ' encoding="' . trim($this->sEncoding) . '"';
		}
		
		print '<?xml version="1.0"' . $encoding . ' ?'.'>';
	}
	
	/*
		Function: _printResponse
	
		Used internally to generate the command output.
	*/
	function _printResponse()
	{
		print '<xjx>';
		
		if (null != $this->returnValue)
		{
			print '<xjxrv>';
			if ('object' == gettype($this->returnValue) || is_array($this->returnValue))
			{
				print $this->_arrayToXML(
					$this->_buildObj(
						$this->returnValue
						)
					);
			}
			else print $this->returnValue;
			print '</xjxrv>';
		}

		if (is_array($this->aCommands))
		{
			foreach($this->aCommands as $aCommand)
			{
				$sData = $aCommand['data'];
				unset($aCommand['data']);
				print $this->_getXMLForCommand($aCommand, $sData);
			}
		}
		
		print '</xjx>';
	}
	
	/*
		Function: getCommandCount
		
		Returns:
		
		integer - The number of commands in the response.
	*/
	function getCommandCount()
	{
		return count($this->aCommands);
	}

	/*
		Function: loadCommands
		
		Merges the response commands from the specified <xajaxResponse>
		object with the response commands in this <xajaxResponse> object.
		
		mCommands - (object):  <xajaxResponse> object.
		bBefore - (boolean):  Add the new commands to the beginning 
			of the list.
			
	*/
	function loadCommands($mCommands, $bBefore=false)
	{
		if (is_a($mCommands, 'xajaxResponse')) {
			$this->returnValue = $mCommands->returnValue;
			
			if ($bBefore) {
				$this->aCommands = array_merge($mCommands->aCommands, $this->aCommands);
			}
			else {
				$this->aCommands = array_merge($this->aCommands, $mCommands->aCommands);
			}
		}
		else if (is_array($mCommands)) {
			if ($bBefore) {
				$this->aCommands = array_merge($mCommands, $this->aCommands);
			}
			else {
				$this->aCommands = array_merge($this->aCommands, $mCommands);
			}
		}
		else {
			if (!empty($mCommands))
				trigger_error("The xajax response output could not load other commands as data was not a valid array", E_USER_ERROR);
		}
	}
	
	function absorb($objResponse)
	{
		$this->loadCommands($objResponse);
	}
	
	/*
		Function: addPluginCommand
		
		Adds a response command that is generated by a plugin.
		
		objPlugin - (object):  A reference to a plugin object.
		aAttributes - (array):  Array containing the attributes for this
			response command.
		mData - (mixed):  The data to be sent with this command.
		
		Returns:
		
		object - The <xajaxResponse> object.
	*/
	function addPluginCommand($objPlugin, $aAttributes, $mData)
	{
		$aAttributes['plg'] = $objPlugin->getName();
		return $this->addCommand($aAttributes, $mData);
	}

	/*
		Function: _getXMLForCommand
		
		Generates an XML representation of the command.
		
		aAttributes - (array):  Associative array of attributes for this
			command.
		mData - (mixed):  The data to be associated with this command.
		
		Returns:
		
		string - The xml representation of the response command.
	*/
	function _getXMLForCommand($aAttributes, $mData)
	{
		$xml = '<cmd';
		foreach($aAttributes as $sAttribute => $sValue)
			if ($sAttribute)
				$xml .= " $sAttribute=\"$sValue\"";
		
		if (is_array($mData)) 
			$xml .= '>'.$this->_arrayToXML($mData).'</cmd>';
		else 
			$xml .= '>'.$this->_escape($mData).'</cmd>';
		
		return $xml;
	}
	
	/*
		Function: _arrayToXML
		
		Generates an XML representation of a php array suitable
		for inclusion in the response to the browser.  Arrays
		sent via this method will be converted into a javascript
		array on the browser.
		
		mArray - (array):  Array to be converted.
		
		Returns:
		
		string - An XML representation of the array.
	*/
	function _arrayToXML($mArray) {
		if (!is_array($mArray))
			return $this->_escape($mArray);
		
		$xml = '<xjxobj>';
		foreach ($mArray as $aKey=>$aKeyValues) {
			if (is_array($aKeyValues)) {
				$xml .= '<e>';
				foreach($aKeyValues as $sKey => $sValue) {
					$xml .= '<'.htmlentities($sKey).'>';
					$xml .= $this->_arrayToXML($sValue);
					$xml .= '</'.htmlentities($sKey).'>';	
				}
				$xml .= '</e>';
			} else {
				$xml .= '<e><k>';
				$xml .= $this->_escape($aKey);
				$xml .= '</k><v>';
				$xml .= $this->_escape($aKeyValues);
				$xml .= '</v></e>';
			}
		}
		$xml .= '</xjxobj>';

		return $xml;
	}
	
	/*
		Function: addCommand
		
		Add a response command to the array of commands that will
		be sent to the browser.
		
		aAttributes - (array):  Associative array of attributes that
			will describe the command.
		mData - (mixed):  The data to be associated with this command.
		
		Returns:
		
		object - The <xajaxResponse> command.
	*/
	function addCommand($aAttributes, $mData)
	{
		$aAttributes['data'] = $mData;
		$this->aCommands[] = $aAttributes;
		return $this;
	}
	
	/*
		Function: _escape
		
		Escape the specified data if necessary, so special characters in the 
		command data does not interfere with the structure of the response.
		
		This could be overridden to allow for transport encodings other than
		XML.
		
		sData - (string):  The data to be escaped.
		
		Returns:
		
		string - The escaped data.
	*/
	function _escape($sData)
	{
		if (false == is_numeric($sData))
			if (null == $sData)
				return '';
		
		$needCDATA = false;
		
		if ($this->bOutputEntities) {
			if (!function_exists('mb_convert_encoding'))
				trigger_error('The xajax response output could not be converted to HTML entities because the mb_convert_encoding function is not available', E_USER_NOTICE);
			
			$sData = call_user_func_array('mb_convert_encoding', array(&$sData, 'HTML-ENTITIES', $this->sEncoding));
		} else {
			if ((false !== strpos($sData, '<![CDATA['))
			|| (false !== strpos($sData, ']]>'))
			|| (htmlentities($sData) != $sData))
				$needCDATA = true;
			
			$segments = explode('<![CDATA[', $sData);
			$sData = '';
			foreach ($segments as $key => $segment) {
				$fragments = explode(']]>', $segment);
				$segment = '';
				foreach ($fragments as $fragment) {
					if ('' != $segment)
						$segment .= ']]]]><![CDATA[>';
					$segment .= $fragment;
				}
				if ('' != $sData)
					$sData .= '<![]]><![CDATA[CDATA[';
				$sData .= $segment;
			}
		}
		
		if ($needCDATA)
			$sData = '<![CDATA['.$sData.']]>';
		
		return $sData;
	}
	
	/*
		Function: _buildObj
		
		Recursively serializes a data structure in an array so that it can
		be sent to the browser.  This can be thought of as the opposite of
		<xajaxRequestProcessorPlugin->_parseObjXml>.
		
		mData - (mixed):  The data to be evaluated.
		
		Returns:
		
		mixed - The object constructed from the data.
	*/
	function _buildObj($mData) {
		if (gettype($mData) == 'object')
			$mData = get_object_vars($mData);
		
		if (is_array($mData)) {
			$aData = array();
			foreach ($mData as $key => $value)
				$aData[] = array(
// PHP only supports numeric and string array keys, so no need to buildObj
// on it.
//					'k'=>$this->_buildObj($key), 
					'k'=>$key,
					'v'=>$this->_buildObj($value)
				);
			return $aData;
		} else
			return $mData;
	}
	
}// end class xajaxResponse

class xajaxCustomResponse
{
	var $sOutput;
	var $sContentType;
	
	var $sEncoding;
	var $bOutputEntities;
	
	function xajaxCustomResponse($sContentType)
	{
		$this->sOutput = '';
		$this->sContentType = $sContentType;
		
		$objResponseManager =& xajaxResponseManager::getInstance();
		
		$this->sCharacterEncoding = $objResponseManager->getCharacterEncoding();
		$this->bOutputEntities = $objResponseManager->getOutputEntities();
	}
	
	function setCharacterEncoding($sCharacterEncoding)
	{
		$this->sCharacterEncoding = $sCharacterEncoding;
	}
	
	function setOutputEntities($bOutputEntities)
	{
		$this->bOutputEntities = $bOutputEntities;
	}
	
	function clear()
	{
		$this->sOutput = '';
	}
	
	function append($sOutput)
	{
		$this->sOutput .= $sOutput;
	}
	
	function absorb($objResponse)
	{
		if (false == is_a($objResponse, 'xajaxCustomResponse'))
			trigger_error("Error: Cannot mix types in a single response.", E_USER_ERROR);

		if ($objResponse->getContentType() != $this->getContentType())
			trigger_error("Error: Cannot mix content types in a single response.", E_USER_ERROR);
		
		if ($objResponse->getCharacterEncoding() != $this->getCharacterEncoding())
			trigger_error("Error: Cannot mix character encodings in a single response.", E_USER_ERROR);
		
		if ($objResponse->getOutputEntities() != $this->getOutputEntities())
			trigger_error("Error: Cannot mix output entities (true/false) in a single response.", E_USER_ERROR);

		$this->sOutput .= $objResponse->getOutput;
	}
	
	function getContentType()
	{
		return $this->sContentType;
	}
	
	function getCharacterEncoding()
	{
		return $this->sEncoding;
	}
	
	function getOutputEntities()
	{
		return $this->bOutputEntities;
	}
	
	function getOutput()
	{
		return $this->sOutput;
	}
	
	function printOutput()
	{
		$sContentType = $this->sContentType;
		$sCharacterSet = $this->sCharacterEncoding;
		
		header("content-type: {$sContentType}; charset={$sCharacterSet}");

		print $this->sOutput;
	}
}