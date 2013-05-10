/* 	
 	Open Media Collectors Database
	Copyright (C) 2001-2012 by Jason Pell
*/

function WidgetToolTip(name, type, description, spec, args)
{
	this.name = name;
	this.type = type;
	this.description = description;
	this.spec = spec;
	this.args = args;
}

function _get_widget_tooltip(name, type, arrOfOptions)
{
	var indexOfType = -1;

	if(arrOfOptions!=null)
	{
		for(var i=0; i<arrOfOptions.length; i++)
		{
			if(arrOfOptions[i].name == name && arrOfOptions[i].type == type)
			{
			    indexOfType = i;
				break;
			}
		}
	}

	if(indexOfType!=-1)
	{
		var block = "<div class=\"tooltip\">";

		if(!isempty(arrOfOptions[indexOfType].spec))
		{
			block += "<h2>"+arrOfOptions[indexOfType].spec+"</h2>";
		}
		
		if(!isempty(arrOfOptions[indexOfType].description))
		{
			block += "<p>"+arrOfOptions[indexOfType].description+"</p>";
		}

		block += "<h3>Arguments</h3>";
		if(arrOfOptions[indexOfType].args.length > 0)
		{
			block += "<ul>";
			for(var i=0; i<arrOfOptions[indexOfType].args.length; i++)
			{
				block += "<li>"+arrOfOptions[indexOfType].args[i]+"</li>";
			}
			block += "</ul>";
		}
		else
		{
			block += "<p>None</p>";
		}

		block += "</div>";

		return _format_tooltip_text(block);
	}
	else
	{
		//else
		return _format_tooltip_text('');
	}
}

function show_widget_select_tooltip(select, type, arrOptions)
{
	return overlib(FUNCTION, _get_widget_tooltip(select.options[select.options.selectedIndex].value, type, arrOptions), CAPTION, 'Help for \''+select.options[select.options.selectedIndex].value+'\' '+type+' type');
}