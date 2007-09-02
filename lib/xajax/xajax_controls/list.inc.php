<?php

class clsList extends xajaxControlContainer
{
	function clsList($sTag, $aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsLI');
		
		$this->clearEvent_AddItem();
			
		xajaxControlContainer::xajaxControlContainer($sTag, $aConfiguration);
	}
	
	function addItem($mItem, $mConfiguration=null)
	{
		if (null != $this->eventAddItem) {
			$objItem =& call_user_func($this->eventAddItem, $mItem, $mConfiguration);
			$this->addChild($objItem);
		} else {
			$objItem =& $this->_onAddItem($mItem, $mConfiguration);
			$this->addChild($objItem);
		}
	}
	
	function addItems($aItems, $mConfiguration=null)
	{
		foreach ($aItems as $mItem)
			$this->addItem($mItem, $mConfiguration);
	}
	
	function clearEvent_AddItem()
	{
		$this->eventAddItem = null;
	}
	
	function setEvent_AddItem($mFunction)
	{
		$this->eventAddItem = $mFunction;
	}
	
	function &_onAddItem($mItem, $mConfiguration)
	{
		$objItem =& new clsLI(array(
			'child' => new clsLiteral($mItem)
			));
		return $objItem;
	}
}

class clsUL extends clsList
{
	function clsUL($aConfiguration=array())
	{
		clsList::clsList('ul', $aConfiguration);
	}
}

class clsOL extends clsList
{
	function clsOL($aConfiguration=array())
	{
		clsList::clsList('ol', $aConfiguration);
	}
}

class clsLI extends xajaxControlContainer
{
	function clsLI($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('li', $aConfiguration);
	}
}
