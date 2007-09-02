<?php

class clsInput extends xajaxControl
{
	function clsInput($aConfiguration=array())
	{
		xajaxControl::xajaxControl('input', $aConfiguration);
	}

	function printHTML($sIndent='')
	{
		$this->verifyAttributesExist(
			array('type','name')
			);
		
		xajaxControl::printHTML($sIndent);
	}
}

class clsInputWithLabel extends clsInput
{
	var $objLabel;
	var $sWhere;
	var $objBreak;
	
	function clsInputWithLabel($sLabel, $sWhere, $aConfiguration=array())
	{
		clsInput::clsInput($aConfiguration);
		
		$this->objLabel =& new clsLabel(array(
			'child' => new clsLiteral($sLabel)
			));
		$this->objLabel->setControl($this);
		
		$this->sWhere = $sWhere;
		
		$this->objBreak =& new clsBreak();
	}
	
	function printHTML($sIndent='')
	{
		if ('left' == $this->sWhere || 'above' == $this->sWhere)
			$this->objLabel->printHTML($sIndent);
		if ('above' == $this->sWhere)
			$this->objBreak->printHTML($sIndent);
		
		clsInput::printHTML($sIndent);

		if ('below' == $this->sWhere)
			$this->objBreak->printHTML($sIndent);
		if ('right' == $this->sWhere || 'below' == $this->sWhere)
			$this->objLabel->printHTML($sIndent);
	}
}