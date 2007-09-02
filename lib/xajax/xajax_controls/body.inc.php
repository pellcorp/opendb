<?php

class clsBody extends xajaxControlContainer
{
	function clsBody($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('body', $aConfiguration);
		
		$this->sEndTag = 'optional';
	}
}
