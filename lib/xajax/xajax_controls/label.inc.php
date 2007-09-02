<?php

class clsLabel extends xajaxControlContainer
{
	var $objFor;

	function clsLabel($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsLiteral');
			
		xajaxControlContainer::xajaxControlContainer('label', $aConfiguration);
	}

	function setControl(&$objControl)
	{
		if (false == is_a($objControl, 'xajaxControl'))
			trigger_error(
				'Invalid control passed to clsLabel::setControl(); should be xajaxControl.'
				. $this->backtrace(),
				E_USER_ERROR);

		$this->objFor =& $objControl;
	}

	function printHTML($sIndent='')
	{
		$this->objFor->verifyAttributeExists('id');

		$this->aAttributes['for'] = $this->objFor->aAttributes['id'];
		
		xajaxControlContainer::printHTML($sIndent);
	}
}
