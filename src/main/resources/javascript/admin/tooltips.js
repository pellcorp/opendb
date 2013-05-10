/*
 	Open Media Collectors Database
	Copyright (C) 2001-2012 by Jason Pell
*/

//
// overlib abstraction functions
//

/**
ensure legal tooltip text
*/
function _format_tooltip_text(text)
{
	if(text != undefined && text != null && text.length > 0)
	{
		text = text.replace(/\"/gi, '&quot;');
		text = text.replace(/\(/gi, '&#40;');
    	text = text.replace(/\)/gi, '&#41;');
	}
	else
	{
		text = 'No tooltip available';
	}
	return text;
}

function show_tooltip(tooltip, caption)
{
	if(caption != undefined)
		return overlib(_format_tooltip_text(tooltip), CAPTION, _format_tooltip_text(caption));
	else
		return overlib(_format_tooltip_text(tooltip));
}

function hide_tooltip()
{
	return nd();
}

OLpageDefaults(
	BGCLASS, 'tooltip', 
	FGCLASS, 'tooltip', 
	TEXTFONTCLASS, 'tooltip', 
	CGCLASS, 'tooltip-caption', 
	CAPTIONFONTCLASS, 'tooltip-caption', 
	WRAP, WRAPMAX, 400);
