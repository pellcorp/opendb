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
	
	Datetime widget functions
*/

function array_search(value, array)
{
	for(var i=0; i<array.length; i++)
	{
		if(array[i] == value)
		{
			return i;
		}
	}
	
	//else
	return -1;
}

function is_array(array)
{
	return (array!=null && array.length>0);
}

function print_r(array)
{
	var buffer = '';
	if(is_array(array))
	{
		for(var i=0; i<array.length; i++)
		{
			buffer = buffer + '['+i+'] => '+array[i]+'\n';
		}
	}
	return buffer;
}

var datetime_masks = new Array("DD", "MM", "YYYY", "HH24", "HH", "MI", "SS");
function tokenize_datetime_or_mask_string(value, format_tokens)
{
	var token = '';	
	var tokens = new Array();
	var idx = 0;
	for(var i=0; i<value.length; i++)
	{
		switch(value.charAt(i))
		{
			case ' ':
			case "\t":
			case ',':
			case '/':
			case '\\':
			case '-':
			case ':':
			case '.':
				if(token.length>0)
				{
					tokens[idx++] = token;
					token = '';
				}
				tokens[idx++] = value.charAt(i);
				break;
			
			default:
				// based on the format token, we may need to cut the token off,
				// at a certain point.
				if(is_array(format_tokens))
				{
					if(isNaN(value.charAt(i))) // all date tokens are numeric
					{
						if(token.length>0)
						{
							tokens[idx++] = token;
							token = '';
						}
						tokens[idx++] = value.charAt(i);
					}
					else
					{
						format_token = format_tokens[tokens.length];
						
						if(array_search(format_token, datetime_masks) != -1)
						{
							if(format_token == 'YYYY')
							{
								if(token.length>=4)
								{
									tokens[idx++] = token;
									token = '';
								}
							}
							else if(token.length>=2) // all other mask variables are two chars long
							{
								tokens[idx++] = token;
								token = '';
							}
							
							token += value.charAt(i);
						}
					}
				}
				else
				{
					token += value.charAt(i);
					
					if(array_search(token, datetime_masks) != -1)
					{
						if(token == 'HH')
						{
							// if there is actually a HH24 token, we should ignore this HH one.
							if(value.length > (i + 2) && value.substr(i+1, 2) == '24')
							{
								break; // break to start of switch again.
							}
						}
						
						tokens[idx++] = token;
						token = '';
					}
				}
			//default:
		}//switch
	}
	
	if(token.length > 0)
	{
		tokens[idx++] = token;
	}
	
	return tokens;
}

/**
 * 	Mask components supported are:
 *		DD - Days (01 - 31)
 *		MM - Months (01 -12)
 *		YYYY - Years
 *		HH24 - Hours (00 - 23)
 *		HH - Hours (01 - 12)
 *		MI - Minutes (00 - 59)
 *		SS - Seconds (00 - 59)
*/
function is_datetime(datetime, format_mask)
{
	var format_tokens = tokenize_datetime_or_mask_string(format_mask);
	var datetime_tokens = tokenize_datetime_or_mask_string(datetime, format_tokens);
	
	// As long as the last token is either a punctuation mark, that is
	// the same in both arrays, or is a legal mask token, and has
	// match in the other array.	
	var format_and_datetime_match = false;
	if(format_tokens.length == datetime_tokens.length)
		format_and_datetime_match = true;
	else if(format_tokens.length > datetime_tokens.length)
	{
		var format_token = format_tokens[datetime_tokens.length-1];
		var datetime_token = datetime_tokens[datetime_tokens.length-1];
		
		if(format_token == datetime_token)
			format_and_datetime_match = true;
		else if(array_search(format_token, datetime_masks) != -1) // else mask token
			format_and_datetime_match = true;
	}
	
	if(format_and_datetime_match)
	{
		var datetime_components_found = false;
		var datetime_components = new Array();
		for(var i=0; i<datetime_masks.length; i++)
		{
			var idx = array_search(datetime_masks[i], format_tokens);
			if( idx != -1)
			{
				datetime_components[datetime_masks[i]] = datetime_tokens[idx];
				datetime_components_found = true;
			}
		}
		
		if(datetime_components_found)
		{
			var year = datetime_components['YYYY'];
			
			if(year!=null && year.length>0 && (isNaN(year) || year.length != 4))
				return false;
			else if(array_search('YYYY', format_tokens)!=-1 && (year==null || year.length==0))
				return false;
			
			var month = datetime_components['MM'];
			if(month!=null && month.length>0)
			{
				if(isNaN(month))
				{
					return false;
				}
			}
			else if(array_search('MM', format_tokens)!=-1 && (month==null || month.length==0))
			{
				return false;
			}
			else
			{
				month = 1;
			}
				
			var day = datetime_components['DD'];
			if(day!=null && day.length>0)
			{
				if(isNaN(day))
				{
					return false;
				}
				else
				{
					if(month==2)
					{
						// Check for leap year
						if ( year!=null && year.length>0 && (( year%4==0 && year%100 != 0 ) || year%400==0 ) ) // leap year
						{
							if (day > 29)
							{
								return false;
							}
						}
						else
						{
							if (day > 28)
							{
								return false;
							}
						}
					}
					else if (month==4 || month==6 || month==9 || month==11)
					{
						if (day > 30)
						{
							return false;
						}
					}
				}
			}
			else if(array_search('DD', format_tokens)!=-1 && (day==null || day.length==0))
			{
				return false;
			}
			else
			{
				day = 1;
			}
			
			var hour24 = datetime_components['HH24'];
			var hour = datetime_components['HH'];
			if(hour24!=null && hour24.length>0)
			{
				if(isNaN(hour24) || parseInt(hour24) < 0 || parseInt(hour24) > 23)
					return false;
			}
			else if(hour!=null && hour.length>0)
			{
				if(isNaN(hour) || parseInt(hour) < 1 || parseInt(hour) > 12)
					return false;
			}
			
			minute = datetime_components['MI'];
			if(minute!=null && minute.length>0)
			{
				if(isNaN(minute) || parseInt(minute) < 0 || parseInt(minute) > 59)
					return false;
			}
			
			var second = datetime_components['SS'];
			if(second!=null && second.length>0)
			{
				if(isNaN(second) || parseInt(second) < 0 || parseInt(second) > 59)
					return false;
			}
			
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		//mismatch
		return false;
	}
}
