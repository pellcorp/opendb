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

// Any characters mentioned in escapeChars
// will be escaped by specifying the escapeChar
function escapeChars(inval, escapeChars)
{
	var retval='';
	for (var i=0; i<inval.length; i++)
	{
		if(escapeChars.indexOf(inval.charAt(i))!=-1)
			retval = retval + '\\' + inval.charAt(i);
		else
			retval = retval + inval.charAt(i);
	}
	return retval;
}

// Defines the Attribute javascript object.
function LookupAttribute(type, value, text)
{
	this.type = type;
	this.value = value;
	this.text = text;
}

// Defines the Item Type javascript object.
function ItemType(type, category_attribute_type)
{
	this.type = type;
	this.category_attribute_type = category_attribute_type;
}

//
// This function will populate the selectObject with the
// records from the array that match the type.
// doUniqueOnly parameter if equal to true will populate the list
// with unique elements only.  If false, the list will be empty
// except for default.
//
function populateList(type, selectObject, LookupArray, doUniqueIfTypeEmpty, emptyOptionValue, doEscapeValue)
{
	// This works because we keep setting [0] to null,
	// and the positions of the options keep adjusting.
	if(selectObject.options.length)
	{
		var length = selectObject.options.length;
		for(var i=0; i<length; i++)
			selectObject.options[0] = null;
	}

	var j=0;
	
	// Now repopulate.
	if(emptyOptionValue!=null)
	{
		selectObject.options[0] = new Option(emptyOptionValue, "");
		j++;
	}
	
	if(type.length>0 || doUniqueIfTypeEmpty)
	{
		for (var i=0; i<LookupArray.length; i++)
		{
			if( (type.length>0 && LookupArray[i].type == type) ||
					(type.length==0 && doUniqueIfTypeEmpty && indexOfLookupValue(selectObject.options, LookupArray[i].value)==-1))
			{
				// Escape value if requested.
				if(doEscapeValue){
					selectObject.options[j] = new Option(LookupArray[i].text, escapeChars(LookupArray[i].value, '_'));
				}else{
					selectObject.options[j] = new Option(LookupArray[i].text, LookupArray[i].value);
				}
				j++;
			}
		}
	}

	// Select ALL option.
	selectObject.options[0].selected = true;
	
	// execute onchange event if selectObject
	if(selectObject.onchange)
	{
		selectObject.onchange();
	}
}

function get_category_type(s_item_type, ItemTypes)
{
	for (var i=0; i<ItemTypes.length; i++)
	{
		if(ItemTypes[i].type == s_item_type)
			return ItemTypes[i].category_attribute_type;
	}
	return "";
}

//
// Will return index of value if found, otherwise -1
//
function indexOfLookupValue(SelectOptions, value)
{
	for (var i=0; i<SelectOptions.length; i++)
	{
		if(SelectOptions[i].value == value)
			return i;
	}
	return -1;
}

$(document).ready(function() {
    $('#parent_item_id_loading').hide();

    $('#parent_item_filter').change(function() {
        $('#parent_item_id').prop('disabled', true);
        $('#parent_item_id_loading').show();

        var serializedData = $("form").serialize();

        $.ajax({
            type: 'post',
            url: 'ajax.php',
            dataType: 'json',
            data: serializedData + '&ajax_op=possible-parents'
        })
        .done(function(data) {
            $('#parent_item_id').html(data.select);
            console.log(data);
        })
        .always(function() {
            $('#parent_item_id').prop('disabled', false);
            $('#parent_item_id_loading').hide();
        });
    });
});
