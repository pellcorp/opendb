<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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
function get_javascript($filename)
{
	return '<script src="./scripts/'.$filename.'" language="JavaScript" type="text/javascript"></script>';
}

function get_common_javascript()
{
	return get_javascript('common.js');
}

function get_tabs_javascript()
{
	return get_javascript('tabs.js');
}

function get_marquee_javascript()
{
	return get_javascript('marquee.js');
}

function get_popup_javascript()
{
	return get_javascript('popup.js');
}

function get_forms_javascript()
{
	return get_javascript('forms.js');
}
					
function get_validation_javascript()
{
	return get_javascript('validation.js').
			get_javascript('date.js').
			get_javascript('popup.js');
}

function get_listings_javascript()
{
	return get_javascript('listings.js');
}
?>