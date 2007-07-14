<?php
/* 
 * @version V1.0 2002/July/18 (c) Erh-Wen,Kuo (erhwenkuo@yahoo.com). All rights reserved.
 * Released under both BSD license and Lesser GPL library license. 
 * Whenever there is any discrepancy between the two licenses, 
 * the BSD license will take precedence. 
 *
 * purpose: This class is used to generate simple MS Excel file (xls) via
 * PHP & stream to user's browser.
 * Credits: This class is based on Erol Ozcan's psxlsgen
 *          Excel library functions.
 * 
 * Jpell:
 * 	Removed some of the functionality, as it was not required.
 */
class ExcelGen
{
	var  $excel_data;      	// a buffer for store excel stream data

	/*
	* @param $excel_filename Specify a filename for the excel spreadsheet once downloaded.
	* @param $excel_wksheetname Not implemented.
	*/
	function  ExcelGen()
	{
		$this->excel_data="";					// a buffer for store excel stream data
	
		$this->ExcelStart();
	}
		
	// start of the excel file
	function ExcelStart()
	{
		// start of excel file header
		$this->excel_data = pack( "vvvvvv", 0x809, 0x08, 0x00,0x10, 0x0, 0x0 );
	}
     
	// end of the excel file
	function ExcelEnd()
	{
		$this->excel_data .= pack( "vv", 0x0A, 0x00 );
	}

	// write a Number (double) into cell(row, col)
	function WriteNumber( $row, $col, $value )
	{
		$this->excel_data .= pack( "vvvvv", 0x0203, 14, $row, $col, 0x00 );
		$this->excel_data .= pack( "d", $value );
	}

	// write a text into cell(Row,Col)
	function WriteText( $row, $col, $value )
	{
		// Microsoft Excel has a 255 character limit for data written WriteText fields.
		$value = substr($value, 0, 255);
		
		$len = strlen( $value );
		$this->excel_data .= pack( "v*", 0x0204, 8 + $len, $row, $col, 0x00, $len );
		$this->excel_data .= $value;
	}
   
    /*
	* Get buffer and flush.  Allows us to flush data incrementally so the browser
	* is not waiting for complete file.
	*/
   	function GetBuffer($flush=TRUE)
	{
		if($flush)
		{
			$buffer = $this->excel_data;
			$this->excel_data = "";
			return $buffer;
		}
		else
			return $this->excel_data;
	}
	
   	/*
	* Return Excel data, minus any header's
	*/
   	function Close()
	{
		$this->ExcelEnd();
		return $this->excel_data;
	}
} // end of the class ExcelGen() class
?>
