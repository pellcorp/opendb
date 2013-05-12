/*
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell
*/

/*
	row 2 up, is the equivalent of swapping row 2 and 1

	copy row 1 to buffer
	copy row 2 to row 1
	copy buffer to row 2
*/
function moveRowUp(form, element)
{
	var row = get_form_element_name_index(element.name);
	if(row > 0)
	{
		fArr = copyRowToArray(form, row-1);
		copyRowToRow(form, row, row-1);
		copyArrayToRow(form, fArr, row);

		var rowCount = get_form_element_count(form, 'column_no');
		setAllsRowClass(rowCount, 'data');
		setRowClass(row-1, 'dataHighlight');
	}
	else
	{
		alert('Cannot move row up!');
		return false;
	}	
}

/*
	row 2 down, is the equivalent of swapping row 2 and 3

	copy row 3 to buffer
	copy row 2 to row 3
	copy buffer to row 2
*/
function moveRowDown(form, element)
{
	var row = get_form_element_name_index(element.name);
	var rowCount = get_form_element_count(form, 'column_no');
	if(row < (rowCount-1))
	{
		fArr = copyRowToArray(form, (row-0)+1);
		copyRowToRow(form, row, (row-0)+1);
		copyArrayToRow(form, fArr, row);

		setAllsRowClass(rowCount, 'data');
		setRowClass((row-0)+1, 'dataHighlight');
	}
	else
	{
		alert('Cannot move row down!');
		return false;
	}
}

var listOfColumns = new Array(
		'is_new_row', 'column_no', 'column_type', 's_field_type', 's_attribute_type' , 'override_prompt', 'printable_support_ind', 
		'orderby_support_ind', 'orderby_datatype', 'orderby_default_ind', 'orderby_sort_order'
		);

/**
This code is really really really messy, it should be consolidated so that we don't repeat large sections of
code for different columns,etc
*/
function doOnChange(form, element)
{
	var row = get_form_element_name_index(element.name);
	var name = get_form_element_name(element.name);
	
	var s_field_type = get_row_element(form, row, 's_field_type');
	
	// if get_row_element does not work here, it won't work for anything so bail out now
	if(!s_field_type) 
		return;
	
	var column_type = get_row_element(form, row, 'column_type');
	var s_field_type = get_row_element(form, row, 's_field_type');
	var s_attribute_type = get_row_element(form, row, 's_attribute_type');
	var override_prompt = get_row_element(form, row, 'override_prompt');
	var printable_support_ind = get_row_element(form, row, 'printable_support_ind');
	var orderby_support_ind = get_row_element(form, row, 'orderby_support_ind');
	var orderby_datatype = get_row_element(form, row, 'orderby_datatype');
	var orderby_default_ind = get_row_element(form, row, 'orderby_default_ind');
	var orderby_sort_order = get_row_element(form, row, 'orderby_sort_order');

	if(name == 'column_type')
	{
		var val = get_select_value(element);
		
		s_field_type.disabled = false;
		s_attribute_type.disabled = false;
		orderby_support_ind.disabled = false;
		orderby_support_ind.checked = true;
		
		removeEmptySelectOption(orderby_datatype);
		orderby_datatype.disabled = false;
		
		orderby_default_ind.checked = false;
		orderby_default_ind.disabled = false;
			
		insertEmptySelectOption(orderby_sort_order);
		orderby_sort_order.disabled = true;
		
		override_prompt.disabled = false;
		printable_support_ind.disabled = false;
		printable_support_ind.checked = true;

		if(val == 's_field_type')
		{
			removeEmptySelectOption(s_field_type);
			
			insertEmptySelectOption(s_attribute_type);
			s_attribute_type.disabled=true;
		}
		else if(val == 's_attribute_type')
		{
			insertEmptySelectOption(s_field_type);
			s_field_type.disabled=true;

			removeEmptySelectOption(s_attribute_type);
		}
		else
		{
			insertEmptySelectOption(s_field_type);
			s_field_type.disabled=true;
			
			insertEmptySelectOption(s_attribute_type);
			s_attribute_type.disabled=true;
			
			orderby_support_ind.checked = false;
			orderby_support_ind.disabled = true;
			
			insertEmptySelectOption(orderby_datatype);
			orderby_datatype.disabled = true;
			
			orderby_default_ind.checked = false;
			orderby_default_ind.disabled = true;
			
			insertEmptySelectOption(orderby_sort_order);
			orderby_sort_order.disabled = true;
			
			if(val == 'action_links' || val == '')
			{			
				printable_support_ind.checked = false;
				printable_support_ind.disabled = true;
			}
		}
	}
	else if(name == 's_field_type')
	{
		var val = get_select_value(element);
		
		// do nothing for the moment
		if(val == 'STATUSCMNT' || val == 'RATING')
		{
			orderby_support_ind.checked = false;
			orderby_support_ind.disabled = true;
			
			insertEmptySelectOption(orderby_datatype);
			orderby_datatype.disabled = true;
			
			orderby_default_ind.checked = false;
			orderby_default_ind.disabled = true;
			
			insertEmptySelectOption(orderby_sort_order);
			orderby_sort_order.disabled = true;
			
			orderby_default_ind.checked = false;
			orderby_default_ind.disabled = false;
		}
		else
		{
			orderby_support_ind.disabled = false;
			orderby_support_ind.checked = true;
			
			removeEmptySelectOption(orderby_datatype);
			orderby_datatype.disabled = false;
		}
	}
	else if(name == 's_attribute_type')
	{
		// nothing
	}
	else if(name == 'orderby_support_ind')
	{
		if(!element.checked)
		{
			insertEmptySelectOption(orderby_datatype);
			orderby_datatype.disabled = true;
			
			orderby_default_ind.disabled = true;
			orderby_default_ind.checked = false;
			
			insertEmptySelectOption(orderby_sort_order);
			orderby_sort_order.disabled = true;
		}
		else
		{
			removeEmptySelectOption(orderby_datatype);
			orderby_datatype.disabled = false;
			
			orderby_default_ind.disabled = false;
			orderby_default_ind.checked = false;
		}
	}
	else if(name == 'orderby_default_ind')
	{
		if(!element.checked)
		{
			insertEmptySelectOption(orderby_sort_order);
			orderby_sort_order.disabled = true;
		}
		else
		{
			removeEmptySelectOption(orderby_sort_order);
			orderby_sort_order.disabled = false;
		}
	}
	
	//else
	return true;
}

function setRowClass(row, cls)
{
	//	var tds = document.getElementById('button['+row+']');
	//	tds.className = cls;
	
	for(var i=0; i<listOfColumns.length; i++)
	{
		tds = document.getElementById(listOfColumns[i]+'['+row+']');
		if(tds)
			tds.className = cls;
	}
}

function setAllsRowClass(rowcount, cls)
{
	for(var i=0; i<rowcount; i++)
	{
		//	var tds = document.getElementById('button['+i+']');
		//	tds.className = cls;
		
		for(var j=0; j<listOfColumns.length; j++)
		{
			tds = document.getElementById(listOfColumns[j]+'['+i+']');
			if(tds)
				tds.className = cls;
		}
	}
}

function copyRowToRow(form, rowFrom, rowTo)
{
	var vArr = copyRowToArray(form, rowFrom);
	copyArrayToRow(form, vArr, rowTo);
}

function copyRowToArray(form, row)
{
	var fArr = new Array();
	for(var i=0; i<listOfColumns.length; i++)
	{
		fArr[listOfColumns[i]] = Array();
		
		fArr[listOfColumns[i]]['disabled'] = form[listOfColumns[i]+'['+row+']'].disabled;
		
		if(form[listOfColumns[i]+'['+row+']'].type == 'select-one' || form[listOfColumns[i]+'['+row+']'].type == 'select')
		{
			fArr[listOfColumns[i]]['value'] = form[listOfColumns[i]+'['+row+']'].options[form[listOfColumns[i]+'['+row+']'].options.selectedIndex].value;
		}
		else if(form[listOfColumns[i]+'['+row+']'].type == 'checkbox')
		{
			if(form[listOfColumns[i]+'['+row+']'].checked)
			{
				fArr[listOfColumns[i]]['value'] = form[listOfColumns[i]+'['+row+']'].value;
			}
		}
		else
		{
			fArr[listOfColumns[i]]['value'] = form[listOfColumns[i]+'['+row+']'].value;
		}
	}
	
	return fArr;
}

function copyArrayToRow(form, fArr, row)
{
	for(var i=0; i<listOfColumns.length; i++)
	{
		form[listOfColumns[i]+'['+row+']'].disabled = false;
		
		if(form[listOfColumns[i]+'['+row+']'].type == 'select-one' || form[listOfColumns[i]+'['+row+']'].type == 'select')
		{
			// unset current selected element
			form[listOfColumns[i]+'['+row+']'].options[form[listOfColumns[i]+'['+row+']'].options.selectedIndex].selected = false;
			
			if(fArr[listOfColumns[i]]['value'] == '')
			{
				insertEmptySelectOption(form[listOfColumns[i]+'['+row+']']);
			}
			else
			{	
				for(var j=0; j<form[listOfColumns[i]+'['+row+']'].options.length; j++)
				{
					if(fArr[listOfColumns[i]]['value'] != null && fArr[listOfColumns[i]]['value'] == form[listOfColumns[i]+'['+row+']'].options[j].value)
					{
						form[listOfColumns[i]+'['+row+']'].options[j].selected = true;
						break;
					}
				}
			}
		}
		else if(form[listOfColumns[i]+'['+row+']'].type == 'checkbox')
		{
			// by default its unchecked
			form[listOfColumns[i]+'['+row+']'].checked = false;
			
			if(fArr[listOfColumns[i]]['value'] != null && fArr[listOfColumns[i]]['value'] == form[listOfColumns[i]+'['+row+']'].value)
			{
				form[listOfColumns[i]+'['+row+']'].checked = true;
			}
		}
		else
		{
			form[listOfColumns[i]+'['+row+']'].value = fArr[listOfColumns[i]]['value'];
		}
		
		form[listOfColumns[i]+'['+row+']'].disabled = fArr[listOfColumns[i]]['disabled'];
	}
}

/*
*/
function removeEmptySelectOption(select)
{
	if(select.options[0].value == '')
	{
		select.options[0] = null;
	}
}

/**
*/
function insertEmptySelectOption(select)
{
	if(select.options[0].value != '')
	{
		// first of all install it, but then need to bubble it up to the top
		select.options[select.options.length] = new Option('', '', false, false);
		
		for(var i=select.options.length-1; i>0; i--)
		{
			select.options[i].text = select.options[i-1].text;
			select.options[i].value = select.options[i-1].value;
		}
		
		select.options[0].text = '';
		select.options[0].value = '';
	}
	
	select.selectedIndex = 0;
}

function get_row_element(form, row, name)
{
	return form[name+'['+row+']'];
}

function get_select_value(select)
{
	return select.options[select.options.selectedIndex].value;
}

function get_form_element_count(form, name)
{
	var count = 0;
	
	for (var i=0; i < form.length; i++)
	{
		if (form.elements[i].type.toLowerCase() == 'hidden' && 
				form.elements[i].name.substring(0, name.length+1) == name+'[')
		{
			count++;	
		}
	}
	
	return count;
}

/*
	Retrieves array index element from name
*/
function get_form_element_name_index(name)
{
	start = name.indexOf('[');
	if(start!=-1)
	{
		end = name.indexOf(']');
		if(end != -1)
		{
			var index = name.substring(start+1, end);
			return index;
		}		
	}
	
	//else
	return null;
}

function get_form_element_name(name)
{
	start = name.indexOf('[');
	if(start!=-1)
	{
		end = name.indexOf(']');
		if(end != -1)
		{
			var index = name.substring(0, start);
			return index;
		}		
	}
	
	//else
	return null;
}