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

/**
	Assumes $c is single character
*/
function is_alpha($c)
{
	if( ($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z'))
		return TRUE;
	else
		return FALSE;
}

/*
* The escape sequences \r \n \t, must be enclosed in double quotes, otherwise PHP does
* not recognise them.
*/
function is_nonword_char($c)
{
	if ($c == '(' || $c == ')' || $c == '[' || $c == ']' || $c == '{' || $c == '}' || $c == ':' || $c == '.' || $c == '-' || $c == ' ' || $c == "\t" || $c == "\n" || $c == "\r")
		return TRUE;
	else
		return FALSE;
}

/**
	This function could be improved, but for now it should suffice!
*/
function is_roman_numeral($text)
{
	// Roman numerals from 1 to 20!
	$numerals = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX');
	if(in_array($text, $numerals))
		return TRUE;
	else
		return FALSE;
}

/**
	Small function to process word.
*/
function ucword($word)
{
	if(is_roman_numeral($word))
		return $word;//as is!
	else
		return ucfirst(strtolower($word));
}

/**
	This function will split a sentence into separate words.

	Sentences will be split on normal word boundaries, such as
	spaces,tabs,newlines, but also when a bracket (,{,[,],},) are
	encountered.  All space will be preserved however and restored
	to the final string.

	This function will also recognise Roman numerals from I to XX (1-20) 
	and if they are already UPPERCASE, will maintain case.

	This function differs from ucwords, in that it will set all 
	other characters in a word to lowercase!
*/
function initcap($text)
{
	$word='';
	$rtext='';
	for ($i=0; $i<strlen($text); $i++)
	{
		// We might be in the middle of a word.
		if(strlen($word)>0 || is_alpha($text[$i]))
		{
			// test for end of word.
			if(strlen($word)>0 && is_nonword_char($text[$i]))
			{
				$rtext .= ucword($word);
				$word='';

				$rtext .= $text[$i];
			}
			else
				$word .= $text[$i];
		}
		else//just copy it to return array.
			$rtext .= $text[$i];	
	}

	// Do final word.
	if(strlen($word)>0)
	{
		$rtext .= ucword($word);
		$word='';	
	}
	return $rtext;
}

/**
	Assumes $c is single character
*/
function is_legal_code_char($c)
{
	if( ($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z') || $c == '_' || $c == '-' || $c == '.')
		return TRUE;
	else
		return FALSE;
}

/**
	Ensure legal function name
*/
function is_legal_function_name($word)
{
	if(strlen($word)>0)
	{
		for($i=0; $i<strlen($word); $i++)
		{
			if(!is_legal_code_char($word[$i]))
				return FALSE;
		}
		return TRUE;
	}
	else
		return FALSE;
}

/**
	Get the function type of a specified 
	function_spec.  No other processing
	is required.
*/
function get_function_type($function_spec)
{
	$start = strpos($function_spec, '(');
    if($start !== FALSE)
    	$type = trim(substr($function_spec, 0, $start));
	else
		$type = trim($function_spec);	

	return $type;
}

/*
* Assumes the $args_r is compatible with the 'args' array
* returned from prc_function_spec(...)
* 
	Will reconstitute the function arguments from
	the array of functions.

	If a argument has any ',' in it we assume it
	needs to double quoted.

	Any double quotes in the argument, and they
	need to be escaped.
* 
* @param $function_name - If null, then will reconstitute
* 			the arguments (excluding the brackets) only.
* @param $arguments_r - The arguments.  If empty, will return
* 		the $function_name.
*/
function get_rebuilt_function($function_name, $arguments_r)
{
	if(!is_array($arguments_r))
	{
		if($function_name !== NULL)
		{
			if(strlen($arguments_r)==0)
				return $function_name;
			else
				return $function_name.'('.$arguments_r.')';
		}
		else 
			return $arguments_r;
	}
	else
	{
		$argsText = '';

		@reset($arguments_r);
		while(list(,$argument) = @each($arguments_r))
		{
			// If argument includes a comma, or is whitespace (For a delimiter for example),
			// it must be enclosed in quotes.
			if( (strlen($argument)>0 && strlen(trim($argument))==0) || strpos($argument, ',') !== FALSE)
				$argument = "\"".str_replace("\"", "\\\"", $argument)."\"";
			else if(strpos($argument, '"')!==FALSE)
				$argument = str_replace("\"", "\\\"", $argument);
				
			if(strlen($argsText)==0)
				$argsText .= $argument;
			else
				$argsText .= ', '.$argument;
		}

		if($function_name !== NULL)
		{
			if(strlen($argsText)==0)
				return $function_name;
			else
				return $function_name.'('.$argsText.')';
		}
		else 
			return $argsText;
	}
}

/**
	Will return an array of the following form:
		type=>function_name, args=>array(args)
*/
function prc_function_spec($function_spec, $require_legal_func_name=FALSE) 
{
    $start = strpos($function_spec, '(');
    if($start !== FALSE)
    {
    	// Now we have something to parse.
        $type = trim(substr($function_spec, 0, $start));

		// Now ensure the function name is valid.
		if($require_legal_func_name==FALSE || is_legal_function_name($type))
		{
	        $end = strrpos($function_spec, ')');
			if($end>$start)//Otherwise a fuckup...
        	{
        		// Now we have the args, lets tokenise them.
	        	$args = trim(substr($function_spec, $start+1, $end-($start+1)));
				$arr = prc_args($args);
        	}
	
			return array(type=>strtolower($type), args=>$arr);
		}
		else
			return NULL;
	}
    else if($require_legal_func_name==FALSE)
	{ 
		// No (), so arg[0] is the whole thing.
    	$type = trim($function_spec);

		// Empty argument list.
		return array(type=>strtolower($type), args=>array());
	}
	else
		return NULL;
}

function prc_args($args)
{
	$argument='';
	$quote=NULL;
	
	// Allows us to keep track of nested braces.
	$curly_brace=0;
	$round_brace=0;
	$square_brace=0;
	$dbl_quote=FALSE;
	$sgl_quote=FALSE;
	
	for($i=0; $i<strlen($args); $i++)
	{
		switch($args[$i])
		{
			case '"':
				if($sgl_quote || $curly_brace>0 || $round_brace>0 || $square_brace>0 || ($i>0 && $args[$i-1]=="\\"))
					$argument .= $args[$i];
				else
					$dbl_quote = !$dbl_quote;
				break;
				
			case '\'':
				if($dbl_quote || $curly_brace>0 || $round_brace>0 || $square_brace>0 || ($i>0 && $args[$i-1]=="\\"))
					$argument .= $args[$i];
				else
					$sgl_quote = !$sgl_quote;
				break;
				
			case '\\':
				// If in braces, always include escape character, so it will be seen by the recursive calls to pcr_args.
				if($curly_brace>0 || $round_brace>0 || $square_brace>0)
				{
					$argument .= $args[$i];
				}
				else if($i>0 && $args[$i-1]=="\\")// As previous argument was an escape character, we should put this one in!
					$argument .= $args[$i];
				// else ignore
				break;
				
			case '{':
				// Do not recognise nested braces if inside quotes.
				if(!$dbl_quote && !$sgl_quote)
				{
					$curly_brace++;
				}	
				$argument .= $args[$i];
				break;
				
			case '}':
				// Do not recognise nested braces if inside quotes.
				if(!$dbl_quote && !$sgl_quote && $curly_brace>0)
				{
					$curly_brace--;
				}	
				$argument .= $args[$i];
				break;
				
			case '[':
				if(!$dbl_quote && !$sgl_quote)
				{
					$square_brace++;
				}
				$argument .= $args[$i];
				break;
				
			case ']':
				if(!$dbl_quote && !$sgl_quote && $square_brace>0)
				{
					$square_brace--;
				}
				$argument .= $args[$i];
				break;
				
			case '(':
				if(!$dbl_quote && !$sgl_quote)
				{
					$round_brace++;
				}
				$argument .= $args[$i];
				break;
				
			case ')':
				if(!$dbl_quote && !$sgl_quote && $round_brace>0)
				{
					$round_brace--;
				}
				$argument .= $args[$i];
				break;
				
			case ',':
				if($i>0 && $args[$i-1]=="\\")
				{
					// Get rid of escape character.
					$argument .= $args[$i];
				}
				else if($dbl_quote || $sgl_quote || $curly_brace>0 || $round_brace>0 || $square_brace>0)
				{
					// Inside nested block, so ignore argument separator.
					$argument .= $args[$i];
				}
				else
				{
					$arguments[] = $argument;
					$argument='';
				}
				break;
			
			case " ":
			case "\t":
			case "\n":
			case "\r":
				if($i>0 && $args[$i-1]=="\\")
				{
					// Get rid of escape character.
					$argument[strlen($argument)-1] = $args[$i];
				}
				else if(strlen($argument)>0)
				{
					// If already encountered non-whitespace for this argument, we need to keep it.
					$argument .= $args[$i];
				}
				else if($dbl_quote || $sgl_quote || $curly_brace>0 || $round_brace>0 || $square_brace>0)
				{
					// Inside nested block
					$argument .= $args[$i];
				}
				
				break;
				
			default:
				$argument .= $args[$i];
		}
	}	
	
	if(strlen($argument)>0)
		$arguments[] = $argument;
	return $arguments;
}

function remove_function_arg($function_spec, $arg_text)
{
	$function_spec_r = prc_function_spec($function_spec);
	if(is_array($function_spec_r['args']))
	{
		// we want to remove list-link argument and recompose the display_type
		$new_args = array();
		for($i=0; $i<count($function_spec_r['args']); $i++)
		{
			if($function_spec_r['args'][$i] != $arg_text)
				$new_args[] = $function_spec_r['args'][$i];
		}
										
		return get_rebuilt_function($function_spec_r['type'], $new_args);
	}
	else
	{
		return $function_spec;
	}
}

function remove_illegal_chars($value, $legalChars)
{
	$buffer = '';
	for($i=0; $i<strlen($value); $i++)
	{
		if(strstr($legalChars, substr($value,$i,1)) !== FALSE)
		{
			$buffer .= substr($value,$i,1);
		}
	}
					
	return $buffer;
}

/**
	Will expand any ?-? expressions into their actual
	range.  If you want to include '-' as an option escape
	it with \
*/	
function expand_chars_exp($exp)
{
	$retval="";
	$i=0;
	while($i<strlen($exp))
	{
		if(substr($exp, $i, 1) == '-' && $i>0 && substr($exp, $i-1, 1) != '\\') 
		{
			$start = ord(substr($exp, $i-1, 1));
			$end = ord(substr($exp, ++$i, 1));
			
			if($start < $end && is_alphanum($start) && is_alphanum($end))
			{
				for($j=($start+1); $j<=$end; $j++)
					$retval .= chr($j);
			}
			else//else - not a range
			{
				$retval .= substr($exp, $i-1, 1);
				$retval .= substr($exp, $i, 1);
			}
		}
		else if(substr($exp, $i, 1) == '\\')
		{
			// If this is escaping a character other than  '\'
			// then do not include.  The test will still look
			// at the original exp, for the '\', so getting rid
			// of it here will be alright!
			if($i>0 && substr($exp, $i-1, 1) == '\\')
				$retval .= '\\'; 
		}
		else
		{
			$retval .= substr($exp, $i, 1);
		}
		$i++;
	}
	
	return $retval;
}

/**
 * Could have used ctype_alpnum, but thats not guaranteed to exist, so this is more bullet proof.
 *
 * @param unknown_type $asciivalue
 * @return unknown
 */
function is_alphanum($asciivalue)
{
	if($asciivalue >= ord('0') && $asciivalue <= ord('9'))
		return true; 
	else if($asciivalue >= ord('a') && $asciivalue <= ord('z'))
		return true;
	else if($asciivalue >= ord('A') && $asciivalue <= ord('Z'))
		return true;
	else
		return false;
}

function expand_range($left, $right)
{
	$retval = '';
	for($i=$left; $i<=$right; $i++)
	{
		if(strlen($retval)>0)
			$retval .= ',';
		
		$retval .= $i;
	}
	
	return $retval;
}

/**
* Specify a range of characters in the following format:
* 	1-15,10,1,12,423,312312,123-124.  If you specify
* a range, that is not valid, that portion will be ignored.
*/
function expand_number_range($range)
{
	$retval='';
	$i=0;

	$number = '';
	$left_number = '';
	$right_number = '';
	while($i<strlen($range))
	{
		if(is_numeric($range{$i}))
		{
			 if(is_numeric($left_number))
				$right_number .= $range{$i};
			else
				$number .= $range{$i};
		}
		else if($range{$i} == '-') // end of left range number
		{
			$left_number = $number;
			
			//reset
			$number = '';
		}
		else if($range{$i} == ',') // end of right range number, or lone number
		{
			if(is_numeric($left_number) && is_numeric($right_number))
			{
				$retval .= expand_range($left_number, $right_number);
				
				//reset
				$left_number = '';
				$right_number = '';
			}
			else
			{
				$retval .= $number;
				
				//reset
				$number = '';
			}
			
			$retval .= ',';
		}
		
		$i++;
	}
	
	if(is_numeric($left_number) && is_numeric($right_number))
	{
		$retval .= expand_range($left_number, $right_number);
	}
	else
	{
		$retval .= $number;
	}
	
	// get rid of last character, if a comma.
	if($retval{strlen($retval)-1} == ',')
		$retval = substr($retval, 0, strlen($retval)-1);
		
	return $retval;
}
?>