<?php

class clsDocument extends xajaxControlContainer
{
	function clsDocument($aConfiguration=array())
	{
		if (isset($aConfiguration['attributes']))
			trigger_error(
				'clsDocument objects cannot have attributes.'
				. $this->backtrace(),
				E_USER_ERROR);

		xajaxControlContainer::xajaxControlContainer('', $aConfiguration);
	}

	function printHTML()
	{
		$this->_printChildren();
	}
}
