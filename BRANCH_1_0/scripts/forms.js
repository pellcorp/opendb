/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2002 by Jason Pell

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
function setCheckboxes(form, elname, checked)
{
	for (var i=0; i < form.length; i++)
	{
		if (form.elements[i].type.toLowerCase() == 'checkbox' && 
				(elname == null || form.elements[i].name == elname || form.elements[i].name == elname+'[]' || (form.elements[i].name.substring(0,elname.length+1) == elname+'[' && form.elements[i].name.substring(form.elements[i].name.length-1) == ']')))
		{
			form.elements[i].checked = checked;
		}
	}
}

//http://www.openjs.com/scripts/examples/addfield.php
/*
	@param divArea - id of div whose first child element is the UL
	@param field - name of field to create
*/
function addInputField(ulElementId, name, size, maxlength)
{
	if(!document.getElementById) return; //Prevent older browsers from getting any further.
 	var list_area = document.getElementById(ulElementId);
 	
	if(document.createElement)
	{
		var li = document.createElement("li");
		var input = document.createElement("input");
		input.name = name+'[]';
		input.type = "text"; //Type of field - can be any valid input type like text,file,checkbox etc.
		input.size = size;
		input.maxlength = maxlength;
		li.appendChild(input);
		list_area.appendChild(li);
	} else { //Older Method
		list_area.innerHTML += "<li><input name='"+(name)+"[]' type='text' /></li>";
	}
}
