<?php

class clsButton extends xajaxControlContainer
{
	function clsButton($aConfiguration=array())
	{
		// button controls can only 'contain' literal text
		$aConfiguration['allowed'] = array('clsLiteral');

		xajaxControlContainer::xajaxControlContainer('button', $aConfiguration);
	}

	function printHTML($sIndent='')
	{
		$this->verifyAttributeExists('id');
		
		xajaxControlContainer::printHTML($sIndent);
	}
}
