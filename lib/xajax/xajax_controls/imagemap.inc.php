<?php

class clsMap extends xajaxControlContainer
{
	function clsMap($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsArea');
		
		xajaxControlContainer::xajaxControlContainer('map', $aConfiguration);
	}
}

class clsArea extends xajaxControl
{
	function clsArea($aConfiguration=array())
	{
		xajaxControl::xajaxControl('area', $aConfiguration);
	}
}
