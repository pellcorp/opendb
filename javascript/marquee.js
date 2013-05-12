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

function showMarqueeElement(elementsList, currentDisplay, timeout)
{
	if(elementsList.length > 0) {
		elementsList[currentDisplay].style.display = 'none';
		
		currentDisplay++;
		if(currentDisplay >= elementsList.length)
		{
			currentDisplay=0;
		}
		elementsList[currentDisplay].style.display = '';
		
		setTimeout(function(){ showMarqueeElement(elementsList, currentDisplay, timeout);}, timeout);
	}
}

function startMarquee(container, itemclass, timeout)
{
	var container = document.getElementById(container);
	var elementsList = getElementsByClassName(container, 'div', itemclass);

	showMarqueeElement(elementsList, 0, timeout);
}
