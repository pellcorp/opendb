/*
 	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell
*/

// JavaScript Document
function hasOptions(obj)
{
	if (obj!=null && obj.options!=null)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function selectAllOptions(obj)
{
	if (!hasOptions(obj))
	{
		return;
	}
	
	for (var i=1; i<obj.options.length; i++)
	{
		obj.options[i].selected = true;
	}
}

function moveAllOptions(form, hidden_nm, from, to)
{
	selectAllOptions(from);
	moveOptions(form, hidden_nm, from, to);
}

function moveOptions(form, hidden_nm, from, to)
{
	if (!hasOptions(from))
	{
		return;
	}
	
	for (var i=0; i<from.options.length; i++)
	{
		var o = from.options[i];
		if (o.selected)
		{
			if(!hasOptions(to))
			{
				var index = 0;
			}
			else
			{
				var index=to.options.length;
			}
			
			to.options[index] = new Option( o.text, o.value, false, false);
			
			var el = hidden_nm+'['+to.options[index].value+']';
			if(form[el].value == 'include')
			{
				form[el].value = 'exclude';
			}
			else
			{
				form[el].value = 'include';
			}
		}
	}
	
	// Delete them from original
	for (var i=(from.options.length-1); i>=0; i--)
	{
		var o = from.options[i];
		if (o.selected)
		{
			from.options[i] = null;
		}
	}
}
