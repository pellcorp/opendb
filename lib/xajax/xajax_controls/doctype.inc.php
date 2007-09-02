<?php

class clsDocType extends xajaxControlContainer
{
	var $sTag;
	
	var $sFormat;
	var $sVersion;
	var $sValidation;
	
	function clsDocType($sFormat, $sVersion, $sValidation)
	{
		xajaxControlContainer::xajaxControlContainer('DOCTYPE', array());
		
		$this->sTag = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD ';
		$this->sTag .= $sFormat;
		$this->sTag .= ' ';
		$this->sTag .= $sVersion;
		$this->sTag .= '//EN" ';
		
		if ('HTML' == $sFormat) {
			if ('4.0' == $sVersion) {
				if ('STRICT' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/html40/strict.dtd"';
				else if ('TRANSITIONAL' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/html40/loose.dtd"';
			} else if ('4.01' == $sVersion) {
				if ('STRICT' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/html401/strict.dtd"';
				else if ('TRANSITIONAL' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/html401/loose.dtd"';
			}
		} else if ('XHTML' == $sFormat) {
			if ('1.0' == $sVersion) {
				if ('STRICT' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"';
				else if ('TRANSITIONAL' == $sValidation)
					$this->sTag .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
			} else if ('1.1' == $sVersion) {
				$this->sTag .= '"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"';
			}
		} else
			trigger_error('Unsupported DOCTYPE tag.'
				. $this->backtrace(),
				E_USER_ERROR
				);
		
		$this->sTag .= '>';
		
		$this->sFormat = $sFormat;
		$this->sVersion = $sVersion;
		$this->sValidation = $sValidation;
	}
	
	function printHTML($sIndent='')
	{
		if ('XHTML' == $this->sFormat)
			print '<' . '?' . 'xml version="1.0"' . '?' . ">\n";
			
		print $this->sTag;
		
		print "\n";
		
		xajaxControlContainer::_printChildren($sIndent);
	}
}
