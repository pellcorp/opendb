<?php
/*
* @Version 1.0
* @Author John F. Blyberg <blybergj@aadl.org> - http://www.blyberg.net
* @Desc ISBN Class, adapted from ISBN.pm - http://www.manasystems.co.uk/isbnpm.html
* 
* Downloaded from:
* 	http://www.blyberg.net/2006/04/05/php-port-of-isbn-1013-tool/
* 
* Modified slightly for PHP 4.3.X compatibility
*/

class ISBN {
	function convert($isbn) {
		$isbn2 = substr("978" . trim($isbn), 0, -1);
		$sum13 = $this->genchksum13($isbn2);
		$isbn13 = "$isbn2-$sum13";
		return ($isbn13);
	}

	function gettype($isbn) {
		$isbn = trim($isbn);
		if (preg_match('%[0-9]{12}?[0-9Xx]%s', $isbn)) {
			return 13;
		} else if (preg_match('%[0-9]{9}?[0-9Xx]%s', $isbn)) {
			return 10;
		} else {
			return -1;
		}
	}

	function validateten($isbn) {
		$isbn = trim($isbn);
		$chksum = substr($isbn, -1, 1);
		$isbn = substr($isbn, 0, -1);
		if (preg_match('/X/i', $chksum)) { $chksum="10"; }
		$sum = $this->genchksum10($isbn);
		if ($chksum == $sum){
			return 1;
		}else{
			return 0;
		}
	}

	function validatettn($isbn) {
		$isbn = trim($isbn);
		$chksum = substr($isbn, -1, 1);
		$isbn = substr($isbn, 0, -1);
		if (preg_match('/X/i', $chksum)) { $chksum="10"; }
		$sum = $this->genchksum13($isbn);
		if ($chksum == $sum){
			return 1;
		}else{
			return 0;
		}
	}

	function genchksum13($isbn) {
		$isbn = trim($isbn);
		for ($i = 0; $i <= 12; $i++) {
			$tc = substr($isbn, -1, 1);
			$isbn = substr($isbn, 0, -1);
			$ta = ($tc*3);
			$tci = substr($isbn, -1, 1);
			$isbn = substr($isbn, 0, -1);
			$tb = $tb + $ta + $tci;
		}
		$tg = ($tb / 10);
		$tint = intval($tg);
		if ($tint == $tg) { return 0; }
		$ts = substr($tg, -1, 1);
		$tsum = (10 - $ts);
		return $tsum;
	}

	function genchksum10($isbn) {
		$t = 2;
		$isbn = trim($isbn);
		for($i = 0; $i <= 9; $i++){
			$b = $b + $a;
			$c = substr($isbn, -1, 1);
			$isbn = substr($isbn, 0, -1);
			$a = ($c * $t);
			$t++;
		}
		$s = ($b / 11);
		$s = intval($s);
		$s++;
		$g = ($s * 11);
		$sum = ($g - $b); 
		return $sum;
	}

	function printinvalid() {
		print "That is an invalid ISBN number\n";
		exit;
	}
}
?>