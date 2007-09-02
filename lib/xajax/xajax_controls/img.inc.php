<?php

class clsImg extends xajaxControl
{
	function clsImg($aConfiguration=array())
	{
		xajaxControl::xajaxControl('img', $aConfiguration);
	}
	
	function printHTML($sIndent='')
	{
		$this->verifyAttributesExist(array(
			'src', 'alt'
			));
			
		xajaxControl::printHTML($sIndent);
	}
}

