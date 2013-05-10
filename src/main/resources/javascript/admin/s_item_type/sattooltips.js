/*
 	Open Media Collectors Database
	Copyright (C) 2001-2012 by Jason Pell
*/

/*
The s_attribute_type must match the <option value="..." part
*/
function SystemAttributeTypeTooltip(s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type)
{
	this.s_attribute_type = s_attribute_type;
	this.description = description;
	this.prompt = prompt;
	this.input_type = input_type;
	this.display_type = display_type;
	this.s_field_type = s_field_type;
	this.site_type = site_type;
}

function _get_sat_tooltip(s_attribute_type, arrOfOptions)
{
	var indexOfType = -1;

	if(arrOfOptions!=null)
	{
		for(var i=0; i<arrOfOptions.length; i++)
		{
			if(arrOfOptions[i].s_attribute_type == s_attribute_type)
			{
			    indexOfType = i;
				break;
			}
		}
	}

	if(indexOfType!=-1)
	{
		var block = "<div class=\"tooltip\">";

		if(!isempty(arrOfOptions[indexOfType].description))
		{
			block += "<p>"+arrOfOptions[indexOfType].description+"</p>";
		}

		block += "<dl>";
		
		if(!isempty(arrOfOptions[indexOfType].prompt))
		{
			block += "<dt>Prompt</dt><dd>"+arrOfOptions[indexOfType].prompt+"</dd>";
		}

		if(!isempty(arrOfOptions[indexOfType].input_type))
		{
			block += "<dt>Input&nbsp;type</dt><dd>"+arrOfOptions[indexOfType].input_type+"</dd>";
		}

		if(!isempty(arrOfOptions[indexOfType].display_type))
		{
			block += "<dt>Display&nbsp;type</dt><dd>"+arrOfOptions[indexOfType].display_type+"</dd>";
		}

		if(!isempty(arrOfOptions[indexOfType].s_field_type))
		{
			block += "<dt>Field&nbsp;type</dt><dd>"+arrOfOptions[indexOfType].s_field_type+"</dd>";
		}

		if(!isempty(arrOfOptions[indexOfType].site_type))
		{
			block += "<dt>Site&nbsp;type</dt><dd>"+arrOfOptions[indexOfType].site_type+"</dd>";
		}
		block += "</dl>";
		block += "</div>";

		return _format_tooltip_text(block);
	}
	else
	{
	    //else
		return _format_tooltip_text('');
	}
}

function show_sat_tooltip(s_attribute_type, arrOptions)
{
	return overlib(FUNCTION,_get_sat_tooltip(s_attribute_type, arrOptions), CAPTION, s_attribute_type);
}

function show_sat_select_tooltip(select, arrOptions)
{
	return overlib(FUNCTION,_get_sat_tooltip(select.options[select.options.selectedIndex].value, arrOptions), CAPTION, select.options[select.options.selectedIndex].value);
}
