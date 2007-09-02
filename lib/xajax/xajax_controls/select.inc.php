<?php

/*
	Class: clsSelect
	
	A <xajaxControlContainer> derived class that assists in the construction
	of an HTML select control.
	
	This control can only accept <clsOption> controls as children.
*/
class clsSelect extends xajaxControlContainer
{
	/*
		Function: clsSelect
		
		Construct and initialize an instance of the class.  See <xajaxControlContainer>
		for details regarding the aConfiguration parameter.
	*/
	function clsSelect($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsOption', 'clsOptionGroup');
			
		xajaxControlContainer::xajaxControlContainer('select', $aConfiguration);
	}
	
	/*
		Function: addOption
		
		Used to add a single option to the options list.
		
		sValue - (string):  The value that is returned as the form value
			when this option is the selected option.
		sText - (string):  The text that is displayed in the select box when
			this option is the selected option.
	*/
	function addOption($sValue, $sText)
	{
		$optionNew =& new clsOption();
		$optionNew->setValue($sValue);
		$optionNew->setText($sText);
		$this->addChild($optionNew);
	}
	
	/*
		Function: addOptions
		
		Used to add a list of options.
		
		aOptions - (associative array):  A list of key/value pairs that will
			be passed to <clsSelect->addOption>.
	*/
	function addOptions($aOptions, $aFields=array())
	{
		if (0 == count($aFields))
			foreach ($aOptions as $sValue => $sText)
				$this->addOption($sValue, $sText);
		else if (1 < count($aFields))
			foreach ($aOptions as $aOption)
				$this->addOption($aOption[$aFields[0]], $aOption[$aFields[1]]);
		else
			trigger_error('Invalid list of fields passed to clsSelect::addOptions; should be array of two strings.'
				. $this->backtrace(),
				E_USER_ERROR
				);
	}
}

/*
	Class: clsOptionGroup
	
	A <xajaxControlContainer> derived class that can be used around a list of <clsOption>
	objects to help the user find items in a select list.
*/
class clsOptionGroup extends xajaxControlContainer
{
	function clsOptionGroup($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsOption');
		
		xajaxControlContainer::xajaxControlContainer('optgroup', $aConfiguration);
	}
	
	/*
		Function: addOption
		
		Used to add a single option to the options list.
		
		sValue - (string):  The value that is returned as the form value
			when this option is the selected option.
		sText - (string):  The text that is displayed in the select box when
			this option is the selected option.
	*/
	function addOption($sValue, $sText)
	{
		$optionNew =& new clsOption();
		$optionNew->setValue($sValue);
		$optionNew->setText($sText);
		$this->addChild($optionNew);
	}
	
	/*
		Function: addOptions
		
		Used to add a list of options.
		
		aOptions - (associative array):  A list of key/value pairs that will
			be passed to <clsSelect->addOption>.
	*/
	function addOptions($aOptions, $aFields=array())
	{
		if (0 == count($aFields))
			foreach ($aOptions as $sValue => $sText)
				$this->addOption($sValue, $sText);
		else if (1 < count($aFields))
			foreach ($aOptions as $aOption)
				$this->addOption($aOption[$aFields[0]], $aOption[$aFields[1]]);
		else
			trigger_error('Invalid list of fields passed to clsOptionGroup::addOptions; should be array of two strings.'
				. $this->backtrace(),
				E_USER_ERROR
				);
	}
}

/*
	Class: clsOption
	
	A <xajaxControlContainer> derived class that assists with the construction
	of HTML option tags that will be assigned to an HTML select tag.

	This control can only accept <clsLiteral> objects as children.
*/
class clsOption extends xajaxControlContainer
{
	/*
		Function: clsOption
		
		Constructs and initializes an instance of this class.  See <xajaxControlContainer>
		for more information regarding the aConfiguration parameter.
	*/
	function clsOption($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsLiteral');
			
		xajaxControlContainer::xajaxControlContainer('option', $aConfiguration);
	}
	
	/*
		Function: setValue
		
		Used to set the value associated with this option.  The value is sent as the
		value of the select control when this is the selected option.
	*/
	function setValue($sValue)
	{
		$this->setAttribute('value', $sValue);
	}
	
	/*
		Function: setText
		
		Sets the text to be shown in the select control when this is the 
		selected option.
	*/
	function setText($sText)
	{
		$this->clearChildren();
		$this->addChild(new clsLiteral($sText));
	}
}
