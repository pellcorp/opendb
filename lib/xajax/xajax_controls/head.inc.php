<?php

class clsHead extends xajaxControlContainer
{
	var $objXajax;
	
	function clsHead($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array(
			'clsScript',
			'clsStyle',
			'clsTitle',
			'clsLink',
			'clsMeta'
			);
		
		$this->objXajax = null;
		if (isset($aConfiguration['xajax']))
			$this->setXajax($aConfiguration['xajax']);
			
		xajaxControlContainer::xajaxControlContainer('head', $aConfiguration);
		
		$this->sEndTag = 'optional';
	}
	
	function setXajax(&$objXajax)
	{
		$this->objXajax =& $objXajax;
	}

	function _printChildren($sIndent='')
	{
		if (null != $this->objXajax)
			$this->objXajax->printJavascript();
		
		xajaxControlContainer::_printChildren($sIndent);
	}
}

class clsScript extends xajaxControlContainer
{
	function clsScript($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array(
			'clsLiteral'
			);
		
		xajaxControlContainer::xajaxControlContainer('script', $aConfiguration);
	}
}

class clsStyle extends xajaxControlContainer
{
	function clsStyle($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array(
			'clsLiteral'
			);
		
		xajaxControlContainer::xajaxControlContainer('style', $aConfiguration);
	}
}

class clsLink extends xajaxControl
{
	function clsLink($aConfiguration=array())
	{
		xajaxControl::xajaxControl('link', $aConfiguration);
	}
}
