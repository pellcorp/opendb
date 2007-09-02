<?php

class clsHTML extends xajaxControlContainer
{
	function clsHTML($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array(
			'clsHead',
			'clsBody'
			);
			
		xajaxControlContainer::xajaxControlContainer('html', $aConfiguration);
		
		$this->sEndTag = 'optional';
	}
}
