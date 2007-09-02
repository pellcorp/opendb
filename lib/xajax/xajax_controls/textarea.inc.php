<?php

class clsTextArea extends xajaxControlContainer
{
	function clsTextArea($aConfiguration=array())
	{
		// text area controls can only contain literal text
		$aConfiguration['allowed'] = array('clsLiteral');
		
		xajaxControlContainer::xajaxControlContainer('textarea', $aConfiguration);
	}

	function printHTML($sIndent='')
	{
		$this->verifyAttributesExist(
			array('name')
			);
		
		xajaxControlContainer::printHTML($sIndent);
	}
}
