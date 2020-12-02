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
	
	Validation Javascript for functions/widgets.php
*/

// legalChars will actually be expanded to all characters
// that are legal before this function is called.
function legalCharFilter(inval, legalChars)
{
	var retval='';
	for (var i=0; i<inval.length; i++)
	{
		if(legalChars.indexOf(inval.charAt(i))!=-1)
			retval = retval + inval.charAt(i);
	}
	return retval;
}
	
// Will filter text must match: [0-9]
function numericFilter(inval)
{
	return legalCharFilter(inval, '-0123456789');
}
	
// Very simple email validation
function checkEmail(email)
{
	var indexOfAt = email.indexOf('@');
	var indexOfDot = email.indexOf('.');

	if(indexOfAt!=-1 && indexOfDot!=-1 && (indexOfAt+1) != indexOfDot && indexOfDot != email.length-1)
		return true;
	else
		return false;
}
	
// This form will call all onchange events, and if any return false
// will also return false.
// This is a hack at best and should be enhanced!
function checkForm(form)
{
	for (var i=0; i < form.length; i++)
	{
		var type = null;
		if(form.elements[i].type)
		{
			type = form.elements[i].type.toLowerCase();
		}
		
		// We only want to check these.
		if (type == 'text' || type == 'textarea' || type == 'password' || type == 'file')
		{
			// relies on all input fields having an onchange event handler.
			if(form.elements[i].onchange && !form.elements[i].onchange())
			{
				return false;
			}
		}
	}
	// We have got to here, so return true.
	return true;
}
	
// Tests if 'str' ends with 'endstr'
function endsWith(str, endstr)
{
	// Various simple first off checks.
	if (str==null || endstr==null || str.length==0 || endstr.length==0 || str.length<endstr.length)
		return false;
   
	// Case insensitive comparison.
	str = str.toLowerCase();
	endstr = endstr.toLowerCase();
		
	for (var i=str.length-endstr.length, j=0; i<str.length; i++,j++)
	{
		if (str.charAt(i)!=endstr.charAt(j))
			return false;
	}
	return true;
}

// Tests to see if filename endswith "." + one of the 
// extArray entries, if so we return true...
function isValidExtension(filename, extArray)
{
	// If only one argument, nothing to validate!
	if (filename==null || filename.length==0 || isValidExtension.arguments.length==1)
		return true;

	for (var i=0; i<extArray.length; i++)
	{
		if(endsWith(filename, '.'+extArray[i]))
		{
			// filename cannot be the same length as '.'+extArray[i]
			if (filename.length > ('.'+extArray[i]).length)
				return true;
		}
	}
	return false;
}
	
/**
	I am not sure whether javascript has a stable empty test function,
   so we will provide one here.
*/
function isempty(value)
{
	if(value == null || value.length == 0)
		return true;
	else
		return false;
}

function isWhiteSpace(c)
{
	return (c == ' ' || c == '\t' || c == '\n' || c == '\r');
}

function trim(s)
{
	var b = 0, e = s.length;
	while(b < e && isWhiteSpace(s.charAt(b)))
		b++;

    while(e > 0 && isWhiteSpace(s.charAt(e-1)))
		e--;

	if(b>0 || e < s.length)
		return s.substring(b, e);
	else
		return s;
}

function isUrlAbsolute(url)
{
    var myRegxp = new RegExp("([a-zA-Z]+)://");
	return myRegxp.test(url);
}
