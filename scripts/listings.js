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

function doChecks(checked, form, cbname)
{
	for (var i=0; i < form.length; i++)
	{
		if (form.elements[i].type.toLowerCase() == 'checkbox' && form.elements[i].name == cbname)
		{
			form.elements[i].checked = checked;
		}
	}
}

function doRemoveInterestAllInterestLevel(imgSrc, altText)
{
	for (var i=0; i < document.images.length; i++)
	{
		var currImgSrc = document.images[i].src;
		if (currImgSrc != null && currImgSrc.indexOf("interest_1") >= 0){
			// We update the images
			document.images[i].src = imgSrc;
			document.images[i].alt = altText;
			document.images[i].title = altText;
			
			// We update the input fields with the next value
			// The image id should be interest_level_ITEMID_INSTANCENO. 
			// We get the ITEMID and the INSTANCENO
			var itemId = document.images[i].id;
			var re = /interest_level_(\d*)_(\d*)/;
			re.exec(itemId); 
			// We use the ITEMID to update the input field
			var newValueField = document.getElementById("new_level_value_" + RegExp.$1 + "_" + RegExp.$2);
			if(newValueField != null){
				newValueField.value = 1;
			}
		}
	}
}

// At least one element must be checked.
function isChecked(form, cbname)
{
	for (var i=0; i < form.length; i++)
	{
		if (form.elements[i].type.toLowerCase() == 'checkbox' && form.elements[i].name == cbname && form.elements[i].checked)
			return true;
	}
	//else
	return false;
}

function doFormSubmit(form, script_uri, operation)
{
	form.action = script_uri;
	form.op.value = operation;
	form.submit();
}

