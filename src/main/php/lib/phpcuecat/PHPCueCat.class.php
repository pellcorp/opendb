<?php
/**
 * Project:     PHPCueCat - A PHP CueCat Decoding Library
 * File:        PHPCueCat.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Lyceum mailing list. Send a blank e-mail to
 * lyceum-users-subscribe@lists.sourceforge.net
 *
 * You may contact the authors of PhatCat, by e-mail at:
 * sbw@ibiblio.org
 *
 * Or, write to:
 * Blake Watters
 * Ibiblio.org
 * CB #3456, Manning Hall
 * UNC-Chapel Hill
 * Chapel Hill, North Carolina 27599-34
 *
 * The latest version of PHPCueCat can be obtained from:
 * http://www.ibiblio.org/sbw/phpcuecat/
 *
 * @link http://www.ibiblio.org/sbw/phpcuecat/
 * @copyright 2003 Blake Watters
 * @author Blake Watters <sbw@ibiblio.org>
 * @package PHPCueCat
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */

/**
 * Provides a high level interface for accessing data scanned by the popular
 * CueCat line of PS/2 barcode scanners.
 *
 * Portions of this code was derived from Dustin Sallings' kittycode.js
 * @link http://bleu.west.spy.net/~dustin/kittycode/kittycode.js
 *
 * Portions of this code was derived from Dustin Grau's phatcat.php
 * 
 * @package PHPCueCat
 */
class PHPCueCat {

	/**
	 * The unique id of the CueCat used to scan the input
	 *
	 * @var int
	 */
	var $cat_id;

	/**
	 * The type of barcode that was scanned
	 *
	 * @var string
	 */ 
	var $code_type;

	/**
	 * The raw decoded barcode value
	 *
	 * @var string
	 */
	var $bar_code;

	/**
	 * Whether or not the instance has parsed a scan yet
	 *
	 * @access private
	 * @var bool
	 */
	var $_parsed = false;

	/**
	 * Whether or not the instance has parsed a valid barcode
	 *
	 * @access private
	 * @var bool
	 */
	var $_valid = false;

	/**
	 * Attempt to parse a string read from a CueCat scanner out
	 * into the component parts
	 *
	 * @var string $cc_str the string read from the CueCat
	 * @return bool the success of the parsing operation
	 */
	function parse($cc_str) {
		$parts = explode('.', $cc_str);

		if (count($parts) != 5) {
			return (false);
		}

		/**
		 * Decode the component pieces of the scanner
		 */
		$this->cat_id = $this->decode_part($parts[1]);
		$this->code_type = $this->decode_part($parts[2]);
		$this->bar_code = $this->decode_part($parts[3]);

		/**
		 * Set the flags
		 */
		$this->_parsed = true;
		$this->_valid = $this->check_barcode($this->bar_code);

		return (true);
	}

	/**
	 * Decode a part of a CueCat code and return the result
	 *
	 * @param string $part the string portion we are examining
	 * @return string the result of the decoding operation
	 */
	function decode_part($part) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+-";
		$result = "";
		$packer = 0;
		$count = 0;

		for ($i = 0; $i < strlen($part); $i++) {
			/**
			 * Get the offset to the current character in our map
			 */
			$x = strpos($chars, substr($part, $i, 1));

			/**
			 * For invalid characters, point them out really well!
			 */
			if ($x < 0) {
				$result .= " > " . substr($code, $i, 1) . " < ";
				continue;
			}

			/**
			 * Only count valid characters
			 */
			$count++;

			/**
			 * Pack them bits.
			 */
			$packer = ($packer << 6 | $x);

			/**
			 * Every four bytes, we have three valid characters.
			 */
			if ($count == 4) {
				$result .= chr(($packer >> 16) ^ 67);
				$result .= chr(($packer >> 8 & 255) ^ 67);
				$result .= chr(($packer & 255) ^ 67);
				$count = 0;
				$packer = 0;
			}
		}

		/**
		 * Now, deal with the remainders
		 */
		if ($count == 2) {
			$result .= chr(((($packer << 12) >> 16) ^ 67));
		} elseif ($count == 3) {
			$result .= chr((($packer << 6) >> 16) ^ 67);
			$result .= chr((($packer << 6) >> 8 & 255) ^ 67);
		}

		return ($result);
	}

	/**
	 * Return the validity of the current CueCat scan
	 *
	 * @return bool true if the decoding was successful
	 */
	function is_valid() {
		return ($this->_valid);
	}

	/**
	 * Get ISBN info for the current decoded barcode (if available)
	 *
	 * @return array an array of info about the ISBN
	 */
	function get_isbn_info() {
		/**
		 * We can only get info from valid barcodes
		 */
		if (!$this->_valid) {
			return (false);
		}

		/**
		 * Initialize our info array
		 */
		$info = array('price' => '', 'country' => '', 'isbn' => '', 'isbn-text' => '');

		switch ($this->code_type) {
		/**
		 * UPC's and generic barcodes have no ISBN info
		 */
		case 'UPC':
		case 'UPA':
		case 'UPE':
			$info = false;
			break;

		/**
		 * IBN's have no additional encoded information
		 */
		case 'IBN':
			$info['isbn'] = substr($this->bar_code, 3, 9);
			break;

		case 'IB5':
			$info['isbn'] = substr($this->bar_code, 3, 9);
			$info['price'] = substr($this->bar_code, 14, 1);
			$info['country'] = substr($this->bar_code, 12, 1);
			break;

		case 'C39':
			$info['isbn'] = $this->bar_code;
			break;

		/**
		 * @todo needs work
		 */
		case 'UA5':
			$info['price'] = substr($this->bar_code, 7, 4);
			$info['country'] = substr($this->bar_code, 5, 1);
			$info['isbn'] = substr($this->bar_code, 12, 9);
			break;

		default:
			$info = false;
		}

		/**
		 * Add the check digit
		 */
		$check_digit = $this->get_isbn_check_digit($info['isbn']);
		$info['isbn'] .= $check_digit;

		/**
		 * Add the textual version
		 */
		$part1 = substr($info['isbn'], 1, 3);
		$part2 = substr($info['isbn'], 4, 5);
		$info['isbn-text'] = $info['isbn'][0] . '-' . $part1 . '-' . $part2 . '-' . $check_digit;

		return ($info);
	}

	/**
	 * Get the ISBN check digit from a 9 digit code
	 *
	 * @param string the 9 digit ISBN
	 * @return int|bool the check digit for the ISBN or false
	 */
	function get_isbn_check_digit($isbn) {
		$sum = 0;

		/**
		 * Produce a sum from all weighted digits
		 */
		for ($i = 0; $i < 9; $i++) {
			$sum += ($i + 1) * $isbn[$i];
		}

		return ($sum % 11);
	}

	/**
	 * Check a barcode to see if it is valid
	 *
	 * @static
	 * @param string $bar_code the bar code we are examining
	 * @return bool true if the barcode is valid
	 */
	function check_barcode($bar_code) {
		$result = false;
		$odd_sum = 0;
		$even_sum = 0;

		if ($this->code_type != 'UPA' && $this->code_type != 'UPC' && $this->code_type != 'UPE') {
			return (true);
		}

		if (strlen($bar_code) == 12) {
			for ($i = 0; $i < 11; $i++) {
				if ($i % 2 == 0) {
					$odd_sum += $bar_code[$i];
				} else {
					$even_sum += $bar_code[$i];
				}
			}

			/**
			 * Calculate the checksum digit
			 */
			$n = ($odd_sum * 3) + $even_sum;
			$x = $n % 10;
			if ($x == 0) {
				$x = 10;
			}

			$check_digit = 10 - $x;
			if ($bar_code[11] == $check_digit) {
				$result = true;
			}
		}

		return ($result);
	}

	/**
	 * Check an ISBN for validity
	 *
	 * @link http://www.cs.queensu.ca/home/bradbury/checkdigit/isbncheck.htm
	 * @static
	 * @param string $isbn the ISBN we are validating
	 * @return bool true if the ISBN is valid
	 */
	function check_isbn($isbn) {
		$len = strlen($isbn);
		$sum = 0;

		if (!$len) {
			return (false);
		}

		/**
		 * If the ISBN is textual, drop the dashes
		 */
		if (($len == 13) && (is_string($isbn) == true)) {
			$isbn = str_replace('-', '', $isbn);
			$len = strlen($isbn);
		}

		/**
		 * If the ISBN is not exactly 10 digits, it fails
		 */
		if ($len != 10) {
			return (false);
		}

		/**
		 * Produce a sum from all weighted digits
		 */
		for ($i = 0; $i < ($len - 1); $i++) {
			$sum += ($i + 1) * $isbn[$i];
		}

		$result = $sum % 11;
		$check_digit = $isbn[9];

		if ($result != $check_digit) {
			return (false);
		}

		/**
		 * Add the check digit and modulus 11
		 */
		$sum += (10 * $check_digit);
		if ($sum % 11 != 0) {
			return (false);
		}

		/**
		 * All tests have been passed
		 */
		return (true);
	}
}

?>