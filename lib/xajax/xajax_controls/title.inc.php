<?php

class clsTitle extends xajaxControlContainer
{
	function clsTitle($aConfiguration=array())
	{
		// title controls can only 'contain' literal text
		$aConfiguration['allowed'] = array('clsLiteral');
		
		xajaxControlContainer::xajaxControlContainer('title', $aConfiguration);
		
		$this->sEndTag = 'required';
	}

	function setEvent($sEvent, &$objRequest)
	{
		trigger_error(
			'clsTitle objects do not support events.'
			. $this->backtrace(),
			E_USER_ERROR);
	}
}
