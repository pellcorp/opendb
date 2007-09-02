<?php

class clsForm extends xajaxControlContainer
{
	function clsForm($aConfiguration=array())
	{
		if (false == isset($aConfiguration['attributes']))
			$aConfiguration['attributes'] = array();
		if (false == isset($aConfiguration['attributes']['method']))
			$aConfiguration['attributes']['method'] = 'POST';
		if (false == isset($aConfiguration['attributes']['action']))
			$aConfiguration['attributes']['action'] = '#';

		xajaxControlContainer::xajaxControlContainer('form', $aConfiguration);
	}

	function printHTML($sIndent='')
	{
		$this->verifyAttributesExist(array(
			'action', 
			'method', 
			'id'
			));

		xajaxControlContainer::printHTML($sIndent);
	}
}
