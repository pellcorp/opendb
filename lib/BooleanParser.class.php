<?php
/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2013 by Jason Pell

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
class BooleanLexer {
	var $round_brace;
	var $dbl_quote;
	var $error;
	var $stackPtr;
	var $tokenStack;
	var $lookahead;
	var $tokText;
	var $tokPtr;
	var $string;
	var $stringLen;
	
	function parse($string, $lookahead = NULL) {
		$this->string = $string;
		$this->stringLen = strlen ( $string );
		
		// Initialise		
		$this->round_brace = 0;
		
		if (is_numeric ( $lookahead ))
			$this->lookahead = $lookahead;
		else
			$this->lookahead = 0; // no lookahead

		// Initialise lookahead stack
		$this->tokPtr = 0;
		$this->stackPtr = 0;
		$this->tokenStack = array();
	}
	
	/*
	* To get very last character, use get(-1), to get previous character from
	* current one, use -2.  To get next character,without iterating the pointer (to sneek a look),
	* use get(0)
	*/
	function get($idx = NULL) {
		if (is_numeric ( $idx )) {
			// If idx is negative, this should work as well.
			$index = $this->tokPtr + $idx;
			if ($index >= 0 && $index < $this->stringLen)
				return $this->string[$index];
			else
				return NULL;
		} else {
			if ($this->tokPtr < $this->stringLen)
				return $this->string[$this->tokPtr ++];
			else
				return NULL; // reached end of string
		}
	}

	function unget() {
		-- $this->tokPtr;
	}

	function getError() {
		return $this->error;
	}
	
	/*
	* Return current token, as returned from nextToken
	*/
	function getToken() {
		return $this->tokText;
	}

	/**
	*/
	function skipWhiteSpace() {
		$c = $this->get ();
		if ($c != NULL) {		//end of string
			while ( $c == ' ' || $c == "\t" || $c == "\n" || $c == "\r" ) {
				$c = $this->get ();
			}
			
			// unget last whitespace character.
			$this->unget ();
		}
	}
	
	/*
	* Convert and / or / not (or any case deviations ) to AND / OR / NOT
	*/
	function normaliseToken($token) {
		if (strcasecmp ( $token, 'and' ) === 0)
			return 'AND';
		else if (strcasecmp ( $token, 'or' ) === 0)
			return 'OR';
		else if (strcasecmp ( $token, 'not' ) === 0)
			return 'NOT';
		else
			return $token;
	}

	function nextToken() {
		if ($this->lookahead > 0) {
			// The stackPtr, should always be the same as the count of
			// elements in the tokenStack.  The stackPtr, can be thought
			// of as pointing to the next token to be added.  If however
			// a pushBack() call is made, the stackPtr, will be less than the
			// count, to indicate that we should take that token from the
			// stack, instead of calling nextToken for a new token.
			if ($this->stackPtr < count ( $this->tokenStack )) {
				$this->tokText = $this->tokenStack [$this->stackPtr];
				
				// We have read the token, so now iterate again.
				$this->stackPtr ++;
				return $this->tokText;
			} else {
				// If $tokenStack is full (equal to lookahead), pop the oldest
				// element off, to make room for the new one.
				if ($this->stackPtr == $this->lookahead) {
					// For some reason array_shift and
					// array_pop screw up the indexing, so we do it manually.
					for($i = 0; $i < (count ( $this->tokenStack ) - 1); $i ++) {
						$this->tokenStack [$i] = $this->tokenStack [$i + 1];
					}
					
					// Indicate that we should put the element in
					// at the stackPtr position.
					$this->stackPtr --;
				}
				
				$this->tokText = $this->normaliseToken ( $this->_nextToken () );
				$this->tokenStack [$this->stackPtr] = $this->tokText;
				$this->stackPtr ++;
				return $this->tokText;
			}
		} else {
			$this->tokText = $this->normaliseToken ( $this->_nextToken () );
			return $this->tokText;
		}
	}

	function pushBack() {
		if ($this->lookahead > 0 && count ( $this->tokenStack ) > 0 && $this->stackPtr > 0) {
			$this->stackPtr --;
		}
	}
	
	/*
	* Hidden function
	*/
	function _nextToken() {
		$this->tokText = NULL;
		$dbl_quote = FALSE;
		$expr = "";
		
		$this->skipWhiteSpace ();
		while ( true ) {
			$c = $this->get ();
			switch ($c) {
				case '"' :
					if ($this->get ( - 2 ) == "\\")
						$expr .= $c;
					else {
						if ($dbl_quote)
							return $expr;
						else
							$dbl_quote = TRUE;
					}
					break;
				
				case "\\" :
					//Only support escaping double quotes, otherwise
					//pass through the escaping characters
					if ($this->get ( 0 ) != "\"") { 
						$expr .= $c;
					}
					// else ignore
					break;
				
				case '(' :
					if (! $dbl_quote) {
						if (strlen ( $expr ) > 0) {
							// Unget quote
							$this->unget ();
							return $expr;
						} else {
							$this->round_brace ++;
							return $c;
						}
					} else
						$expr .= $c;
					break;
				
				case ')' :
					if (! $dbl_quote) {
						if ($this->round_brace > 0) {
							if (strlen ( $expr ) > 0) {
								// Unget bracket
								$this->unget ();
								return $expr;
							} else {
								$this->round_brace --;
								return $c;
							}
						} else {
							$this->error = "Mismatched braces";
							return FALSE;
						}
					} else
						$expr .= $c;
					break;
				
				case ' ' :
				case "\t" :
				case "\n" :
				case "\r" :
					if ($dbl_quote)
						$expr .= $c;
					else
						return $expr; // Indicates end of token
					

					break;
				
				case NULL : // end of string
					if (strlen ( $expr ) > 0)
						return $expr;
					else
						return NULL;
				
				default :
					$expr .= $c;
			} //switch
		} //while
	}
}

class BooleanParser {
	var $lexer = NULL;

	function parseBooleanStatement($statement) {
		if ($this->lexer == NULL)
			$this->lexer = new BooleanLexer ();

		$this->lexer->parse ( $statement, 1 );

		while ( true ) {
			$statement = $this->parseStatement();
			if ($statement === FALSE)
				return FALSE;
			else if ($statement !== NULL)
				$statements [] = $statement;
			else
				break; // finished
		}

		return $statements ?? NULL;
	}

	function getError() {
		return $this->lexer->getError ();
	}

	function parseStatement() {
		$conditions [] = $this->parseCompoundStatement ();
		$token = $this->lexer->nextToken ();
		while ( $token == 'OR' ) {
			$conditions [] = $this->parseCompoundStatement ();
			$token = $this->lexer->nextToken ();
		}
		$this->lexer->pushBack ();

		if (is_array ( $conditions ) && count ( $conditions ) > 1) {
			return array ('or' => $conditions );
		} else {
			return $conditions [0];
		}
	}

	/*
	* Will parse several basic 'left <op> right' condition statements, as
	* long as they are separated by AND tokens.
	* 
	* Will also support conditions, enclosed in brackets, and treat them
	* as normal compound conditions.
	* 
	* So the following will be supported
	* 
	* 	<left> <op> <right> AND (<left> <op> <right> OR <left> <op> <right>)
	*/
	function parseCompoundStatement() {
		$token = $this->lexer->nextToken ();
		if ($token == 'NOT') {
			return array (
					'not' => $this->parseStatement () );
		} else if ($token == '(') {
			$condition = $this->parseStatement ();
			$token = $this->lexer->nextToken ();
			if ($token != ')') {
				return FALSE; // should never happen!
			}
		} else if ($this->isTextToken ( $token )) {
			$condition = $token;
		}
		
		if ($condition ?? TRUE !== FALSE) {
			$conditions [] = $condition ?? NULL;
			while ( true ) {
				$token = $this->lexer->nextToken();
				if ($token == 'AND') {
					$condition = $this->parseCompoundStatement();
					if ($condition !== FALSE)
						$conditions [] = $condition;
					else
						return FALSE;
				} else { //if($token == 'and')
					$this->lexer->pushBack ();
					break;
				}
			}
			
			if (is_array ( $conditions ?? '' ) && count ( $conditions ) > 1)
				return array (
						'and' => $conditions );
			else
				return $conditions [0];
		} else {		//if($condition !== FALSE)
			return FALSE;
		}
	}

	function isTextToken($token) {
		if ($token == NULL || $token == '(' || $token == ')' || $token == 'AND' || $token == 'OR' || $token == 'NOT')
			return FALSE;
		else
			return TRUE;
	}
}

// ------------------------------------------------
// Utility Functions
// ------------------------------------------------


/*
* @param $column_name
* @param $column_value
* @param $match_mode ["word" | "exact" | "partial"]
*/
function get_compare_clause($column_name, $column_value, $match_mode, $case_sensitive) {
	$column_value_wildcard = FALSE;
	for($i = 0; $i < strlen ( $column_value ); $i ++) {
		if (($column_value [$i] == '%' || $column_value [$i] == '_') && ($i == 0 || $column_value [$i - 1] != '\\')) {
			$column_value_wildcard = TRUE;
		}
	}
	
	if ($column_value_wildcard) {
		return "UPPER($column_name) LIKE '" . trim ( $column_value ) . "'";
	} else {
		if (strcasecmp ( $match_mode, "word" ) === 0) {
			$column_value = trim ( $column_value );
			if (is_null ( $case_sensitive )) {
				return "($column_name RLIKE '[[:<:]]" . $column_value . "[[:>:]]')";
			} else {
				return "($column_name RLIKE BINARY '[[:<:]]" . $column_value . "[[:>:]]')";
			}
		} else if (strcasecmp ( $match_mode, "partial" ) === 0) {
			$column_value = trim ( $column_value );
			if (is_null ( $case_sensitive )) {
				return "$column_name LIKE '%" . $column_value . "%'";
			} else {
				return "$column_name LIKE BINARY '%" . $column_value . "%'";
			}
		} else if (strcasecmp ( $match_mode, "exact" ) === 0) {
			if (is_null ( $case_sensitive )) {
				return "$column_name = '" . str_replace ( '\_', '_', trim ( $column_value ) ) . "'";
			} else {
				return "BINARY $column_name = '" . str_replace ( '\_', '_', trim ( $column_value ) ) . "'";
			}
		} else {		// plain
			if (is_null ( $case_sensitive )) {
				return "$column_name = '" . str_replace ( '\_', '_', $column_value ) . "'";
			} else {
				return "BINARY $column_name = '" . str_replace ( '\_', '_', $column_value ) . "'";
			}
		}
	}
}

/*
* Builds a where sub-clause based on the statements array returned 
* from BooleanParser::parseBooleanStatement(...)
*/
function build_boolean_clause($statement_rs, $column_name, $match_mode, $mode = 'AND', $case_sensitive = NULL) {
	$query = "";

	foreach ( $statement_rs as $key => $statement ) {
		if (strlen ( $query ) > 0)
			$query .= " $mode ";
		
		if (is_array ( $statement ) && is_array ( $statement ['not'] )) {
			$query .= "NOT (" . build_boolean_clause ( $statement ['not'], $column_name, $match_mode, $mode, $case_sensitive ) . ")";
		} else if (is_array ( $statement ) && is_array ( $statement ['and'] )) {
			$query .= "(" . build_boolean_clause ( $statement ['and'], $column_name, $match_mode, 'AND', $case_sensitive ) . ")";
		} else if (is_array ( $statement ) && is_array ( $statement ['or'] )) {
			$query .= "(" . build_boolean_clause ( $statement ['or'], $column_name, $match_mode, 'OR', $case_sensitive ) . ")";
		} else if (is_array ( $statement )) {
			$query .= "(";
			
			if (isset ( $statement ['not'] ))
				$query .= "NOT ";
			
			$query .= "(" . build_boolean_clause ( $statement, $column_name, $match_mode, $key == 'or' ? 'OR' : $mode, $case_sensitive ) . ")";
			
			$query .= ")";
		} else {
			$query .= get_compare_clause ( $column_name, $statement, $match_mode, $case_sensitive );
		}
	}
	
	return $query;
}
?>
