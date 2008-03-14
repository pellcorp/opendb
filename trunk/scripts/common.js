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
function addEvent(obj, evType, fn, useCapture)
{
	if (obj.addEventListener)
	{
		obj.addEventListener(evType, fn, useCapture);
		return true;
	}
	else if (obj.attachEvent)
	{
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	}
	else
	{
		// support sort-of for IE 5.5 on Mac here.
		obj["on"+evType] = fn;
	}
}

function getElementsByClassName(parentElement, tagName, clsName)
{ 
	var arr = new Array(); 
	var elems = parentElement.getElementsByTagName(tagName);
	for(var i=0; i<elems.length; i++)
	{
		if ( elems[i].className == clsName )
		{
			arr[arr.length] = elems[i];
		}
	}
	return arr;
}

// this function only returns direct children
function getChildElementsByTagName(parentElement, tagName)
{
	var arr = new Array(); 
	for( var n = parentElement.firstChild; n ; n = n.nextSibling )
	{
		if ( n.tagName && n.tagName.toLowerCase() == tagName )
		{
			arr[arr.length] = n;
		}
	}
	return arr;
}

function getChildElementByTagName(parentElement, tagName)
{
	for( var n = parentElement.firstChild; n ; n = n.nextSibling )
	{
		if ( n.tagName && n.tagName.toLowerCase() == tagName )
		{
			return n;
		}
	}
	return NULL;
}

// TODO - do not remove existing classes!!!!
function toggleVisible(linkElement, element)
{
	if(element.className.indexOf('elementHidden')!=-1)
	{
		linkElement.className = linkElement.className.replace('toggleHidden', 'toggleVisible');
		element.className = element.className.replace('elementHidden', '');
	}
	else
	{
	   linkElement.className = linkElement.className.replace('toggleVisible', 'toggleHidden');
        element.className += ' elementHidden';
	}
	return true;
}

function popup(url, width, height)
{
	if(width != null)
	{
		window.open(url, target, 'resizable=yes,toolbar=no,scrollbars=yes,location=no,menubar=no,status=no,width='+width+',height='+height);
	}
	else
	{
		window.open(url, target);
	}
}

function fileviewer(url, width, height, target)
{
	window.open(url, target, 'resizable=yes,toolbar=no,scrollbars=yes,location=no,menubar=no,status=no,width='+width+',height='+height);
}