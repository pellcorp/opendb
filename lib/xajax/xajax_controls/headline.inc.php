<?php

class clsHeadline extends xajaxControlContainer
{
	function clsHeadline($sType, $aConfiguration=array())
	{
		if (0 < strpos($sType, '123456r'))
			trigger_error('Invalid type for headline control; should be 1,2,3,4,5,6 or r.'
				. $this->backtrace(),
				E_USER_ERROR
				);
		
		// headline controls can only 'contain' literal text
		if ('r' == $sType)
			$aConfiguration['allowed'] = array('*No children allowed*');
//		else
//			$aConfiguration['allowed'] = array('clsLiteral');
		
		xajaxControlContainer::xajaxControlContainer('h' . $sType, $aConfiguration);
		
		if ('r' == $sType)
			$this->sEndTag = 'forbidden';
	}

	function setEvent($sEvent, &$objRequest)
	{
		trigger_error(
			'clsTitle objects do not support events.'
			. $this->backtrace(),
			E_USER_ERROR);
	}
}
