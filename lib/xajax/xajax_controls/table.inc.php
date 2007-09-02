<?php

/*
	File: table.inc.php
	
	Contains the <xajaxControlContainer> derived class that aids in the construction 
	of HTML tables.

	Title: clsTable class

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: xajaxControl.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class clsTableRowContainer extends xajaxControlContainer
{
	var $eventAddRow;
	var $eventAddRowCell;
	
	function clsTableRowContainer($sTag, $aConfiguration=array())
	{
		if (false == is_a($this, 'clsTable'))
			$aConfiguration['allowed'] = array('clsTableRow');
		
		$this->clearEvent_AddRow();
		$this->clearEvent_AddRowCell();
			
		xajaxControlContainer::xajaxControlContainer($sTag, $aConfiguration);
	}

	function addRow($aCells, $mConfiguration=null)
	{
		if (null != $this->eventAddRow) {
			$objRow =& call_user_func($this->eventAddRow, $aCells, $mConfiguration);
			$this->addChild($objRow);
		} else {
			$objRow =& $this->_onAddRow($aCells, $mConfiguration);
			$this->addChild($objRow);
		}
	}
		
	function addRows($aRows, $mConfiguration=null)
	{
		foreach ($aRows as $aCells)
			$this->addRow($aCells, $mConfiguration);
	}
	
	function clearEvent_AddRow()
	{
		$this->eventAddRow = null;
	}
	function clearEvent_AddRowCell()
	{
		$this->eventAddRowCell = null;
	}
	
	function setEvent_AddRow($mFunction)
	{
		$mPrevious = $this->eventAddRow;
		$this->eventAddRow = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddRowCell($mFunction)
	{
		$mPrevious = $this->eventAddRowCell;
		$this->eventAddRowCell = $mFunction;
		return $mPrevious;
	}
	
	function &_onAddRow($aCells, $mConfiguration=null)
	{
		$objTableRow =& new clsTableRow();
		if (null != $this->eventAddRowCell)
			$objTableRow->setEvent_AddCell($this->eventAddRowCell);
		$objTableRow->addCells($aCells, $mConfiguration);
		return $objTableRow;
	}
}

/*
	Class: clsTable
	
	A <xajaxControlContainer> derived class that aids in the construction of HTML 
	tables.  Inherently, <xajaxControl> and <xajaxControlContainer> derived classes 
	support <xajaxRequest> based events using the <xajaxControl->setEvent> method.
*/
class clsTable extends clsTableRowContainer
{
	var $eventAddHeader;
	var $eventAddHeaderRow;
	var $eventAddHeaderRowCell;
	var $eventAddBody;
	var $eventAddBodyRow;
	var $eventAddBodyRowCell;
	var $eventAddFooter;
	var $eventAddFooterRow;
	var $eventAddFooterRowCell;
	
	/*
		Function: clsTable
		
		Constructs and initializes an instance of the class.
	*/
	function clsTable($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array(
			'clsTableRow',
			'clsTableHeader', 
			'clsTableBody', 
			'clsTableFooter'
			);
		
		$this->clearEvent_AddHeader();
		$this->clearEvent_AddHeaderRow();
		$this->clearEvent_AddHeaderRowCell();
		$this->clearEvent_AddBody();
		$this->clearEvent_AddBodyRow();
		$this->clearEvent_AddBodyRowCell();
		$this->clearEvent_AddFooter();
		$this->clearEvent_AddFooterRow();
		$this->clearEvent_AddFooterRowCell();
		
		clsTableRowContainer::clsTableRowContainer('table', $aConfiguration);
	}

	function addHeader($aRows, $mConfiguration=null)
	{
		if (null != $this->eventAddHeader) {
			$objHeader =& call_user_func($this->eventAddHeader, $aRows, $mConfiguration);
			$this->addChild($objHeader);
		} else {
			$objHeader =& $this->_onAddHeader($aRows, $mConfiguration);
			$this->addChild($objHeader);
		}
	}
	function addBody($aRows, $mConfiguration=null)
	{
		if (null != $this->eventAddBody) {
			$objBody =& call_user_func($this->eventAddBody, $aRows, $mConfiguration);
			$this->addChild($objBody);
		} else {
			$objBody =& $this->_onAddBody($aRows, $mConfiguration);
			$this->addChild($objBody);
		}
	}
	function addFooter($aRows, $mConfiguration=null)
	{
		if (null != $this->eventAddFooter) {
			$objFooter =& call_user_func($this->eventAddFooter, $aRows, $mConfiguration);
			$this->addChild($objFooter);
		} else {
			$objFooter =& $this->_onAddFooter($aRows, $mConfiguration);
			$this->addChild($objFooter);
		}
	}
		
	function addBodies($aBodies, $mConfiguration=null)
	{
		foreach ($aBodies as $aRows)
			$this->addBody($aRows, $mConfiguration);
	}

	function clearEvent_AddHeader()
	{
		$this->eventAddHeader = null;
	}
	function clearEvent_AddHeaderRow()
	{
		$this->eventAddHeaderRow = null;
	}
	function clearEvent_AddHeaderRowCell()
	{
		$this->eventAddHeaderRowCell = null;
	}
	function clearEvent_AddBody()
	{
		$this->eventAddBody = null;
	}
	function clearEvent_AddBodyRow()
	{
		$this->eventAddBodyRow = null;
	}
	function clearEvent_AddBodyRowCell()
	{
		$this->eventAddBodyRowCell = null;
	}
	function clearEvent_AddFooter()
	{
		$this->eventAddFooter = null;
	}
	function clearEvent_AddFooterRow()
	{
		$this->eventAddFooterRow = null;
	}
	function clearEvent_AddFooterRowCell()
	{
		$this->eventAddFooterRowCell = null;
	}
	
	function setEvent_AddHeader($mFunction)
	{
		$mPrevious = $this->eventAddHeader;
		$this->eventAddHeader = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddHeaderRow($mFunction)
	{
		$mPrevious = $this->eventAddHeaderRow;
		$this->eventAddHeaderRow = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddHeaderRowCell($mFunction)
	{
		$mPrevious = $this->eventAddHeaderRowCell;
		$this->eventAddHeaderRowCell = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddBody($mFunction)
	{
		$mPrevious = $this->eventAddBody;
		$this->eventAddBody = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddBodyRow($mFunction)
	{
		$mPrevious = $this->eventAddBodyRow;
		$this->eventAddBodyRow = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddBodyRowCell($mFunction)
	{
		$mPrevious = $this->eventAddBodyRowCell;
		$this->eventAddBodyRowCell = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddFooter($mFunction)
	{
		$mPrevious = $this->eventAddFooter;
		$this->eventAddFooter = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddFooterRow($mFunction)
	{
		$mPrevious = $this->eventAddFooterRow;
		$this->eventAddFooterRow = $mFunction;
		return $mPrevious;
	}
	function setEvent_AddFooterRowCell($mFunction)
	{
		$mPrevious = $this->eventAddFooterRowCell;
		$this->eventAddFooterRowCell = $mFunction;
		return $mPrevious;
	}
	
	function &_onAddHeader($aRows, $mConfiguration)
	{
		$objTableHeader =& new clsTableHeader();
		if (null != $this->eventAddHeaderRow)
			$objTableHeader->setEvent_AddRow($this->eventAddHeaderRow);
		if (null != $this->eventAddHeaderRowCell)
			$objTableHeader->setEvent_AddRowCell($this->eventAddHeaderRowCell);
		$objTableHeader->addRows($aRows, $mConfiguration);
		return $objTableHeader;
	}
	function &_onAddBody($aRows, $mConfiguration)
	{
		$objTableBody =& new clsTableBody();
		if (null != $this->eventAddBodyRow)
			$objTableBody->setEvent_AddRow($this->eventAddBodyRow);
		if (null != $this->eventAddBodyRowCell)
			$objTableBody->setEvent_AddRowCell($this->eventAddBodyRowCell);
		$objTableBody->addRows($aRows, $mConfiguration);
		return $objTableBody;
	}
	function &_onAddFooter($aRows, $mConfiguration)
	{
		$objTableFooter =& new clsTableFooter();
		if (null != $this->eventAddFooterRow)
			$objTableFooter->setEvent_AddRow($this->eventAddFooterRow);
		if (null != $this->eventAddFooterRowCell)
			$objTableFooter->setEvent_AddRowCell($this->eventAddFooterRowCell);
		$objTableFooter->addRows($aRows, $mConfiguration);
		return $objTableFooter;
	}
}

/*
	Class: clsTableHeader
*/
class clsTableHeader extends clsTableRowContainer
{
	/*
		Function: clsTableHeader
		
		Constructs and initializes an instance of the class.
	*/
	function clsTableHeader($aConfiguration=array())
	{
		clsTableRowContainer::clsTableRowContainer('thead', $aConfiguration);
	}
}

/*
	Class: clsTableBody
*/
class clsTableBody extends clsTableRowContainer
{
	/*
		Function: clsTableBody
		
		Constructs and initializes an instance of the class.
	*/
	function clsTableBody($aConfiguration=array())
	{
		clsTableRowContainer::clsTableRowContainer('tbody', $aConfiguration);
	}
}

/*
	Class: clsTableFooter
*/
class clsTableFooter extends clsTableRowContainer
{
	/*
		Function: clsTableFooter
		
		Constructs and initializes an instance of the class.
	*/
	function clsTableFooter($aConfiguration=array())
	{
		clsTableRowContainer::clsTableRowContainer('tfoot', $aConfiguration);
	}
}

/*
	Class: clsTableRow
*/
class clsTableRow extends xajaxControlContainer
{
	var $eventAddCell;
	
	/*
		Function: clsTableRow
		
		Constructs and initializes an instance of the class.
	*/
	function clsTableRow($aConfiguration=array())
	{
		$aConfiguration['allowed'] = array('clsTableCell');
		
		$this->clearEvent_AddCell();
			
		xajaxControlContainer::xajaxControlContainer('tr', $aConfiguration);
	}
	
	function addCell($mCell, $mConfiguration=null)
	{
		if (null != $this->eventAddCell) {
			$objCell =& call_user_func($this->eventAddCell, $mCell, $mConfiguration);
			$this->addChild($objCell);
		} else {
			$objCell =& $this->_onAddCell($mCell, $mConfiguration);
			$this->addChild($objCell);
		}
	}
	
	function addCells($aCells, $mConfiguration=null)
	{
		foreach ($aCells as $mCell)
			$this->addCell($mCell, $mConfiguration);
	}
	
	function clearEvent_AddCell()
	{
		$this->eventAddCell = null;
	}
	
	function setEvent_AddCell($mFunction)
	{
		$mPrevious = $this->eventAddCell;
		$this->eventAddCell = $mFunction;
		return $mPrevious;
	}
	
	function &_onAddCell($mCell, $mConfiguration=null)
	{
		return new clsTableCell(array(
			'child' => new clsLiteral($mCell)
			));
	}
}

/*
	Class: clsTableCell
*/
class clsTableCell extends xajaxControlContainer
{
	/*
		Function: clsTableCell
		
		Constructs and initializes an instance of the class.
	*/
	function clsTableCell($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('td', $aConfiguration);
	}
}
