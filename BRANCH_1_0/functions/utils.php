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
	If strlen($value)==0 will return $ifnull value
	instead.
*/
function ifempty($value, $ifnull)
{
	if(strlen($value)==0)
		return $ifnull;
	else
		return $value;
}

/**
	Returns TRUE if $array is array, AND has at least
	one element.  I hate this is_array(...) and count(...)
	combination which is pissing me off.
*/
function is_not_empty_array($array)
{
	if(is_array($array) && count($array)>0)
		return TRUE;
	else
		return FALSE;
}

function is_empty_array($array)
{
	return is_empty_or_not_array($array);
}

/**
	Returns TRUE if $array is not array, OR has no
	elements.

	I know you can probably use 'empty($array)', but
	considering how some of the functionality of these
	functions can change, I don't want to risk it.
*/
function is_empty_or_not_array($array)
{
	if(!is_array($array) || count($array)==0)
		return TRUE;
	else
		return FALSE;
}

/**
	Stupid strrpos only allows searching on first
	character, so this is proper version.
*/
function laststrpos($haystack, $needle)
{
	// Initialise.
	$index = FALSE;
	$idx = strpos($haystack, $needle);
	while($idx!==FALSE)
	{
		$index = $idx;
		$idx = strpos($haystack, $needle, $index+strlen($needle));// Move past found needle.
	}
	return $index;
}

/**
	Extended version of str_replace.  Even though the doco says that
	you can specify arrays for both find and replace, it does not seem
	to work properly on the version of PHP I am using.  (4.0.4p1), so
	I have written my own.
*/
function str_replaces($find_r, $replace_r, $buf)
{
	if(strlen($buf)>0)
	{
		for ($j=0; $j<count($find_r); $j++)
		{
			if(strlen($find_r[$j])>0)
			{
				if($replace_r[$j])
					$buf = str_replace($find_r[$j], $replace_r[$j], $buf);
				else
					$buf = str_replace($find_r[$j], "", $buf);
			}
		}
	}
	return $buf;
}

/**
* Perform explode, but then trim each array
* element before returning.
*/
function trim_explode($delimiter, $value)
{
	if(strlen(trim($value))>0)
	{
		$tmp_values_r = explode($delimiter, $value);
		if(is_not_empty_array($tmp_values_r))
		{
			// we need to trim all the entries
			$values_r = NULL;
			for($i=0; $i<count($tmp_values_r); $i++)
			{
				$values_r[] = trim($tmp_values_r[$i]);
			}
		}
		else
		{
			$values_r[] = trim($value);
		}
		return $values_r;
	}
	else
	{
		return NULL;
	}
}

/*
* Check if $s1 startsWith $s2
* 
* Where $s1 is smaller than $s2, return FALSE
* Where $s1 same length as $s2, do a direct '==' comparison
* Where $s1 is larger than $s2, then substr to length of 
* $s2 and do '==' comparison.
*/
function starts_with($s1, $s2)
{
	if(strlen($s1) < strlen($s2))
		return FALSE;
	else if(strlen($s1) == strlen($s2))
		return ($s1 == $s2);
	else
		return (substr($s1,0,strlen($s2)) == $s2);
}

/**
 * Return true if $s1 ends with $s2
 *
 * @param string $s1
 * @param string $s2
 * @return boolean
 */
function ends_with($s1, $s2)
{
	if(strlen($s1) < strlen($s2))
		return FALSE;
	else if(strlen($s1) == strlen($s2))
		return ($s1 == $s2);
	else
		return (substr($s1,-(strlen($s2)),strlen($s2)) == $s2);
}

/**
	A pedestrian attempt to trim URL in a neat way.
*/
function trim_url($str, $length)
{
	if(strlen($str)>$length)
		return substr($str, 0, ($length/2)-3)."...".substr($str, strlen($str)-($length/2));
	else
		return $str;
}

/*
* This function does not search nested arrays.
* 
* @param $strcasecmp Specifies whether to do Case INSENSITIVE comparison
* or not.
*/
function array_search2($needle, $haystack, $strcasecmp=FALSE)
{
	if(is_array($haystack))
	{
		reset($haystack);
		while(list($key,$value) = each($haystack))
		{
			if(($strcasecmp!==TRUE && strcmp($value, $needle)===0) || ($strcasecmp===TRUE && strcasecmp($value, $needle)===0))
				return $key;
		}
	}

	//else
	return FALSE;
}

// Replace Windows and Mac newlines with Unix standard newline.
function replace_newlines($value)
{
	// 1) Replace all '\r\n' with single '\n'
	// 2) Replace all remaining '\r' with a '\n'
	return str_replace("\r", "\n", 
			str_replace("\r\n", "\n", $value));
}

/**
	Format sql in clause from set of values in array.
	
	Returns the clause minus the IN ( ... )
	
	Does no escaping of ' quotes, so ensure they
	are not present in the array_of_values array.
*/
function format_sql_in_clause($array_of_values)
{
	$inclause = '';
	
	while(list(,$value) = @each($array_of_values))
	{
		if(strlen($inclause)>0)
			$inclause .= ', ';
		$inclause .= "'$value'";
	}
	
	if(strlen($inclause)>0)
		return $inclause;
	else
		return NULL;
}

function validate_ind_column($column, $options_r=NULL)
{
	$column = strtoupper($column);
	
	if(is_array($options_r) && array_search2($column, $options_r)!==FALSE)
		return $column;
	else if($column == 'Y')
		return 'Y';
	else
		return 'N';
}

/**
	If a title is specified with an article "The", "An", "A", etc
	then move it to end of title, with a ',' separator.
	 
	Note: Match is NOT case sensitive.
*/
function format_title_grammar_article($title, $articles)
{
	while (list(,$article) = each($articles))
	{
		$article = trim($article);
		
		// If $title starts with $entry - NOT CASE SENSITIVE!!!
		if(strcasecmp(substr($title, 0, strlen($article.' ')),$article.' ')===0)
		{
			// INITCAP the $entry.
			$title = substr($title, strlen($article.' '), strlen($title)).', '.
						substr($title, 0, strlen($article.' '));

			// Hit first article, so get out of here.
			break;
		}
	}
	
	return $title;
}

/**
 * If $s begins and ends with a single or double quote, the quotes will be
 * removed.
 */
function remove_enclosing_quotes($s)
{
	if( (substr($s,0,1) == '"' && substr($s,-1,1) == '"') || 
			(substr($s,0,1) == "'" && substr($s,-1,1) == "'"))
	{
		return substr($s, 1, -1);
	}
	else
	{
		return $s;
	}
}

/**
 * Replace html_entity_decode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.html_entity_decode
 * @author      David Irvine <dave@codexweb.co.za>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.10 $
 * @since       PHP 4.3.0
 * @internal    Setting the charset will not do anything
 * @require     PHP 4.0.0 (user_error)
 */
function unhtmlentities($string)
{
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
    
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}
?>