/* 	
 	Open Media Collectors Database
	Copyright (C) 2001-2012 by Jason Pell

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

function addSelectOption(form_element, prmpt, subtype)
{
	var value = prompt('Specify a new '+prmpt+' value ('+subtype+'):', '');
	while(value!=null)
	{
		var oldvalue = value;
        if(subtype == 'number')
			value = numericFilter(value);

		// make sure not duplicate - if duplicate set to null
		for(var i=0; i<form_element.options.length; i++)
		{
		    if(form_element.options[i].value == value)
		    {
		        alert('Duplicate value specified');
		        value = null;
		        break;
		    }
		}
		
		if(value == oldvalue && value.length>0)
		{
			var index = form_element.options.length;

			form_element.options[index] = new Option(value, value, false, false);
			form_element.options[index].selected = true;

			return true;
		}
		else
		{
            value = prompt('Specify a new '+prmpt+' value ('+subtype+'):', oldvalue);
		}
	}

	//else
	return false;
}

function updateSelectedOption(form_element, prmpt, subtype)
{
	if(form_element.options.selectedIndex!=null && form_element.options.selectedIndex >= 0)
	{
        var value = prompt('Modify '+prmpt+' value ('+subtype+'):', form_element.options[form_element.options.selectedIndex].value);
		while(value!=null)
		{
			var oldvalue = value;
	        if(subtype == 'number')
				value = numericFilter(value);

            // make sure not duplicate - if duplicate set to null
			for(var i=0; i<form_element.options.length; i++)
			{
			    // not a duplicate unless different from option we are changing.
			    if(form_element.options[form_element.options.selectedIndex].value != value &&
						form_element.options[i].value == value)
			    {
			        alert('Duplicate value specified');
			        value = null;
			        break;
			    }
			}
		
			if(value == oldvalue && value.length>0)
			{
				form_element.options[form_element.options.selectedIndex].value = value;
                form_element.options[form_element.options.selectedIndex].text = value;

				return true;
			}
			else
			{
	            value = prompt('Modify '+prmpt+' value ('+subtype+')', oldvalue);
			}
		}
	}

	return true;
}

function removeSelectedOption(form_element)
{
	if(form_element.options.selectedIndex!=null && form_element.options.selectedIndex >= 0)
	{
		var selectedIndex = form_element.options.selectedIndex;
		form_element.options[selectedIndex] = null;

		// if not last option selected, select next one down, otherwise select now last option.
		if(form_element.options.length > (selectedIndex))
			form_element.options[selectedIndex].selected = true;
		else
            form_element.options[selectedIndex-1].selected = true;
	}

	return true;
}