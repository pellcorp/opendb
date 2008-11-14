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

function ifnotset(s, notsetvar) {
	if(s == undefined || s == null) {
		return notsetvar;
	} else {
		return s;
	}
}

/**
@param tabClass - The hidden version will have 'Hidden' suffix

, 'tab-menu', 'tab-content', 'activeTab', 'tabContent'
*/
function activateTab(menuId, menuContainerId, contentContainerId, activeMenuClass, tabClass)
{
	menuContainerId = ifnotset(menuContainerId, 'tab-menu');
	contentContainerId = ifnotset(contentContainerId, 'tab-content');
	activeMenuClass = ifnotset(activeMenuClass, 'activeTab');
	tabClass = ifnotset(tabClass, 'tabContent');

	var menuContainer = document.getElementById( menuContainerId );
	var contentContainer = document.getElementById( contentContainerId );

	var elems = menuContainer.getElementsByTagName('li');
	for(var i=0; i<elems.length; i++)
	{
		elems[i].className = '';
		if(i == 0)
		{
			elems[i].className = 'first';
		}
		
		if( elems[i].id == 'menu-'+menuId )
		{
			elems[i].className = elems[i].className + " " + activeMenuClass;
		}
	}
	
	elems = getChildElementsByTagName(contentContainer, 'div');
	for(var i=0; i<elems.length; i++)
	{
		elems[i].className = tabClass+"Hidden";
		if( elems[i].id == menuId )
		{
			elems[i].className = tabClass;
		}
	}
	
	return true;
}