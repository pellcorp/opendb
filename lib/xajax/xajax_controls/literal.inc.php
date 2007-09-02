<?php

class clsLiteral extends xajaxControl
{
	function clsLiteral($sText)
	{
		xajaxControl::xajaxControl('');

		$this->sText = $sText;
	}

	function printHTML($sIndent='')
	{
		echo $this->sText;
	}
}
