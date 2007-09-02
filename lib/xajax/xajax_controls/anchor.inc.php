<?php

class clsAnchor extends xajaxControlContainer
{
	function clsAnchor($aConfiguration=array())
	{
		if (false == isset($aConfiguration['attributes']))
			$aConfiguration['attributes'] = array();
		if (false == isset($aConfiguration['attributes']['href']))
			$aConfiguration['attributes']['href'] = '#';

		xajaxControlContainer::xajaxControlContainer('a', $aConfiguration);
	}

	function setEvent($sEvent, &$objRequest, $aParameters=array(), $sBeforeRequest='if (false == ', $sAfterRequest=') return false; ')
	{
		xajaxControl::setEvent($sEvent, $objRequest, $aParameters, $sBeforeRequest, $sAfterRequest);
	}

	function printHTML($sIndent='')
	{
		$this->verifyAttributesExist(
			array('href')
			);
		
		xajaxControlContainer::printHTML($sIndent);
	}
}
